<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceEventLog;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    private function normalizeSerialNumber(?string $serial): string
    {
        $serial = strtoupper(trim((string) $serial));

        return preg_replace('/\s+/', '', $serial) ?? '';
    }

    private function normalizeResponseKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? $key;

        return trim($key, '_');
    }

    private function parsedValue(array $parsedData, array $keys): mixed
    {
        $normalized = [];

        foreach ($parsedData as $key => $value) {
            $normalized[$this->normalizeResponseKey((string) $key)] = $value;
        }

        foreach ($keys as $key) {
            $normalizedKey = $this->normalizeResponseKey($key);
            if (array_key_exists($normalizedKey, $normalized)) {
                return $normalized[$normalizedKey];
            }
        }

        return null;
    }

    private function extractFailureReason(array $parsedData, string $rawBody, string $returnVal): string
    {
        $reason = $this->parsedValue($parsedData, [
            'FailureReason',
            'Reason',
            'Error',
            'ErrorMessage',
            'Message',
            'Result',
            'Description',
            'ReturnMessage',
        ]);

        if (is_string($reason) && trim($reason) !== '') {
            return trim($reason);
        }

        $rawBody = trim($rawBody);
        if ($rawBody !== '' && $rawBody !== 'OK') {
            return $returnVal === '0'
                ? 'Device returned a non-standard success response.'
                : 'Device response: ' . $rawBody;
        }

        return $returnVal === '0'
            ? 'No failure reason reported by the device.'
            : 'Device returned error code ' . $returnVal . ' without a detailed message.';
    }

    /**
     * Handshake / Option sync endpoint (GET /iclock/cdata)
     *
     * @OA\Get(
     *     path="/iclock/cdata",
     *     tags={"ADMS"},
     *     summary="Device Handshake",
     *     description="Initial connection handshake for ZKTeco ADMS device.",
     *     @OA\Parameter(
     *         name="SN",
     *         in="query",
     *         description="Device Serial Number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(mediaType="text/plain")
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function handshake(Request $request)
    {
        $sn = $this->normalizeSerialNumber($request->query('SN'));

        if (empty($sn)) {
            // Log a critical event for malformed/missing serial connections
            DeviceEventLog::create([
                'device_id' => null,
                'event_type' => 'CONNECTION_ERROR',
                'event_message' => 'ADMS connection attempt rejected: missing SN query parameter.',
                'severity' => 'Critical',
                'raw_payload' => json_encode($request->all()),
            ]);

            return response('ERROR: SN required', 400)->header('Content-Type', 'text/plain');
        }

        // Search for existing device
        $device = Device::where('device_serial_number', $sn)->first();

        if ($device) {
            // Update last seen and IP
            $device->public_ip_address = $request->ip();
            $device->last_seen = now();
            $device->save();

            // Log reconnection if needed
            DeviceEventLog::create([
                'device_id' => $device->id,
                'event_type' => 'DEVICE_CONNECTED',
                'event_message' => "Device Serial {$sn} reconnected and synced configurations.",
                'severity' => 'Info',
            ]);
        } else {
            // Auto discovery: create device record in pending state
            $device = Device::create([
                'device_name' => 'Auto-Discovered ' . $sn,
                'device_serial_number' => $sn,
                'status' => 'Pending Approval',
                'timezone' => 'Asia/Colombo',
                'public_ip_address' => $request->ip(),
                'last_seen' => now(),
            ]);

            DeviceEventLog::create([
                'device_id' => $device->id,
                'event_type' => 'DEVICE_DISCOVERED',
                'event_message' => "New device discovered automatically. Serial: {$sn}. Awaiting administrator approval.",
                'severity' => 'Warning',
            ]);

            // Auto-Discovery Email Alert to Super Administrator (Logged for V1.1)
            Log::info("ALERT MAIL TO SUPER ADMIN: New ZKTeco device detected with Serial Number: {$sn}. Status is Pending Approval.");
        }

        // Return configuration response options
        $response = "GET OPTION FROM: {$sn}\n" .
            "Stamp=1\n" .
            "OpStamp=1\n" .
            "PhotoStamp=1\n" .
            "ErrorDelay=30\n" .
            "Delay=10\n" .
            "TransTimes=00:00;23:59\n" .
            "TransInterval=1\n" .
            "TransFlag=1111111111\n" .
            "Realtime=1\n" .
            "Encrypt=0\n";

        return response($response, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Log Uploader / Heartbeat Data endpoint (POST /iclock/cdata)
     *
     * @OA\Post(
     *     path="/iclock/cdata",
     *     tags={"ADMS"},
     *     summary="Receive Data from Device",
     *     description="Receives ATTLOG, USER, BIODATA, or OPTIONS data from the terminal.",
     *     @OA\Parameter(
     *         name="SN",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="table",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"ATTLOG", "USER", "BIODATA", "OPTIONS"})
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="text/plain",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Returns OK")
     * )
     */
    public function receiveData(Request $request)
    {
        $sn = $this->normalizeSerialNumber($request->query('SN'));
        $table = $request->query('table');

        // Capture raw payload for ADMS diagnostics
        @file_put_contents(storage_path('logs/adms_debug.log'), '[' . date('Y-m-d H:i:s') . '] table=' . $table . ' | SN=' . $sn . ' | body=' . str_replace("\n", '\\n', $request->getContent()) . "\n", FILE_APPEND);

        if (empty($sn)) {
            return response('ERROR: SN required', 400)->header('Content-Type', 'text/plain');
        }

        $device = Device::where('device_serial_number', $sn)->first();

        // If device doesn't exist, auto-create it
        if (!$device) {
            $device = Device::create([
                'device_name' => 'Auto-Discovered ' . $sn,
                'device_serial_number' => $sn,
                'status' => 'Pending Approval',
                'timezone' => 'Asia/Colombo',
                'public_ip_address' => $request->ip(),
                'last_seen' => now(),
            ]);
            Log::info("ALERT MAIL TO SUPER ADMIN: New ZKTeco device detected during upload: {$sn}.");
        }

        $device->public_ip_address = $request->ip();
        $device->last_seen = now();
        $device->save();

        $rawBody = $request->getContent();

        if (strtoupper($table) === 'ATTLOG' && !empty($rawBody)) {
            $device->last_attendance_received = now();
            $device->save();

            $lines = explode("\n", $rawBody);
            
            // PRE-LOAD SETTINGS
            $lateGrace = (int) (\App\Models\Setting::getVal('Attendance', 'LateGracePeriod') ?? 5);
            $earlyOutGrace = (int) (\App\Models\Setting::getVal('Attendance', 'EarlyOutGracePeriod') ?? 0);
            $otStartMinutes = (int) (\App\Models\Setting::getVal('Attendance', 'OvertimeStartAfterMinutes') ?? 30);
            $graceMinutes = ($lateGrace > 5) ? $lateGrace : 14;

            // PRE-PARSE LINES AND EXTRACT PINS
            $parsedLogs = [];
            $pinsToFetch = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $fields = explode("\t", $line);
                if (count($fields) >= 2) {
                    $pin = trim($fields[0]);
                    $timestampStr = trim($fields[1]);
                    $statusValue = trim($fields[2] ?? '0');
                    $verifyCode = trim($fields[3] ?? '0');

                    try {
                        $timestamp = Carbon::parse($timestampStr);
                    } catch (\Exception $e) {
                        continue;
                    }
                    
                    $pinsToFetch[] = $pin;
                    $parsedLogs[] = [
                        'pin' => $pin,
                        'timestamp' => $timestamp,
                        'statusValue' => $statusValue,
                        'verifyCode' => $verifyCode,
                        'raw_data' => $line
                    ];
                }
            }

            // EAGER LOAD EMPLOYEES
            $employees = Employee::whereIn('sync_pin', array_unique($pinsToFetch))
                ->orWhereIn('employee_id', array_unique($pinsToFetch))
                ->with(['shift', 'secondaryShift']) // Ensure shifts are eager loaded if needed
                ->get()
                ->keyBy(function ($emp) {
                    return $emp->sync_pin ?: $emp->employee_id;
                });

            $bulkInsertData = [];
            
            foreach ($parsedLogs as $log) {
                $pin = $log['pin'];
                $timestamp = $log['timestamp'];
                
                $employee = $employees->get($pin);
                $employeeId = $employee ? $employee->id : null;

                $verifyMethod = 'Other';
                switch ($log['verifyCode']) {
                    case '1': case '2': $verifyMethod = 'Fingerprint'; break;
                    case '15': $verifyMethod = 'Face'; break;
                    case '4': $verifyMethod = 'RFID Card'; break;
                    case '3': $verifyMethod = 'PIN'; break;
                }

                $attendanceType = 'Check-In';
                switch ($log['statusValue']) {
                    case '0': $attendanceType = 'Check-In'; break;
                    case '1': $attendanceType = 'Check-Out'; break;
                    case '2': $attendanceType = 'Break-Out'; break;
                    case '3': $attendanceType = 'Break-In'; break;
                }

                $attendanceStatus = 'Present';
                if ($employeeId && ($employee->shift_id || $employee->secondary_shift_id)) {
                    $shift = $employee->getMatchingShift($timestamp->toTimeString());
                    if ($shift) {
                        $shiftStart = Carbon::parse($shift->start_time);
                        $shiftEnd = Carbon::parse($shift->end_time);
                        $punchTime = Carbon::parse($timestamp->toTimeString());

                        if ($attendanceType === 'Check-In') {
                            $earliestIn = $shiftStart->copy()->subMinutes(45);
                            $lateThreshold = $shiftStart->copy()->addMinutes($graceMinutes + 1);
                            $absentThreshold = $shiftStart->copy()->addMinutes(60);

                            if ($punchTime->lessThan($earliestIn)) continue;

                            if ($punchTime->greaterThanOrEqualTo($absentThreshold)) {
                                $attendanceStatus = 'Absent';
                            } elseif ($punchTime->greaterThanOrEqualTo($lateThreshold)) {
                                $attendanceStatus = 'Late';
                            } else {
                                $attendanceStatus = 'Present';
                            }
                        } elseif ($attendanceType === 'Check-Out') {
                            $earlyOutLimit = $shiftEnd->copy()->subMinutes(120);
                            $normalOutLimit = $shiftEnd;

                            if ($punchTime->lessThan($earlyOutLimit)) {
                                $attendanceStatus = 'Invalid Out';
                            } elseif ($punchTime->lessThan($normalOutLimit)) {
                                $attendanceStatus = 'Early Out';
                            } else {
                                $otLimit = $shiftEnd->copy()->addMinutes($otStartMinutes);
                                if ($punchTime->greaterThanOrEqualTo($otLimit)) {
                                    $attendanceStatus = 'Overtime';
                                } else {
                                    $attendanceStatus = 'Present';
                                }
                            }
                        }
                    }
                }

                $bulkInsertData[] = [
                    'employee_id' => $employeeId,
                    'device_id' => $device->id,
                    'device_user_id' => $pin,
                    'attendance_date' => $timestamp->toDateString(),
                    'attendance_time' => $timestamp->toTimeString(),
                    'attendance_timestamp' => $timestamp->toDateTimeString(),
                    'verification_method' => $verifyMethod,
                    'verify_code' => $log['verifyCode'],
                    'device_serial' => $sn,
                    'source' => $verifyMethod,
                    'attendance_status' => $attendanceStatus,
                    'attendance_type' => $attendanceType,
                    'raw_data' => $log['raw_data'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // BULK UPSERT (ignores duplicates using the unique constraints on device_id, device_user_id, attendance_timestamp)
            $importedCount = 0;
            if (count($bulkInsertData) > 0) {
                // Because some drivers don't return accurate counts for insertOrIgnore, we count pre-insert.
                // It's acceptable for the log to just say "Attempted to sync X logs".
                AttendanceLog::insertOrIgnore($bulkInsertData);
                $importedCount = count($bulkInsertData);
                
                // Process these via the engine (in production, dispatch to a Job/Queue)
                $engine = app(\App\Services\AttendanceEngineService::class);
                foreach ($bulkInsertData as $logData) {
                    $log = new AttendanceLog($logData);
                    $engine->processRawLog($log);
                }
            }

            DeviceEventLog::create([
                'device_id' => $device->id,
                'event_type' => 'ATTENDANCE_RECEIVED',
                'event_message' => "Attendance logs received. Attempted to sync {$importedCount} logs (duplicates ignored automatically).",
                'severity' => 'Info',
                'raw_payload' => $rawBody,
            ]);
        }

        if ((strtoupper($table) === 'USER' || strtoupper($table) === 'USERINFO' || strtoupper($table) === 'OPERLOG') && !empty($rawBody)) {
            $lines = explode("\n", $rawBody);
            
            $usersToProcess = [];
            $pinsToFetch = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $fields = explode("\t", $line);
                $parsed = [];
                foreach ($fields as $field) {
                    if (str_contains($field, '=')) {
                        $parts = explode('=', $field, 2);
                        $parsed[trim($parts[0])] = trim($parts[1]);
                    }
                }

                $pin = $parsed['USER PIN'] ?? ($parsed['PIN'] ?? ($parsed['Pin'] ?? null));
                if ($pin) {
                    $pinsToFetch[] = $pin;
                    $usersToProcess[] = [
                        'pin' => $pin,
                        'name' => trim($parsed['Name'] ?? 'DeviceUser')
                    ];
                }
            }

            // EAGER LOAD EMPLOYEES
            $employees = Employee::whereIn('sync_pin', array_unique($pinsToFetch))
                ->orWhereIn('employee_id', array_unique($pinsToFetch))
                ->get()
                ->keyBy(function ($emp) {
                    return $emp->sync_pin ?: $emp->employee_id;
                });

            // CACHE COMPANIES & DEPARTMENTS
            $defaultCompany = Company::first();
            $defaultCompanyId = $device->company_id ?: ($defaultCompany ? $defaultCompany->id : null);
            
            $primeOneCompany = Company::where('company_name', 'like', '%Prime One%')->first();
            $altitudeCompany = Company::where('company_name', 'like', '%Altitude%')->first();
            
            $departmentCache = []; // Cache to prevent repetitive default dept queries
            $importedCount = 0;

            foreach ($usersToProcess as $user) {
                $pin = $user['pin'];
                $fullName = empty($user['name']) ? 'DeviceUser ' . $pin : $user['name'];
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? 'User';

                $employee = $employees->get($pin);

                if ($employee) {
                    // Update existing
                    $employee->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName
                    ]);
                } else {
                    // CREATE NEW EMPLOYEE (PIN Mapping Logic)
                    $targetCompanyId = $defaultCompanyId;
                    
                    if (str_starts_with($pin, '10') && $primeOneCompany) {
                        $targetCompanyId = $primeOneCompany->id;
                    } elseif (str_starts_with($pin, '20') && $altitudeCompany) {
                        $targetCompanyId = $altitudeCompany->id;
                    }
                    
                    if ($targetCompanyId) {
                        if (!isset($departmentCache[$targetCompanyId])) {
                            $dept = Department::where('company_id', $targetCompanyId)->first();
                            if (!$dept) {
                                $c = Company::find($targetCompanyId);
                                $dept = Department::create([
                                    'company_id' => $targetCompanyId,
                                    'department_code' => ($c ? $c->company_code : 'DFT') . '-DFT',
                                    'department_name' => 'Default Department',
                                ]);
                            }
                            $departmentCache[$targetCompanyId] = $dept->id;
                        }

                        preg_match('/\d+$/', $pin, $matches);
                        $employeeNumber = isset($matches[0]) ? (int) $matches[0] : 1;

                        // Quick duplicate protection for employee number
                        while(Employee::where('company_id', $targetCompanyId)->where('employee_number', $employeeNumber)->exists()) {
                            $employeeNumber++;
                        }

                        $newEmp = Employee::create([
                            'company_id' => $targetCompanyId,
                            'branch_id' => $device->branch_id,
                            'department_id' => $departmentCache[$targetCompanyId],
                            'employee_id' => $pin,
                            'sync_pin' => $pin,
                            'employee_number' => $employeeNumber,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'status' => 'Active',
                        ]);
                        
                        $employees->put($pin, $newEmp); // Add to cache for subsequent checks
                        $importedCount++;
                    }
                }
            }

            DeviceEventLog::create([
                'device_id' => $device->id,
                'event_type' => 'USERS_SYNCED',
                'event_message' => "Successfully processed user sync. Auto-created {$importedCount} new employees based on PIN mapping.",
                'severity' => 'Info',
                'raw_payload' => $rawBody,
            ]);
        }

        if ((strtoupper($table) === 'BIODATA' || strtoupper($table) === 'FPTEMPLATE' || strtoupper($table) === 'BIOPHOTO') && !empty($rawBody)) {
            $lines = explode("\n", $rawBody);
            
            $biosToProcess = [];
            $pinsToFetch = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $fields = explode("\t", $line);
                $parsed = [];
                foreach ($fields as $field) {
                    if (str_contains($field, '=')) {
                        $parts = explode('=', $field, 2);
                        $parsed[trim($parts[0])] = trim($parts[1]);
                    }
                }

                $pin = $parsed['PIN'] ?? ($parsed['Pin'] ?? null);
                if ($pin) {
                    $pinsToFetch[] = $pin;
                    $biosToProcess[] = [
                        'pin' => $pin,
                        'parsed' => $parsed,
                        'raw_line' => $line
                    ];
                }
            }

            $employees = Employee::whereIn('sync_pin', array_unique($pinsToFetch))
                ->orWhereIn('employee_id', array_unique($pinsToFetch))
                ->get()
                ->keyBy(function ($emp) {
                    return $emp->sync_pin ?: $emp->employee_id;
                });

            $importedBioCount = 0;

            foreach ($biosToProcess as $bio) {
                $pin = $bio['pin'];
                $parsed = $bio['parsed'];
                $employee = $employees->get($pin);

                if ($employee) {
                    $bioType = isset($parsed['FID']) ? 'fingerprint' : 'face';
                    $fingerPos = null;

                    if ($bioType === 'fingerprint') {
                        $fid = (int) ($parsed['FID'] ?? 0);
                        $map = [0=>'right_thumb', 1=>'right_index', 2=>'right_middle', 3=>'right_ring', 4=>'right_little', 5=>'left_thumb', 6=>'left_index', 7=>'left_middle', 8=>'left_ring', 9=>'left_little'];
                        $fingerPos = $map[$fid] ?? 'right_index';
                    }

                    $templateStr = $parsed['Tmp'] ?? ($parsed['TMP'] ?? ($parsed['Template'] ?? $bio['raw_line']));

                    \App\Models\BiometricTemplate::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'biometric_type' => $bioType,
                            'finger_position' => $fingerPos,
                        ],
                        [
                            'template_id' => $parsed['Valid'] ?? '1',
                            'template_version' => $parsed['Ver'] ?? '10.0',
                            'raw_template' => $templateStr,
                            'device_id' => $device->id,
                            'device_serial_number' => $sn,
                            'firmware_version' => $device->firmware_version,
                            'enrollment_source' => 'ADMS Automatic Push',
                            'raw_table_source' => strtoupper($table),
                            'enrolled_at' => now(),
                            'synchronized_at' => now(),
                            'status' => 'Synchronized',
                        ]
                    );

                    if ($bioType === 'fingerprint') {
                        $employee->update(['fingerprint_enrolled' => true]);
                    } else {
                        $employee->update(['face_enrolled' => true]);
                    }
                    $importedBioCount++;
                }
            }

            DeviceEventLog::create([
                'device_id' => $device->id,
                'event_type' => 'BIOMETRICS_RECEIVED',
                'event_message' => "Received and processed {$importedBioCount} biometric templates.",
                'severity' => 'Info',
            ]);
        }

        if (strtoupper($table) === 'OPTIONS' && !empty($rawBody)) {
            $lines = explode("\n", str_replace(',', "\n", $rawBody));
            $options = [];
            foreach ($lines as $line) {
                if (str_contains($line, '=')) {
                    $parts = explode('=', trim($line), 2);
                    $options[trim($parts[0])] = trim($parts[1]);
                }
            }

            $userCount = $this->parsedValue($options, ['UserCount']);
            $fpCount = $this->parsedValue($options, ['FPCount']);
            $faceCount = $this->parsedValue($options, ['FaceCount']);
            $cardCount = $this->parsedValue($options, ['CardCount']);
            $fwVersion = $this->parsedValue($options, ['FWVersion', 'PushVersion']);
            $ipAddress = $this->parsedValue($options, ['IPAddress']);
            $maxUserCount = $this->parsedValue($options, ['MaxUserCount']);
            $maxAttLogCount = $this->parsedValue($options, ['MaxAttLogCount']);

            if ($userCount !== null) $device->user_count = (int) $userCount;
            if ($fpCount !== null) $device->fingerprint_count = (int) $fpCount;
            if ($faceCount !== null) $device->face_count = (int) $faceCount;
            if ($cardCount !== null) $device->card_count = (int) $cardCount;
            if ($fwVersion !== null) $device->firmware_version = (string) $fwVersion;
            if ($ipAddress !== null) $device->ip_address = (string) $ipAddress;
            if ($maxUserCount !== null) $device->storage_capacity = (int) $maxUserCount;

            $device->last_info_sync = now();
            $device->save();
        }

        return response("OK\n", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Outbound Command Dispatcher endpoint (GET /iclock/getrequest)
     *
     * @OA\Get(
     *     path="/iclock/getrequest",
     *     tags={"ADMS"},
     *     summary="Command Polling",
     *     description="Device polls this endpoint to fetch pending server commands.",
     *     @OA\Parameter(
     *         name="SN",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Returns ADMS commands as plain text")
     * )
     */
    public function getRequest(Request $request)
    {
        $sn = $this->normalizeSerialNumber($request->query('SN'));

        if (empty($sn)) {
            return response('ERROR: SN required', 400)->header('Content-Type', 'text/plain');
        }

        $device = Device::where('device_serial_number', $sn)->first();

        if (!$device) {
            return response("OK\n", 200)->header('Content-Type', 'text/plain');
        }

        $device->public_ip_address = $request->ip();
        $device->last_seen = now();
        $device->save();

        // Get oldest pending command for this device
        $command = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->first();

        if ($command) {
            $command->status = 'sent';
            $command->sent_at = now();
            $command->save();

            $commandText = $command->command;

            // Map friendly action aliases to exact ZKTeco ADMS protocol command strings
            if ($commandText === 'NETWORK_INFO' || $commandText === 'GET_DEVICE_INFO') {
                $commandText = 'GET OPTIONS ~IPAddress,SubnetMask,GateWay,FWVersion,UserCount,FPCount,FaceCount,CardCount';
            } elseif ($commandText === 'REFRESH_USERS' || $commandText === 'REFRESH_DATA' || $commandText === 'DOWNLOAD_USERS') {
                $commandText = 'DATA QUERY USERINFO';
            } elseif ($commandText === 'GET_ATTLOG') {
                $commandText = 'DATA QUERY ATTLOG';
            } elseif ($commandText === 'SYNC_TIME' || $commandText === 'SET_TIME') {
                $commandText = 'SET OPTIONS Time=' . date('Y-m-d H:i:s');
            } elseif ($commandText === 'CLEAR_ATTLOG') {
                $commandText = 'CLEAR LOG';
            } elseif ($commandText === 'CLEAR_USERS') {
                $commandText = 'CLEAR DATA';
            }

            // If the command already begins with C:{id}:, don't duplicate the prefix
            if (str_starts_with($commandText, 'C:')) {
                $admsCommandStr = "{$commandText}\n";
            } else {
                $admsCommandStr = "C:{$command->id}:{$commandText}\n";
            }

            return response($admsCommandStr, 200)->header('Content-Type', 'text/plain');
        }

        return response("OK\n", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Command execution updates callback (POST /iclock/devicecmd)
     *
     * @OA\Post(
     *     path="/iclock/devicecmd",
     *     tags={"ADMS"},
     *     summary="Command Callback",
     *     description="Device sends the result of a dispatched command back to the server.",
     *     @OA\Parameter(
     *         name="SN",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="text/plain")
     *     ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function deviceCommandCallback(Request $request)
    {
        $sn = $this->normalizeSerialNumber($request->query('SN'));

        if (empty($sn)) {
            return response('ERROR: SN required', 400)->header('Content-Type', 'text/plain');
        }

        $device = Device::where('device_serial_number', $sn)->first();
        if ($device) {
            $device->public_ip_address = $request->ip();
            $device->last_seen = now();
            $device->save();
        }

        $rawBody = $request->getContent();

        if (!empty($rawBody)) {
            $lines = explode("\n", $rawBody);
            $parsedData = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if (str_contains($line, '&')) {
                    parse_str($line, $lineParams);
                    $parsedData = array_merge($parsedData, $lineParams);
                } elseif (str_contains($line, '=')) {
                    parse_str($line, $lineParams);
                    $parsedData = array_merge($parsedData, $lineParams);
                }
            }

            parse_str($rawBody, $globalParams);
            if (count($globalParams) > 1) {
                $parsedData = array_merge($parsedData, $globalParams);
            }

            $cmdId = $parsedData['ID'] ?? null;
            $returnVal = $parsedData['Return'] ?? '-1';

            if ($cmdId) {
                $command = DeviceCommand::find($cmdId);
                if ($command) {
                    if ($returnVal === '0') {
                        $command->status = 'completed';
                        $command->failure_reason = null;
                        
                        if ($command->command_type === 'CREATE_USER' && $command->employee_id) {
                            $empToUpdate = Employee::find($command->employee_id);
                            if ($empToUpdate) {
                                $empToUpdate->biometric_status = 'User Synchronized';
                                $empToUpdate->save();
                            }
                        }
                    } else {
                        $reason = $this->extractFailureReason($parsedData, $rawBody, $returnVal);
                        $maxRetries = 3;

                        if ($command->retry_count < $maxRetries) {
                            $command->retry_count += 1;
                            $command->status = 'pending';
                            $command->failure_reason = "Retry #{$command->retry_count}: {$reason}";
                        } else {
                            $command->status = 'failed';
                            $command->failure_reason = "Max retries reached ({$maxRetries}): {$reason}";
                        }
                    }
                    $command->completed_at = now();
                    if ($command->sent_at) {
                        $command->execution_time_ms = (int) $command->completed_at->diffInMilliseconds($command->sent_at, true);
                    }
                    $command->response = $rawBody;
                    $command->save();

                    // Update device metadata if returned from command response
                    if ($device) {
                        $updated = false;
                        if (($ipAddress = $this->parsedValue($parsedData, ['IPAddress', 'IP', 'IpAddress', 'Ip'])) !== null) {
                            $device->ip_address = $ipAddress;
                            $updated = true;
                        }
                        if (($firmwareVersion = $this->parsedValue($parsedData, ['FirmwareVersion', 'FWVersion', 'Version'])) !== null) {
                            $device->firmware_version = $firmwareVersion;
                            $updated = true;
                        }
                        if (($sdkVersion = $this->parsedValue($parsedData, ['SDKVersion', 'SdkVersion'])) !== null) {
                            $device->sdk_version = $sdkVersion;
                            $updated = true;
                        }
                        if (($userCount = $this->parsedValue($parsedData, ['UserCount'])) !== null) {
                            $device->user_count = (int) $userCount;
                            $updated = true;
                        }
                        if (($attendanceCount = $this->parsedValue($parsedData, ['AttendanceCount', 'AttCount', 'DeviceAttendanceCount'])) !== null) {
                            $device->device_attendance_count = (int) $attendanceCount;
                            $updated = true;
                        }
                        if (($faceCount = $this->parsedValue($parsedData, ['FaceCount'])) !== null) {
                            $device->face_count = (int) $faceCount;
                            $updated = true;
                        }
                        if (($fingerprintCount = $this->parsedValue($parsedData, ['FPCount', 'FingerprintCount'])) !== null) {
                            $device->fingerprint_count = (int) $fingerprintCount;
                            $updated = true;
                        }
                        if (($cardCount = $this->parsedValue($parsedData, ['CardCount'])) !== null) {
                            $device->card_count = (int) $cardCount;
                            $updated = true;
                        }
                        if (($photoCount = $this->parsedValue($parsedData, ['PhotoCount'])) !== null) {
                            $device->photo_count = (int) $photoCount;
                            $updated = true;
                        }
                        if (($storageCapacity = $this->parsedValue($parsedData, ['StorageCapacity', 'Capacity', 'Storage Capacity', 'storage_capacity'])) !== null) {
                            $device->storage_capacity = (int) $storageCapacity;
                            $updated = true;
                        }
                        if (($storageUsed = $this->parsedValue($parsedData, ['StorageUsed', 'Used', 'Storage Used', 'storage_used'])) !== null) {
                            $device->storage_used = (int) $storageUsed;
                            $updated = true;
                        }
                        if (($storageAvailable = $this->parsedValue($parsedData, ['StorageAvailable', 'Available', 'Storage Available', 'storage_available'])) !== null) {
                            $device->storage_available = (int) $storageAvailable;
                            $updated = true;
                        }

                        // Set info sync timestamp
                        if (str_contains($command->command, 'INFO') || str_contains($command->command, 'NETWORK_INFO') || $updated) {
                            $device->last_info_sync = now();
                            $updated = true;
                        }

                        if ($updated) {
                            $device->save();
                        }
                    }

                    DeviceEventLog::create([
                        'device_id' => $command->device_id,
                        'event_type' => 'COMMAND_EXECUTED',
                        'event_message' => $command->status === 'failed'
                            ? "Command ID {$cmdId} failed. Code: {$returnVal}. Reason: {$command->failure_reason}"
                            : "Command ID {$cmdId} finished with status: {$command->status}. Code: {$returnVal}.",
                        'severity' => $returnVal === '0' ? 'Info' : 'Error',
                        'raw_payload' => $rawBody,
                    ]);
                }
            }
        }

        return response("OK\n", 200)->header('Content-Type', 'text/plain');
    }

    public function receiveQueryData(Request $request)
    {
        $sn = $this->normalizeSerialNumber($request->query('SN'));
        $type = $request->query('type'); // type=user or similar

        // Capture raw payload for ADMS diagnostics
        @file_put_contents(storage_path('logs/adms_debug.log'), '[' . date('Y-m-d H:i:s') . '] QUERYDATA: type=' . $type . ' | SN=' . $sn . ' | body=' . str_replace("\n", '\\n', $request->getContent()) . "\n", FILE_APPEND);

        if (empty($sn)) {
            return response('ERROR: SN required', 400)->header('Content-Type', 'text/plain');
        }

        $device = Device::where('device_serial_number', $sn)->first();
        if (!$device) {
            return response("OK\n", 200)->header('Content-Type', 'text/plain');
        }

        $device->public_ip_address = $request->ip();
        $device->last_seen = now();
        $device->save();

        $rawBody = $request->getContent();

        // If the query type is user
        if ((strtoupper($type) === 'USER' || strtoupper($type) === 'USERINFO' || strtoupper($type) === 'OPERLOG') && !empty($rawBody)) {
            $lines = explode("\n", $rawBody);
            $importedCount = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                // Format: PIN=2\tName=John Doe\tPri=0\t...
                $fields = explode("\t", $line);
                $parsed = [];
                foreach ($fields as $field) {
                    if (str_contains($field, '=')) {
                        $parts = explode('=', $field, 2);
                        $parsed[trim($parts[0])] = trim($parts[1]);
                    }
                }

                $pin = $parsed['USER PIN'] ?? ($parsed['PIN'] ?? ($parsed['Pin'] ?? null));
                if ($pin) {
                    $fullName = trim($parsed['Name'] ?? 'DeviceUser');
                    if (empty($fullName)) {
                        $fullName = 'DeviceUser ' . $pin;
                    }
                    $nameParts = explode(' ', $fullName, 2);
                    $firstName = $nameParts[0];
                    $lastName = isset($nameParts[1]) ? $nameParts[1] : 'User';

                    // Find or create employee
                    $employee = Employee::where('employee_id', $pin)->first();

                    if ($employee) {
                        // Update existing employee
                        $employee->first_name = $firstName;
                        $employee->last_name = $lastName;
                        $employee->save();
                    } else {
                        // Create new employee
                        $companyId = $device->company_id;
                        $branchId = $device->branch_id;

                        // Fallback to any company if device has none
                        if (!$companyId) {
                            $company = Company::first();
                            $companyId = $company ? $company->id : null;
                        } else {
                            $company = Company::find($companyId);
                        }

                        // Must have a company to create employee
                        if ($companyId) {
                            // Find or create default department for company
                            $dept = Department::where('company_id', $companyId)->first();
                            if (!$dept) {
                                $dept = Department::create([
                                    'company_id' => $companyId,
                                    'department_code' => ($company ? $company->company_code : 'DFT') . '-DFT',
                                    'department_name' => 'Default Department',
                                ]);
                            }

                            // Extract employee number from pin
                            preg_match('/\d+$/', $pin, $matches);
                            if (isset($matches[0])) {
                                $employeeNumber = (int) $matches[0];
                            } else {
                                $maxNum = Employee::where('company_id', $companyId)->max('employee_number');
                                $employeeNumber = $maxNum ? $maxNum + 1 : 1;
                            }

                            // Prevent duplicate unique constraint for company_id + employee_number
                            $numExists = Employee::where('company_id', $companyId)->where('employee_number', $employeeNumber)->exists();
                            if ($numExists) {
                                $maxNum = Employee::where('company_id', $companyId)->max('employee_number');
                                $employeeNumber = $maxNum ? $maxNum + 1 : 1;
                            }

                            Employee::create([
                                'company_id' => $companyId,
                                'branch_id' => $branchId,
                                'department_id' => $dept->id,
                                'employee_id' => $pin,
                                'employee_number' => $employeeNumber,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'status' => 'Active',
                            ]);
                            $importedCount++;
                        }
                    }
                }
            }

            DeviceEventLog::create([
                'device_id' => $device->id,
                'event_type' => 'USERS_SYNCED',
                'event_message' => "Successfully synced/imported {$importedCount} users from the device via querydata.",
                'severity' => 'Info',
                'raw_payload' => $rawBody,
            ]);
        }

        return response("OK\n", 200)->header('Content-Type', 'text/plain');
    }
}
