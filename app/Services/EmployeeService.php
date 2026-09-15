<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Device;
use App\Models\AuditLog;
use App\Jobs\SyncEmployeeToDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;

class EmployeeService
{
    public function storeEmployee(Request $request)
    {
        $employeeId = trim((string) $request->input('employee_id'));
        $company = Company::find($request->input('company_id'));
        if ($company && $company->company_code) {
            $prefix = $company->company_code . '-';
            if (!str_starts_with(strtoupper($employeeId), strtoupper($prefix))) {
                $employeeId = $company->company_code . '-' . ltrim($employeeId, '-');
            }
        }

        preg_match('/\d+$/', $employeeId, $matches);
        if (isset($matches[0])) {
            $employeeNumber = (int) $matches[0];
        } else {
            $maxNum = Employee::where('company_id', $request->input('company_id'))->max('employee_number');
            $employeeNumber = $maxNum ? $maxNum + 1 : 1;
        }

        $data = $request->all();
        $data['employee_number'] = $employeeNumber;
        $data['employee_id'] = $employeeId;
        $data['allow_remote_punch'] = $request->has('allow_remote_punch') ? 1 : 0;

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $tempPath = $file->getRealPath();
            $optimizedPath = $this->processAndSaveImage($tempPath);
            if ($optimizedPath) {
                $data['profile_photo'] = $optimizedPath;
            } else {
                // Fallback to direct move if GD fails
                $filename = time() . '_' . $file->getClientOriginalName();
                \Illuminate\Support\Facades\File::ensureDirectoryExists(public_path('uploads/profiles/'));
                $file->move(public_path('uploads/profiles/'), $filename);
                $data['profile_photo'] = 'uploads/profiles/' . $filename;
            }
        }

        $emp = Employee::create($data);
        
        if (empty($emp->device_user_id)) {
            $emp->update(['device_user_id' => $emp->employee_id]);
        }
        
        $emp->update(['sync_pin' => (int) ($emp->company_id . sprintf('%03d', $emp->employee_number))]);

        // Account Creation
        if ($request->has('create_account') && $request->create_account == 1 && $request->email) {
            $user = User::create([
                'username' => $request->employee_id,
                'email' => $request->email,
                'password' => Hash::make($request->password ?? 'password123'),
                'role' => $request->role ?? 'Employee',
                'status' => 'Active',
                'force_password_change' => true,
            ]);
            $emp->update(['user_id' => $user->id]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_EMPLOYEE',
            'module' => 'Employee Management',
            'record_id' => $emp->id,
            'new_value' => json_encode($emp),
            'ip_address' => $request->ip(),
        ]);

        $deviceIds = $request->input('device_ids', []);
        if (empty($deviceIds) && $emp->company_id) {
            $deviceIds = Device::where('company_id', $emp->company_id)->pluck('id')->toArray();
        }
        if (empty($deviceIds)) {
            $deviceIds = Device::pluck('id')->toArray();
        }
        if (!empty($deviceIds)) {
            $emp->devices()->sync($deviceIds);
        }

        SyncEmployeeToDevices::dispatchSync($emp->id, $deviceIds, 'create');

