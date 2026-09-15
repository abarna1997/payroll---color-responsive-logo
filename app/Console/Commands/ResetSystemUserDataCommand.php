<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\Employee;
use App\Models\ManualLog;
use App\Services\Adms\AdmsCommandService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetSystemUserDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset-user-data {--force : Force the operation to run without interactive prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue CLEAR DATA / DELETE USER commands for all biometric terminals and wipe employee & attendance data from application database.';

    /**
     * Execute the console command.
     */
    public function handle(AdmsCommandService $commandService)
    {
        if (!$this->option('force') && !$this->confirm('WARNING: This will queue CLEAR DATA commands for all ZKTeco devices and WIPE all employees and attendance logs from the database. Do you wish to continue?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting System & Biometric Device Data Reset...');

        // 1. Queue CLEAR DATA command for all biometric devices
        $devices = Device::all();
        $queuedCommands = 0;

        foreach ($devices as $device) {
            // Queue ZKTeco CLEAR DATA command (Clears users, fingerprints, logs from terminal memory)
            DeviceCommand::create([
                'device_id' => $device->id,
                'employee_id' => null,
                'command_type' => 'CLEAR_DATA',
                'command' => 'CLEAR DATA',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            // Queue CLEAR LOG command
            DeviceCommand::create([
                'device_id' => $device->id,
                'employee_id' => null,
                'command_type' => 'CLEAR_LOG',
                'command' => 'CLEAR LOG',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            $queuedCommands += 2;
        }

        $this->info("Queued {$queuedCommands} wipe commands (CLEAR DATA & CLEAR LOG) across " . $devices->count() . " biometric devices.");

        // 2. Wipe application database user & attendance data
        Schema::disableForeignKeyConstraints();

        $employeesCount = Employee::count();
        $attendanceLogsCount = AttendanceLog::count();
        $manualLogsCount = ManualLog::count();

        DB::table('employee_devices')->truncate();
        DB::table('employee_checklists')->truncate();
        DB::table('employee_documents')->truncate();
        DB::table('employee_agreements')->truncate();
        DB::table('employee_assets')->truncate();
        DB::table('onboarding_stages')->truncate();
        
        AttendanceLog::truncate();
        ManualLog::truncate();
        Employee::truncate();

        Schema::enableForeignKeyConstraints();

        $this->info("Successfully wiped from application database:");
        $this->line(" - Employees: {$employeesCount} records deleted");
        $this->line(" - Attendance Logs: {$attendanceLogsCount} records deleted");
        $this->line(" - Manual Logs: {$manualLogsCount} records deleted");
        $this->line(" - Mapped Devices & Onboarding files cleared");

        $this->info('Reset complete! Biometric devices will clear their internal user/fingerprint memory on next ADMS poll.');

        return 0;
    }
}
