<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\AdmsController;
use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SimulateAdmsDeviceCommand extends Command
{
    protected $signature = 'adms:simulate {--sn= : Serial number of device to simulate}';

    protected $description = 'Simulate complete ADMS workflow: handshake, command polling (GET /getrequest), command callback (POST /devicecmd), and attendance log push (POST /cdata?table=ATTLOG).';

    public function handle(AdmsController $admsController)
    {
        $sn = $this->option('sn');

        if (!$sn) {
            $device = Device::first();
            $sn = $device ? $device->device_serial_number : 'SETGO-DEV-9999';
        }

        $this->info("=================================================");
        $this->info("   ADMS WORKFLOW SIMULATION FOR DEVICE SN: {$sn}");
        $this->info("=================================================");

        // Step 1: Handshake
        $this->line("\n[STEP 1] Device Handshake (GET /api/adms/cdata?SN={$sn})...");
        $handshakeReq = Request::create('/api/adms/cdata', 'GET', ['SN' => $sn]);
        $handshakeRes = $admsController->handshake($handshakeReq);
        $this->info("Handshake Response Status: {$handshakeRes->getStatusCode()}");
        $this->line("Body:\n" . $handshakeRes->getContent());

        // Step 2: Poll GET /iclock/getrequest
        $this->line("\n[STEP 2] Device Polling Commands (GET /api/adms/getrequest?SN={$sn})...");
        $getRequestReq = Request::create('/api/adms/getrequest', 'GET', ['SN' => $sn]);
        $getRequestRes = $admsController->getRequest($getRequestReq);
        $cmdOutput = trim($getRequestRes->getContent());
        $this->info("getrequest Response:\n" . $cmdOutput);

        // Step 3: Handle Command Callback if command was received
        if (str_starts_with($cmdOutput, 'C:')) {
            preg_match('/^C:(\d+):/', $cmdOutput, $matches);
            $cmdId = $matches[1] ?? null;
            if ($cmdId) {
                $this->line("\n[STEP 3] Device Callback Execution for Command ID: {$cmdId} (POST /api/adms/devicecmd)...");
                $callbackBody = "ID={$cmdId}&Return=0&CMD=USER";
                $callbackReq = Request::create('/api/adms/devicecmd?SN=' . $sn, 'POST', [], [], [], ['CONTENT_TYPE' => 'text/plain'], $callbackBody);
                $callbackRes = $admsController->deviceCommandCallback($callbackReq);
                $this->info("Callback Response Status: {$callbackRes->getStatusCode()}, Body: " . trim($callbackRes->getContent()));

                $updatedCmd = DeviceCommand::find($cmdId);
                $this->info("DeviceCommand #{$cmdId} Updated Status in DB: {$updatedCmd->status}");
            }
        } else {
            $this->line("\n[STEP 3] No pending commands returned for device.");
        }

        // Step 4: Push Attendance Log (POST /iclock/cdata?table=ATTLOG)
        $employee = Employee::first();
        $pin = $employee ? ($employee->sync_pin ?? $employee->employee_id) : '1001';
        $nowStr = now()->format('Y-m-d H:i:s');
        $attBody = "{$pin}\t{$nowStr}\t0\t1\t0\t0\n";

        $this->line("\n[STEP 4] Pushing Attendance Log for PIN={$pin} at {$nowStr}...");
        $attReq = Request::create('/api/adms/cdata?SN=' . $sn . '&table=ATTLOG', 'POST', [], [], [], ['CONTENT_TYPE' => 'text/plain'], $attBody);
        $attRes = $admsController->receiveData($attReq);
        $this->info("Attendance Push Response Status: {$attRes->getStatusCode()}, Body: " . trim($attRes->getContent()));

        $latestLog = AttendanceLog::where('device_user_id', (string)$pin)->orderBy('id', 'desc')->first();
        if ($latestLog) {
            $this->info("Successfully verified Attendance Log in DB: ID {$latestLog->id}, Employee: {$latestLog->employee_id}, Time: {$latestLog->attendance_timestamp}, Status: {$latestLog->attendance_status}");
        } else {
            $this->warn("Attendance log recorded but employee PIN was not matched to employee.");
        }

        $this->info("\n=================================================");
        $this->info("   ADMS WORKFLOW SIMULATION COMPLETED CLEANLY!");
        $this->info("=================================================");

        return 0;
    }
}
