<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\WfhRequest;
use Carbon\Carbon;

class WebPunchEligibilityService
{
    /**
     * Determine if the employee is eligible to use Web Punch today.
     * 
     * Decision Matrix:
     * - NORMAL + No Approved WFH = Fingerprint (Not eligible for Web Punch)
     * - NORMAL + Approved WFH = Eligible
     * - WFH + Remote Punch Enabled = Eligible
     * - WFH + Remote Punch Disabled = Not eligible
     */
    public function checkEligibility(Employee $employee, $latitude = null, $longitude = null)
    {
        $today = Carbon::today('Asia/Colombo')->format('Y-m-d');
        
        $wfhRequest = WfhRequest::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->where('status', 'APPROVED')
            ->first();
            
        // Check WFH & HYBRID Employee policy
        if (in_array($employee->work_mode, ['WFH', 'HYBRID'])) {
            if (!$employee->allow_remote_punch) {
                return [
                    'eligible' => false,
                    'reason' => 'Remote punch is disabled for your account. Please contact HR.'
                ];
            }
            
            if (!$employee->home_latitude || !$employee->home_longitude || !$employee->allowed_radius) {
                return [
                    'eligible' => false,
                    'reason' => 'WFH location is not configured properly. Please contact HR.'
                ];
            }
            
            // GPS validation could go here
            return [
                'eligible' => true,
                'reason' => 'Eligible for Web Punch (WFH Work Mode).'
            ];
        }

        // NORMAL Employee policy
        if (!$wfhRequest) {
            return [
                'eligible' => false,
                'reason' => 'No approved WFH request for today. Please use fingerprint attendance in the office.'
            ];
        }

        return [
            'eligible' => true,
            'reason' => 'Eligible for Web Punch (Temporary WFH Request Approved).'
        ];
    }
}
