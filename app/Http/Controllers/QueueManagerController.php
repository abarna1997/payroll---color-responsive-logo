<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceEventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueManagerController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to access the Queue Manager.');
        }

        $queueStats = [
            'pending' => DeviceCommand::where('status', 'pending')->count(),
            'sent' => DeviceCommand::where('status', 'sent')->count(),
            'completed' => DeviceCommand::where('status', 'completed')->count(),
            'failed' => DeviceCommand::where('status', 'failed')->count(),
            'cancelled' => DeviceCommand::where('status', 'cancelled')->count(),
        ];

        $queueCommands = DeviceCommand::with('device', 'creator')
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'sent' THEN 2 WHEN 'failed' THEN 3 WHEN 'completed' THEN 4 WHEN 'cancelled' THEN 5 ELSE 6 END")
            ->orderBy('id', 'desc')
            ->paginate(12);

        $queueDevices = Device::orderBy('device_name')->get();

        return view('administration.queue_manager', compact('queueStats', 'queueCommands', 'queueDevices'));
    }

    public function queueCommand(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to manage the device queue.');
        }

        $data = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'command' => 'required|string|max:255',
        ]);

        $device = Device::findOrFail($data['device_id']);

        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command' => $data['command'],
            'status' => 'pending',
            'created_by' => Auth::id(),
            'retry_count' => 0,
            'failure_reason' => null,
        ]);

        DeviceEventLog::create([
            'device_id' => $device->id,
            'event_type' => 'COMMAND_QUEUED',
            'event_message' => "Command queued from Administration Queue Manager: {$command->command}",
            'severity' => 'Info',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'QUEUE_DEVICE_COMMAND_ADMIN',
            'module' => 'Administration',
            'record_id' => $command->id,
            'new_value' => json_encode($command),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Command queued successfully!');
    }

    public function retryCommand(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to manage the device queue.');
        }

        $command = DeviceCommand::findOrFail($id);
        $oldValue = json_encode($command);

        $command->status = 'pending';
        $command->sent_at = null;
        $command->completed_at = null;
        $command->response = null;
        $command->failure_reason = null;
        $command->retry_count = $command->retry_count + 1;
        $command->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'RETRY_DEVICE_COMMAND_ADMIN',
            'module' => 'Administration',
            'record_id' => $command->id,
            'old_value' => $oldValue,
            'new_value' => json_encode($command),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Command moved back to pending queue.');
    }

    public function cancelCommand(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to manage the device queue.');
        }

        $command = DeviceCommand::findOrFail($id);
        $oldValue = json_encode($command);

        $command->status = 'cancelled';
        $command->failure_reason = 'Cancelled by administrator.';
        $command->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CANCEL_DEVICE_COMMAND_ADMIN',
            'module' => 'Administration',
            'record_id' => $command->id,
            'old_value' => $oldValue,
            'new_value' => json_encode($command),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Command cancelled successfully.');
    }
}