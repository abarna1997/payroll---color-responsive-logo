<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceEventLog;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\ManualLog;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserPermissionHistory;
use App\Jobs\SyncEmployeeToDevices;
use App\Services\NetworkScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class DeviceController extends Controller
{
    /**
     * Dashboard View (with dynamic stats, health panel, and graphs)
     */

    /**
     * Companies CRUD
     */





    /**
     * Branches CRUD
     */




    /**
     * Departments CRUD
     */








    /**
     * Shifts CRUD
     */




    /**
     * Employees CRUD
     */












    public function syncDevice($id)
    {
        $device = Device::findOrFail($id);
        $assignedEmployeeIds = DB::table('employee_devices')
            ->where('device_id', $device->id)
            ->pluck('employee_id')
            ->toArray();

        if (empty($assignedEmployeeIds)) {
            // Fallback: fetch all active employees in the device's company
            if ($device->company_id) {
                $assignedEmployeeIds = Employee::where('company_id', $device->company_id)
                    ->where('status', 'Active')
                    ->pluck('id')
                    ->toArray();
            }
        }

        $queuedCount = 0;
        foreach ($assignedEmployeeIds as $empId) {
            SyncEmployeeToDevices::dispatch($empId, [$device->id], 'create');
            $queuedCount++;
        }

        return back()->with('success', "Queued synchronization for {$queuedCount} employees to device: {$device->device_name} ({$device->device_serial_number})!");
    }



    /**
     * Devices CRUD & Actions
     */
    public function devices()
    {
        $devices = Device::with('company', 'branch')->get();
        $companies = Company::where('status', 'Active')->get();
        $branches = Branch::where('status', 'Active')->get();

        $totalDevices = $devices->count();
        $onlineDevices = 0;
        $offlineDevices = 0;
        $disabledDevices = 0;
        $pendingDevices = 0;
        $totalHealthScore = 0;

        foreach ($devices as $d) {
            $d->employees_count = $d->company_id ? Employee::where('company_id', $d->company_id)->count() : 0;
            $d->attendance_today_count = AttendanceLog::where('device_id', $d->id)
                ->where('attendance_date', Carbon::today()->toDateString())
                ->count();

            $statusText = $d->status_text;
            if ($statusText === 'Online') {
                $onlineDevices++;
            } elseif ($statusText === 'Offline') {
                $offlineDevices++;
            } elseif ($statusText === 'Disabled') {
                $disabledDevices++;
            } elseif ($statusText === 'Pending Approval') {
                $pendingDevices++;
            }

            $totalHealthScore += $d->health_score;
        }

        $averageHealth = $totalDevices > 0 ? round($totalHealthScore / $totalDevices) : 100;

        $avgResponseTimeMs = DeviceCommand::where('status', 'completed')
            ->whereNotNull('execution_time_ms')
            ->avg('execution_time_ms');

        $avgResponseTime = $avgResponseTimeMs ? round($avgResponseTimeMs) : 0;

        return view('devices.index', compact(
            'devices',
            'companies',
            'branches',
            'totalDevices',
            'onlineDevices',
            'offlineDevices',
            'disabledDevices',
            'pendingDevices',
            'averageHealth',
            'avgResponseTime'
        ));
    }

    public function scanNetwork(Request $request, NetworkScannerService $scanner)
    {
        $devices = $scanner->autoDiscoverDevices();
        return response()->json(['devices' => $devices]);
    }

    public function discoverDevice(Request $request)
    {
        $request->validate([
            'device_serial_number' => 'required|string|unique:devices,device_serial_number',
            'device_name' => 'required|string',
            'location' => 'nullable|string',
        ]);

        $device = Device::create([
            'device_name' => $request->input('device_name'),
            'device_serial_number' => $request->input('device_serial_number'),
            'status' => 'Pending Approval',
            'timezone' => 'Asia/Colombo',
            'location' => $request->input('location'),
        ]);

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'DEVICE_DISCOVERED',
            'event_message' => "Device manually added for discovery. Serial: {$device->device_serial_number}.",
            'severity' => 'Info',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'MANUAL_DISCOVER_DEVICE',
            'module' => 'Device Management',
            'record_id' => $device->id,
            'new_value' => json_encode($device),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Device successfully added to discovery list!');
    }

    public function updateDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $oldVal = json_encode($device);

        $request->validate([
            'device_name' => 'required|string|max:255',
            'device_serial_number' => 'required|string|max:255|unique:devices,device_serial_number,' . $device->id,
            'location' => 'nullable|string|max:255',
            'timezone' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|string|in:Pending Approval,Online,Offline,Disabled,Error',
            'device_model' => 'nullable|string|max:255',
            'firmware_version' => 'nullable|string|max:255',
        ]);

        $device->device_name = $request->input('device_name');
        $device->device_serial_number = strtoupper(trim($request->input('device_serial_number')));
        $device->location = $request->input('location');
        $device->timezone = $request->input('timezone');
        $device->company_id = $request->input('company_id');
        $device->branch_id = $request->input('branch_id');
        $device->status = $request->input('status');
        $device->device_model = $request->input('device_model');
        $device->firmware_version = $request->input('firmware_version');
        $device->save();

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'DEVICE_UPDATED',
            'event_message' => 'Device details updated by Administrator.',
            'severity' => 'Info',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_DEVICE',
            'module' => 'Device Management',
            'record_id' => $device->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($device),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Device updated successfully!');
    }

    public function destroyDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $oldVal = json_encode($device);

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'DEVICE_DELETED',
            'event_message' => 'Device deleted by Administrator.',
            'severity' => 'Warning',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_DEVICE',
            'module' => 'Device Management',
            'record_id' => $device->id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        $device->delete();

        return back()->with('success', 'Device deleted successfully!');
    }

    public function approveDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $oldVal = json_encode($device);

        $device->status = 'Online';
        $device->company_id = $request->input('company_id');
        $device->branch_id = $request->input('branch_id');
        $device->device_name = $request->input('device_name', $device->device_name);
        $device->location = $request->input('location');
        $device->save();

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'DEVICE_APPROVED',
            'event_message' => 'Device approved by Administrator and linked to company/branch.',
            'severity' => 'Info',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'APPROVE_DEVICE',
            'module' => 'Device Management',
            'record_id' => $device->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($device),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Device approved and activated!');
    }

    public function disableDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $oldVal = json_encode($device);

        $device->status = 'Disabled';
        $device->save();

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'DEVICE_DISABLED',
            'event_message' => 'Device disabled by Administrator.',
            'severity' => 'Warning',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DISABLE_DEVICE',
            'module' => 'Device Management',
            'record_id' => $device->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($device),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Device disabled successfully!');
    }

    public function enableDevice(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $oldVal = json_encode($device);

        $device->status = 'Online';
        $device->save();

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'DEVICE_ENABLED',
            'event_message' => 'Device re-enabled by Administrator.',
            'severity' => 'Info',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'ENABLE_DEVICE',
            'module' => 'Device Management',
            'record_id' => $device->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($device),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Device enabled successfully!');
    }

    public function triggerCommand(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $cmdText = $request->input('command');

        if ($request->filled('command_pin')) {
            $pins = preg_split('/[\s,]+/', trim($request->input('command_pin')), -1, PREG_SPLIT_NO_EMPTY);
            
            if (count($pins) > 0) {
                foreach ($pins as $pin) {
                    $cmd = DeviceCommand::create([
                        'device_id' => $device->id,
                        'command' => "DATA DELETE userinfo PIN=" . $pin,
                        'status' => 'pending',
                        'created_by' => Auth::id(),
                        'failure_reason' => null,
                    ]);

                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'QUEUE_DEVICE_COMMAND',
                        'module' => 'Device Management',
                        'record_id' => $cmd->id,
                        'new_value' => json_encode($cmd),
                        'ip_address' => $request->ip(),
                    ]);
                }
                return back()->with('success', "Queued deletion commands for " . count($pins) . " users.");
            }
        }

        if (empty($cmdText)) {
            return back()->with('error', 'Command string cannot be empty.');
        }

        // Support dynamic timestamp for time synchronization
        if ($cmdText === 'SET_TIME') {
            $cmdText = 'SET_TIME ' . now()->format('Y-m-d H:i:s');
        }

        $cmd = DeviceCommand::create([
            'device_id' => $device->id,
            'command' => $cmdText,
            'status' => 'pending',
            'created_by' => Auth::id(),
            'failure_reason' => null,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'QUEUE_DEVICE_COMMAND',
            'module' => 'Device Management',
            'record_id' => $cmd->id,
            'new_value' => json_encode($cmd),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Command queued: {$cmdText}!");
    }

    /**
     * Enterprise Attendance Operations Center
     */



    /**
     * Manual Logs (Correction requests)
     */




    /**
     * Settings Panel
     */


    /**
     * Audit Logs List
     */

    /**
     * Backups Panel (using mysqldump)
     */



    /**
     * Dynamic Reports Generator (12 report types)
     */
    /**
     * Compute paired daily attendance record from raw logs according to policy thresholds
     */

    /**
     * Dynamic Reports Generator (12 report types)
     */

    /**
     * CSV Export handler
     */

    /**
     * Holidays Management CRUD
     */




    /**
     * Users & RBAC Management CRUD
     */




    /**
     * Leaves & Leave Types Management CRUD
     */














}