        return $emp;
    }

    public function updateEmployee(Employee $emp, Request $request)
    {
        $data = $request->all();
        $data['allow_remote_punch'] = $request->has('allow_remote_punch') ? 1 : 0;

        if ($request->hasFile('profile_photo')) {
            if ($emp->profile_photo && file_exists(public_path($emp->profile_photo))) {
                @unlink(public_path($emp->profile_photo));
            }
            $file = $request->file('profile_photo');
            $tempPath = $file->getRealPath();
            $optimizedPath = $this->processAndSaveImage($tempPath);
            if ($optimizedPath) {
                $data['profile_photo'] = $optimizedPath;
            } else {
                throw new \Exception("Image processing failed. Please ensure the PHP GD extension is enabled on your cPanel server and you are uploading a valid JPG/PNG.");
            }
        }

        $oldStatus = $emp->status;
        $oldVal = json_encode($emp);
        
        $oldDeviceIds = DB::table('employee_devices')->where('employee_id', $emp->id)->pluck('device_id')->toArray();
        $newDeviceIds = $request->input('device_ids', []);

        $emp->update($data);
        $emp->devices()->sync($newDeviceIds);

        // Account Update / Creation
        if ($request->has('create_account') && $request->create_account == 1) {
            if ($emp->user_id) {
                $user = User::find($emp->user_id);
                if ($user) {
                    $user->role = $request->role ?? 'Employee';
                    if ($request->filled('password')) {
                        $user->password = Hash::make($request->password);
                    }
                    $user->save();
                }
            } else if ($request->email) {
                $user = User::create([
                    'username' => $emp->employee_id,
                    'email' => $request->email,
                    'password' => Hash::make($request->password ?? 'password123'),
                    'role' => $request->role ?? 'Employee',
                    'status' => 'Active',
                    'force_password_change' => true,
                ]);
                $emp->update(['user_id' => $user->id]);
            }
        }

        $addedDevices = array_diff($newDeviceIds, $oldDeviceIds);
        $removedDevices = array_diff($oldDeviceIds, $newDeviceIds);
        $retainedDevices = array_intersect($oldDeviceIds, $newDeviceIds);

        if (!empty($addedDevices)) {
            SyncEmployeeToDevices::dispatchSync($emp->id, array_values($addedDevices), 'create');
        }

        if (!empty($removedDevices)) {
            SyncEmployeeToDevices::dispatchSync($emp->id, array_values($removedDevices), 'delete');
        }

        if ($emp->status !== 'Active') {
            if (!empty($retainedDevices)) {
                SyncEmployeeToDevices::dispatchSync($emp->id, array_values($retainedDevices), 'delete');
            }
        } elseif ($oldStatus !== 'Active' && $emp->status === 'Active') {
            if (!empty($retainedDevices)) {
                SyncEmployeeToDevices::dispatchSync($emp->id, array_values($retainedDevices), 'create');
            }
        } else {
            if (!empty($retainedDevices)) {
                SyncEmployeeToDevices::dispatchSync($emp->id, array_values($retainedDevices), 'update');
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_EMPLOYEE',
            'module' => 'Employee Management',
            'record_id' => $emp->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($emp),
            'ip_address' => $request->ip(),
        ]);

        return $emp;
    }

    public function destroyEmployee(Employee $emp, Request $request)
    {
        $oldVal = json_encode($emp);
        $assignedDeviceIds = DB::table('employee_devices')->where('employee_id', $emp->id)->pluck('device_id')->toArray();

        if (empty($assignedDeviceIds)) {
            if ($emp->company_id) {
                $assignedDeviceIds = Device::where('company_id', $emp->company_id)->pluck('id')->toArray();
            }
            if (empty($assignedDeviceIds)) {
                $assignedDeviceIds = Device::pluck('id')->toArray();
            }
        }

        if (!empty($assignedDeviceIds)) {
            SyncEmployeeToDevices::dispatchSync($emp->id, $assignedDeviceIds, 'delete');
        }

        if ($emp->profile_photo && file_exists(public_path($emp->profile_photo))) {
            @unlink(public_path($emp->profile_photo));
        }

        $emp->devices()->detach();
        $emp->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_EMPLOYEE',
            'module' => 'Employee Management',
            'record_id' => $emp->id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return true;
    }

    /**
     * Process, resize, compress, and save the employee photo strictly as JPEG using native PHP GD.
     * Process, resize, compress, and save the employee photo strictly as JPEG using native PHP GD or Imagick.
     * ADMS ZKTeco devices require small JPEG images (typically < 50KB, Type 9).
     */
    private function processAndSaveImage(string $sourcePath): ?string
    {
        if (!file_exists($sourcePath)) return null;

        $info = @getimagesize($sourcePath);
        if ($info === false) return null;
        $mime = $info['mime'];
        
        $filename = time() . '_' . uniqid() . '.jpg';
        $directory = public_path('uploads/profiles/');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($directory);
        $destination = $directory . $filename;

        // Try GD First
        if (function_exists('imagecreatetruecolor')) {
            $image = null;
            switch ($mime) {
                case 'image/jpeg': $image = @imagecreatefromjpeg($sourcePath); break;
                case 'image/png':  $image = @imagecreatefrompng($sourcePath); break;
                case 'image/gif':  $image = @imagecreatefromgif($sourcePath); break;
            }
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                $maxWidth = 600; $maxHeight = 600;

                if ($width > $maxWidth || $height > $maxHeight) {
                    $ratio = min($maxWidth / $width, $maxHeight / $height);
                    $newWidth = (int)($width * $ratio);
                    $newHeight = (int)($height * $ratio);
                    $newImage = imagecreatetruecolor($newWidth, $newHeight);
                    if ($mime == 'image/png' || $mime == 'image/gif') {
                        $white = imagecolorallocate($newImage, 255, 255, 255);
                        imagefill($newImage, 0, 0, $white);
                    }
                    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagedestroy($image);
                    $image = $newImage;
                } else {
                    // For small PNG/GIFs, convert transparency to white before saving as JPG
                    if ($mime == 'image/png' || $mime == 'image/gif') {
                        $newImage = imagecreatetruecolor($width, $height);
                        $white = imagecolorallocate($newImage, 255, 255, 255);
                        imagefill($newImage, 0, 0, $white);
                        imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
                        imagedestroy($image);
                        $image = $newImage;
                    }
                }
                
                $success = imagejpeg($image, $destination, 85);
                imagedestroy($image);
                if ($success) return 'uploads/profiles/' . $filename;
            }
        }

        // Fallback to Imagick if GD is completely disabled in cPanel
        if (extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick($sourcePath);
                $imagick->setImageBackgroundColor('white');
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompressionQuality(85);
                
                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();
                if ($width > 600 || $height > 600) {
                    $imagick->thumbnailImage(600, 600, true);
                }
                
                if ($imagick->writeImage($destination)) {
                    $imagick->clear();
                    $imagick->destroy();
                    return 'uploads/profiles/' . $filename;
                }
            } catch (\Throwable $e) {
                // Ignore and fall through to throw exception
            }
        }

        throw new \Exception("Image processing failed: Neither GD nor Imagick is available or functional.");
    }
}
