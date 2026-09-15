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

class SystemController extends Controller
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















    /**
     * Devices CRUD & Actions
     */









    /**
     * Enterprise Attendance Operations Center
     */



    /**
     * Manual Logs (Correction requests)
     */




    /**
     * Settings Panel
     */
    public function settings()
    {
        $settings = Setting::all();

        return view('settings.index', compact('settings'));
    }

    public function storeSettings(Request $request)
    {
        $inputs = $request->except('_token');

        foreach ($inputs as $fullKey => $val) {
            // fullKey format: groupName__settingKey
            if (str_contains($fullKey, '__')) {
                $parts = explode('__', $fullKey);
                $group = $parts[0];
                $key = $parts[1];

                $setting = Setting::where('group_name', $group)->where('setting_key', $key)->first();
                if ($setting) {
                    $oldVal = $setting->setting_value;

                    if ($setting->is_encrypted) {
                        $setting->setting_value = Crypt::encryptString($val);
                    } else {
                        $setting->setting_value = $val;
                    }
                    $setting->save();

                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'UPDATE_SETTING',
                        'module' => 'System Settings',
                        'record_id' => $setting->id,
                        'old_value' => json_encode([$setting->setting_key => $oldVal]),
                        'new_value' => json_encode([$setting->setting_key => $val]),
                        'ip_address' => $request->ip(),
                    ]);
                }
            }
        }

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Audit Logs List
     */
    public function auditLogs()
    {
        $logs = AuditLog::with('user')->orderBy('id', 'desc')->paginate(30);

        return view('audit_logs.index', compact('logs'));
    }

    /**
     * Backups Panel (using mysqldump)
     */
    public function backups()
    {
        abort_unless(auth()->user()->role === 'Super Administrator', 403, 'Unauthorized access to backups.');
        
        $backups = Backup::orderBy('id', 'desc')->get();

        return view('backups.index', compact('backups'));
    }

    public function triggerBackup(Request $request)
    {
        abort_unless(auth()->user()->role === 'Super Administrator', 403, 'Unauthorized access to trigger backups.');
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $storagePath = storage_path('app/backups/');

        if (!file_exists($storagePath)) {
            @mkdir($storagePath, 0777, true);
        }

        $filePath = $storagePath . $filename;
        $connection = config('database.default');

        if (!function_exists('exec')) {
            return back()->with('error', 'The PHP exec() function is disabled on this server. Backup aborted.');
        }

        if ($connection === 'sqlite') {
            $sqliteDb = config('database.connections.sqlite.database');
            if (file_exists($sqliteDb)) {
                $sqlContent = "-- SQLite Backup generated on " . date('Y-m-d H:i:s') . "\n";
                $sqlContent .= "-- CREATE TABLE sqlite_backup\n";
                $sqlContent .= file_get_contents($sqliteDb);
                file_put_contents($filePath, $sqlContent);
                $resultCode = 0;
            } else {
                $resultCode = 1;
            }
        } else {
            // MySQL/MariaDB
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');

            // Detect mysqldump
            $mysqldump = env('MYSQLDUMP_PATH', 'mysqldump');
            
            // Build the command safely
            $cmd = escapeshellcmd($mysqldump) . " -h " . escapeshellarg($host) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($user);
            if (!empty($pass)) {
                $cmd .= " -p" . escapeshellarg($pass);
            }
            $cmd .= " " . escapeshellarg($database) . " > " . escapeshellarg($filePath) . " 2>&1";

            $output = [];
            $resultCode = null;
            exec($cmd, $output, $resultCode);
        }

        $fileOk = file_exists($filePath) && filesize($filePath) > 0;

        if ($resultCode === 0 && $fileOk) {
            $backup = Backup::create([
                'backup_date' => now(),
                'backup_status' => 'Success',
                'backup_type' => 'Manual',
                'backup_location' => $filename,
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DATABASE_BACKUP_SUCCESS',
                'module' => 'System Backups',
                'record_id' => $backup->id,
                'new_value' => json_encode(['backup_location' => $filename]),
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', 'Manual database backup completed successfully!');
        } else {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $backup = Backup::create([
                'backup_date' => now(),
                'backup_status' => 'Failed',
                'backup_type' => 'Manual',
                'backup_location' => $filename,
            ]);
            
            // Log failure safely (without exposing password)
            $safeOutput = implode("\n", $output ?? []);
            
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DATABASE_BACKUP_FAILED',
                'module' => 'System Backups',
                'record_id' => $backup->id,
                'new_value' => json_encode(['error' => $safeOutput, 'code' => $resultCode]),
                'ip_address' => $request->ip(),
            ]);

            return back()->with('error', 'Database backup failed. Check Audit Logs for technical details.');
        }
    }

    public function restoreBackup(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'Super Administrator', 403, 'Unauthorized access to restore backups.');

        if (!function_exists('exec')) {
            return back()->with('error', 'The PHP exec() function is disabled on this server. Restore aborted.');
        }

        $lock = \Illuminate\Support\Facades\Cache::lock('database_restore_in_progress', 600);
        if (!$lock->get()) {
            return back()->with('error', 'Another database restore is currently in progress. Please wait.');
        }

        try {
            // 1. Verify Backup
            $backup = Backup::findOrFail($id);
            if ($backup->backup_status !== 'Success') {
                return back()->with('error', 'Cannot restore a failed backup.');
            }

            // Prevent path traversal
            $filePath = storage_path('app/backups/' . basename($backup->backup_location));
            if (!file_exists($filePath) || !is_readable($filePath) || filesize($filePath) <= 0) {
                return back()->with('error', 'Backup file is missing, unreadable, or empty.');
            }

            // 2. Create Safety Backup
            $safetyFilename = 'pre_restore_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $safetyFilePath = storage_path('app/backups/' . $safetyFilename);
            
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');
            $mysqldump = env('MYSQLDUMP_PATH', 'mysqldump');

            $dumpCmd = escapeshellcmd($mysqldump) . " -h " . escapeshellarg($host) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($user);
            if (!empty($pass)) {
                $dumpCmd .= " -p" . escapeshellarg($pass);
            }
            $dumpCmd .= " " . escapeshellarg($database) . " > " . escapeshellarg($safetyFilePath) . " 2>&1";

            $dumpOutput = [];
            $dumpCode = null;
            exec($dumpCmd, $dumpOutput, $dumpCode);

            if ($dumpCode !== 0 || !file_exists($safetyFilePath) || filesize($safetyFilePath) <= 0) {
                if (file_exists($safetyFilePath)) @unlink($safetyFilePath);
                return back()->with('error', 'Restore cancelled because the safety backup could not be created.');
            }

            // Register safety backup
            Backup::create([
                'backup_date' => now(),
                'backup_status' => 'Success',
                'backup_type' => 'Safety',
                'backup_location' => $safetyFilename,
            ]);

            // 3. Restore Selected Backup
            $mysql = env('MYSQL_PATH', 'mysql');
            $restoreCmd = escapeshellcmd($mysql) . " -h " . escapeshellarg($host) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($user);
            if (!empty($pass)) {
                $restoreCmd .= " -p" . escapeshellarg($pass);
            }
            $restoreCmd .= " " . escapeshellarg($database) . " < " . escapeshellarg($filePath) . " 2>&1";

            $restoreOutput = [];
            $restoreCode = null;
            exec($restoreCmd, $restoreOutput, $restoreCode);

            if ($restoreCode !== 0) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'DATABASE_RESTORE_FAILED',
                    'module' => 'System Restores',
                    'record_id' => $backup->id,
                    'new_value' => json_encode(['error' => implode("\n", $restoreOutput)]),
                    'ip_address' => $request->ip(),
                ]);
                return back()->with('error', 'Restore process failed! Your database might be in an inconsistent state. Please check audit logs.');
            }

            // 4. Verify Database Connection
            try {
                \Illuminate\Support\Facades\DB::reconnect();
                $usersCount = \Illuminate\Support\Facades\DB::table('users')->count();
            } catch (\Exception $e) {
                return back()->with('error', 'Restore appeared to succeed, but the application cannot connect to the database or core tables are missing.');
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DATABASE_RESTORE_SUCCESS',
                'module' => 'System Restores',
                'record_id' => $backup->id,
                'new_value' => json_encode(['restored_file' => $backup->backup_location, 'safety_file' => $safetyFilename]),
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', 'Database successfully restored from backup! A safety backup was also created.');

        } finally {
            $lock->release();
        }
    }

    public function downloadBackup($id)
    {
        abort_unless(auth()->user()->role === 'Super Administrator', 403, 'Unauthorized access to download backups.');
        
        $backup = Backup::findOrFail($id);
        $filePath = storage_path('app/backups/' . $backup->backup_location);

        if (file_exists($filePath)) {
            return Response::download($filePath);
        }

        return back()->with('error', 'Backup file does not exist on disk.');
    }

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
    public function holidays()
    {
        $holidays = Holiday::with('company')->get();
        $companies = Company::where('status', 'Active')->get();

        return view('holidays.index', compact('holidays', 'companies'));
    }

    public function storeHoliday(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'holiday_name' => 'required|string',
            'holiday_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_HOLIDAY',
            'module' => 'Holiday Management',
            'record_id' => $holiday->id,
            'new_value' => json_encode($holiday),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday created successfully!');
    }

    public function updateHoliday(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'holiday_name' => 'required|string',
            'holiday_date' => 'required|date',
        ]);

        $oldVal = json_encode($holiday);
        $holiday->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_HOLIDAY',
            'module' => 'Holiday Management',
            'record_id' => $holiday->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($holiday),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday updated successfully!');
    }

    public function destroyHoliday(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);
        $oldVal = json_encode($holiday);
        $holiday->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_HOLIDAY',
            'module' => 'Holiday Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday deleted successfully!');
    }

    /**
     * Users & RBAC Management CRUD
     */
    public function users()
    {
        $users = User::with(['accessLevel', 'template'])->get();
        $roles = Role::orderBy('sort_order', 'asc')->get();
        $accessLevels = \App\Models\AccessLevel::orderBy('level', 'asc')->get();
        $templates = \App\Models\PermissionTemplate::orderBy('name', 'asc')->get();

        return view('users.index', compact('users', 'roles', 'accessLevels', 'templates'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'access_level_id' => 'nullable|exists:access_levels,id',
            'template_id' => 'nullable|exists:permission_templates,id',
        ]);

        $user = User::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'access_level_id' => $request->input('access_level_id'),
            'template_id' => $request->input('template_id'),
            'status' => 'Active',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_USER',
            'module' => 'User Management',
            'record_id' => $user->id,
            'new_value' => json_encode($user),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'User registered successfully!');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|string|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'access_level_id' => 'nullable|exists:access_levels,id',
            'template_id' => 'nullable|exists:permission_templates,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $oldVal = json_encode($user);

        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->role = $request->input('role');
        $user->access_level_id = $request->input('access_level_id');
        $user->template_id = $request->input('template_id');
        $user->status = $request->input('status');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        // Invalidate permission cache
        $user->invalidatePermissionCache();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_USER',
            'module' => 'User Management',
            'record_id' => $user->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($user),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'User updated successfully!');
    }

    public function destroyUser(Request $request, $id)
    {
        if (Auth::id() == $id) {
            return back()->with('error', 'You cannot delete your own user account!');
        }

        $user = User::findOrFail($id);
        $oldVal = json_encode($user);
        $user->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_USER',
            'module' => 'User Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Leaves & Leave Types Management CRUD
     */









    public function roles(Request $request)
    {
        $roles = Role::orderBy('sort_order', 'asc')->get();

        return view('roles.index', compact('roles'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        $maxSort = Role::max('sort_order') ?? 0;

        $permissions = [
            'manage_companies' => $request->has('permissions.manage_companies'),
            'manage_employees' => $request->has('permissions.manage_employees'),
            'manage_attendance' => $request->has('permissions.manage_attendance'),
            'view_reports' => $request->has('permissions.view_reports'),
            'manage_settings' => $request->has('permissions.manage_settings'),
            'manage_roles' => $request->has('permissions.manage_roles'),
        ];

        $role = Role::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'permissions' => $permissions,
            'sort_order' => $maxSort + 1,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_ROLE',
            'module' => 'Roles Management',
            'record_id' => $role->id,
            'old_value' => null,
            'new_value' => json_encode($role),
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role created successfully!',
                'redirect' => route('roles')
            ]);
        }

        return back()->with('success', 'Role created successfully!');
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $oldVal = json_encode($role);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $permissions = [
            'manage_companies' => $request->has('permissions.manage_companies'),
            'manage_employees' => $request->has('permissions.manage_employees'),
            'manage_attendance' => $request->has('permissions.manage_attendance'),
            'view_reports' => $request->has('permissions.view_reports'),
            'manage_settings' => $request->has('permissions.manage_settings'),
            'manage_roles' => $request->has('permissions.manage_roles'),
        ];

        $role->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'permissions' => $permissions,
        ]);

        cache()->forget('role_permissions_' . $role->name);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_ROLE',
            'module' => 'Roles Management',
            'record_id' => $role->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($role),
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role updated successfully!',
                'redirect' => route('roles')
            ]);
        }

        return back()->with('success', 'Role updated successfully!');
    }

    public function destroyRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Super Administrator') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The Super Administrator role cannot be deleted.'], 422);
            }
            return back()->with('error', 'The Super Administrator role cannot be deleted.');
        }

        $oldVal = json_encode($role);
        cache()->forget('role_permissions_' . $role->name);
        $role->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_ROLE',
            'module' => 'Roles Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'new_value' => null,
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role deleted successfully!',
                'redirect' => route('roles')
            ]);
        }

        return back()->with('success', 'Role deleted successfully!');
    }

    public function moveRoleUp(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $previousRole = Role::where('sort_order', '<', $role->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousRole) {
            $oldOrder = $role->sort_order;
            $role->sort_order = $previousRole->sort_order;
            $previousRole->sort_order = $oldOrder;
            $role->save();
            $previousRole->save();

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'REORDER_ROLE_UP',
                'module' => 'Roles Management',
                'record_id' => $role->id,
                'old_value' => json_encode(['role_id' => $role->id, 'sort_order' => $oldOrder]),
                'new_value' => json_encode(['role_id' => $role->id, 'sort_order' => $role->sort_order]),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Role priority updated.',
                    'redirect' => route('roles')
                ]);
            }

            return back()->with('success', 'Role moved up successfully!');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role is already at the highest priority.'], 422);
        }

        return back()->with('error', 'Role is already at the highest priority.');
    }

    public function moveRoleDown(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $nextRole = Role::where('sort_order', '>', $role->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextRole) {
            $oldOrder = $role->sort_order;
            $role->sort_order = $nextRole->sort_order;
            $nextRole->sort_order = $oldOrder;
            $role->save();
            $nextRole->save();

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'REORDER_ROLE_DOWN',
                'module' => 'Roles Management',
                'record_id' => $role->id,
                'old_value' => json_encode(['role_id' => $role->id, 'sort_order' => $oldOrder]),
                'new_value' => json_encode(['role_id' => $role->id, 'sort_order' => $role->sort_order]),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Role priority updated.',
                    'redirect' => route('roles')
                ]);
            }

            return back()->with('success', 'Role moved down successfully!');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role is already at the lowest priority.'], 422);
        }

        return back()->with('error', 'Role is already at the lowest priority.');
    }
}
