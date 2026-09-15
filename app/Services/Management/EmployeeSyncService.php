<?php

namespace App\Services\Management;

use App\Models\Employee;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Services\Adms\AdmsCommandService;
use Illuminate\Support\Facades\DB;

class EmployeeSyncService
{
    protected $commandService;

    public function __construct(AdmsCommandService $commandService)
    {
        $this->commandService = $commandService;
    }

    public function syncEmployee(Employee $emp)
    {
        $assignedDeviceIds = DB::table('employee_devices')
            ->where('employee_id', $emp->id)
            ->pluck('device_id')
            ->toArray();

        // If no explicit devices assigned, fallback to device assigned to employee company or all devices
        if (empty($assignedDeviceIds)) {
            $assignedDeviceIds = Device::where('company_id', $emp->company_id)->pluck('id')->toArray();
            if (empty($assignedDeviceIds)) {
                $assignedDeviceIds = Device::pluck('id')->toArray();
            }
            if (!empty($assignedDeviceIds)) {
                $emp->devices()->sync($assignedDeviceIds);
            }
        }

        if (empty($assignedDeviceIds)) {
            return [
                'success' => false,
                'message' => "No biometric terminals found in the system to sync {$emp->full_name}."
            ];
        }

        $queued = 0;

        foreach ($assignedDeviceIds as $devId) {
            $deviceCommand = DeviceCommand::create([
                'device_id' => $devId,
                'employee_id' => $emp->id,
                'command_type' => 'CREATE_USER',
                'command' => '',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            $commandString = $this->commandService->generateUserUpdateCommand($emp, $deviceCommand->id);
            $deviceCommand->update(['command' => $commandString]);
            $queued++;
        }

        return [
            'success' => true,
            'queued' => $queued,
            'message' => "Force Sync Triggered: Immediately queued CREATE_USER command for {$emp->full_name} across {$queued} terminal(s)!"
        ];
    }
}
