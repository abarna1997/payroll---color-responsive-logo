<?php

namespace App\Services\Adms;

use App\Models\Employee;

class AdmsCommandService
{
    /**
     * Generate the DATA UPDATE USERINFO command string for ZKTeco ADMS user creation.
     * Protocol: DATA UPDATE USERINFO PIN=<User ID>\tName=<Name>\tPri=<Privilege>\tGrp=<Group>\tTZ=<Timezone>\tCard=<Card Number>
     * 
     * @param Employee $employee
     * @param int $commandId
     * @return string
     */
    public function generateUserCreateCommand(Employee $employee, int $commandId): string
    {
        return $this->generateUserUpdateCommand($employee, $commandId);
    }

    /**
     * Generate the DATA UPDATE USERINFO command string for ZKTeco ADMS.
     * Format: DATA UPDATE USERINFO PIN=<User ID>\tName=<Name>\tPri=<Privilege>\tGrp=<Group>\tTZ=<Timezone>\tCard=<Card Number>
     * 
     * @param Employee $employee
     * @param int $commandId
     * @return string
     */
    public function generateUserUpdateCommand(Employee $employee, int $commandId): string
    {
        // Use strictly numeric sync_pin for maximum device compatibility
        $pin = $employee->sync_pin ?? preg_replace('/[^0-9]/', '', $employee->device_user_id ?? $employee->employee_id);
        if (empty($pin)) {
            $pin = (string) $employee->id;
        }
        
        // Clean name (alphanumeric and spaces only), trimmed to max 24 chars for firmware limits
        $fullName = trim($employee->first_name . ' ' . $employee->last_name);
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $fullName);
        $name = substr(trim($name), 0, 24);
        if (empty($name)) {
            $name = 'User' . $pin;
        }
        
        $privilege = $employee->privilege ?? 0;
        
        $command = "DATA UPDATE USERINFO PIN={$pin}\tName={$name}\tPri={$privilege}\tGrp=1\tTZ=0";
        
        if (!empty($employee->card_number)) {
            $command .= "\tCard={$employee->card_number}";
        }
        
        return $command;
    }

    /**
     * Generate the DATA DELETE USERINFO command string for ZKTeco ADMS.
     * Format: DATA DELETE USERINFO PIN=<User ID>
     * 
     * @param string $deviceUserId
     * @param int $commandId
     * @return string
     */
    public function generateUserDeleteCommand(string $deviceUserId, int $commandId): string
    {
        return "DATA DELETE userinfo PIN={$deviceUserId}";
    }

    /**
     * Generate REBOOT command.
     */
    public function generateRebootCommand(): string
    {
        return "REBOOT";
    }

    /**
     * Generate SYNC_TIME / SET_TIME command.
     */
    public function generateSyncTimeCommand(?string $dateTime = null): string
    {
        $timeStr = $dateTime ?? date('Y-m-d H:i:s');
        return "SET OPTIONS Time={$timeStr}";
    }

    /**
     * Generate REFRESH_DATA / DOWNLOAD_USERS command.
     */
    public function generateRefreshUsersCommand(): string
    {
        return "DATA QUERY USERINFO";
    }

    /**
     * Generate GET_ATTLOG command.
     */
    public function generateGetAttLogCommand(): string
    {
        return "DATA QUERY ATTLOG";
    }

    /**
     * Generate CLEAR_ATTLOG command.
     */
    public function generateClearAttLogCommand(): string
    {
        return "CLEAR LOG";
    }

    /**
     * Generate CLEAR_USERS command.
     */
    public function generateClearUsersCommand(): string
    {
        return "CLEAR DATA";
    }

    /**
     * Generate GET_DEVICE_INFO command.
     */
    public function generateGetDeviceInfoCommand(): string
    {
        return "GET OPTIONS ~IPAddress,SubnetMask,GateWay,FWVersion,UserCount,FPCount,FaceCount,CardCount,MaxUserCount,MaxAttLogCount";
    }

    /**
     * Generate ENROLL_FP remote fingerprint enrollment trigger command.
     * Format: ENROLL_FP PIN=<User ID>\tFID=<Finger ID 0-9>\tRENEW=1
     */
    public function generateEnrollFingerprintCommand(string $pin, int $fingerId = 0): string
    {
        return "ENROLL_FP PIN={$pin}\tFID={$fingerId}\tRENEW=1";
    }

    /**
     * Generate ENROLL_FACE remote face enrollment trigger command.
     * Format: ENROLL_FACE PIN=<User ID>\tRENEW=1
     */
    public function generateEnrollFaceCommand(string $pin): string
    {
        return "ENROLL_FACE PIN={$pin}\tRENEW=1";
    }

    /**
     * Generate DATA QUERY FPTEMPLATE / BIODATA command to request templates back from device.
     */
    public function generateQueryBioDataCommand(string $pin): string
    {
        return "DATA QUERY BIODATA PIN={$pin}";
    }

    /**
     * Generate DATA UPDATE USERPIC command string to dispatch uploaded profile photo to terminal for display.
     * Protocol: DATA UPDATE USERPIC PIN=<User ID>\tSize=<Size>\tContent=<Base64Image>
     */
    public function generateUserPicCommand(Employee $employee, string $photoPath): ?string
    {
        $pin = $employee->sync_pin ?? preg_replace('/[^0-9]/', '', $employee->device_user_id ?? $employee->employee_id);
        if (empty($pin)) {
            $pin = (string) $employee->id;
        }

        $fullPath = public_path($photoPath);
        if (!file_exists($fullPath)) {
            return null;
        }

        $imageData = file_get_contents($fullPath);
        if (!$imageData) {
            return null;
        }

        $base64 = base64_encode($imageData);
        $size = strlen($imageData);

        return "DATA UPDATE USERPIC PIN={$pin}\tSize={$size}\tContent={$base64}";
    }
}

