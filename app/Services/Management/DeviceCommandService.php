<?php

namespace App\Services\Management;

use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Support\Facades\Auth;

class DeviceCommandService
{
    public function queueCommand(Device $device, string $cmdText, $userId = null)
    {
        if (empty($cmdText)) {
            return false;
        }

        // Support dynamic timestamp for time synchronization
        if ($cmdText === 'SET_TIME') {
            $cmdText = 'SET_TIME ' . now()->format('Y-m-d H:i:s');
        }

        return DeviceCommand::create([
            'device_id' => $device->id,
            'command' => $cmdText,
            'status' => 'pending',
            'created_by' => $userId ?? Auth::id(),
            'failure_reason' => null,
        ]);
    }
}
