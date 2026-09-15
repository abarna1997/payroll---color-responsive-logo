<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\Employee;
use App\Services\Adms\AdmsCommandService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncEmployeeToDevices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $employeeId;
    public $deviceIds;
    public $action; // 'create', 'update', or 'delete'

    /**
     * Create a new job instance.
     *
     * @param int $employeeId
     * @param array $deviceIds Specific target device IDs. If empty, defaults to all assigned devices in employee_devices.
     * @param string $action 'create', 'update', or 'delete'
     */
    public function __construct(int $employeeId, array $deviceIds = [], string $action = 'create')
    {
        $this->employeeId = $employeeId;
        $this->deviceIds = $deviceIds;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(AdmsCommandService $commandService): void
    {
        $employee = Employee::find($this->employeeId);

        if (!$employee && $this->action !== 'delete') {
            return;
        }

        $targetDeviceIds = $this->deviceIds;

        if (empty($targetDeviceIds)) {
            if ($employee) {
                $targetDeviceIds = DB::table('employee_devices')
                    ->where('employee_id', $this->employeeId)
                    ->pluck('device_id')
                    ->toArray();

                if (empty($targetDeviceIds)) {
                    if ($employee->company_id) {
                        $targetDeviceIds = Device::where('company_id', $employee->company_id)->pluck('id')->toArray();
                    }
                    if (empty($targetDeviceIds)) {
                        $targetDeviceIds = Device::pluck('id')->toArray();
                    }

                    if (!empty($targetDeviceIds) && $this->action !== 'delete') {
                        $employee->devices()->syncWithoutDetaching($targetDeviceIds);
                    }
                }
            } else {
                // If employee record was already deleted, fallback to targeting all active devices in the system
                $targetDeviceIds = Device::pluck('id')->toArray();
            }
        }

        if (empty($targetDeviceIds)) {
            return;
        }

        foreach ($targetDeviceIds as $deviceId) {
            $device = Device::find($deviceId);
            if (!$device) {
                continue;
            }

            $commandType = match ($this->action) {
                'delete' => 'DELETE_USER',
                'update' => 'UPDATE_USER',
                default => 'CREATE_USER',
            };

            // Avoid duplicating pending identical commands for the same device & employee
            $existingPending = DeviceCommand::where('device_id', $deviceId)
                ->where('employee_id', $this->employeeId)
                ->where('command_type', $commandType)
                ->where('status', 'pending')
                ->first();

            if ($existingPending) {
                continue;
            }

            $deviceCommand = DeviceCommand::create([
                'device_id' => $deviceId,
                'employee_id' => $this->employeeId,
                'command_type' => $commandType,
                'command' => '',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            if ($this->action === 'delete') {
                $pin = $employee ? ($employee->sync_pin ?? preg_replace('/[^0-9]/', '', $employee->device_user_id ?? $employee->employee_id)) : $this->employeeId;
                if (empty($pin)) {
                    $pin = $employee ? $employee->id : $this->employeeId;
                }
                $commandString = $commandService->generateUserDeleteCommand((string) $pin, $deviceCommand->id);
            } else {
                $commandString = $commandService->generateUserUpdateCommand($employee, $deviceCommand->id);
            }

            $deviceCommand->update([
                'command' => $commandString,
            ]);

            // If employee has a profile photo, queue DATA UPDATE USERPIC command for terminal display
            if ($employee && !empty($employee->profile_photo) && $this->action !== 'delete') {
                $bioPhotoCmd = $commandService->generateUserPicCommand($employee, $employee->profile_photo);
                if ($bioPhotoCmd) {
                    DeviceCommand::create([
                        'device_id' => $deviceId,
                        'employee_id' => $this->employeeId,
                        'command_type' => 'UPDATE_USERPIC',
                        'command' => $bioPhotoCmd,
                        'status' => 'pending',
                        'retry_count' => 0,
                    ]);
                }
            }
        }

        if ($employee && $this->action !== 'delete') {
            $employee->update(['biometric_status' => 'Pending Sync']);
        }
    }
}
