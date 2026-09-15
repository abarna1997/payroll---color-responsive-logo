<?php

namespace App\Services\Payroll;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\SalaryProfile;
use Illuminate\Support\Facades\Auth;

class SalaryProfileService
{
    /**
     * Update a single employee's salary profile by creating a new version.
     */
    public function updateProfile(int $employeeId, array $data, string $ipAddress): SalaryProfile
    {
        $effectiveFrom = $data['effective_from'] ?? date('Y-m-d');
        
        // Prevent overlaps: Check if there's a profile that starts on or after the new effective_from
        $futureProfile = SalaryProfile::where('employee_id', $employeeId)
            ->where('status', 'Active')
            ->where('effective_from', '>=', $effectiveFrom)
            ->first();
            
        if ($futureProfile) {
            throw new \Exception("Cannot create a salary profile that overlaps with an existing future profile starting on {$futureProfile->effective_from}.");
        }

        // Find the current active profile (if any) and cap its effective_to date
        $currentProfile = SalaryProfile::where('employee_id', $employeeId)
            ->where('status', 'Active')
            ->whereNull('effective_to')
            ->orderBy('effective_from', 'desc')
            ->first();

        if ($currentProfile) {
            $effectiveTo = \Carbon\Carbon::parse($effectiveFrom)->subDay()->format('Y-m-d');
            
            if ($effectiveTo < $currentProfile->effective_from) {
                 throw new \Exception("New effective date overlaps with the current profile's start date.");
            }
            
            $currentProfile->effective_to = $effectiveTo;
            $currentProfile->save();
        }

        $allowances = [];
        if (isset($data['allowances'])) {
            foreach ($data['allowances'] as $name => $amount) {
                if ($amount !== null && $amount !== '') {
                    $allowances[] = ['name' => $name, 'amount' => (float) $amount];
                }
            }
        }

        $deductions = [];
        if (isset($data['deductions'])) {
            foreach ($data['deductions'] as $name => $amount) {
                if ($amount !== null && $amount !== '') {
                    $deductions[] = ['name' => $name, 'amount' => (float) $amount];
                }
            }
        }

        $profile = SalaryProfile::create([
            'employee_id' => $employeeId,
            'effective_from' => $effectiveFrom,
            'effective_to' => null,
            'status' => 'Active',
            'basic_salary' => $data['basic_salary'],
            'allowances_json' => $allowances,
            'deductions_json' => $deductions,
            'epf_eligible' => isset($data['epf_eligible']),
            'etf_eligible' => isset($data['etf_eligible']),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_SALARY_PROFILE_VERSION',
            'module' => 'Payroll',
            'record_id' => $profile->id,
            'old_value' => $currentProfile ? json_encode($currentProfile) : null,
            'new_value' => json_encode($profile),
            'ip_address' => $ipAddress,
        ]);

        return $profile;
    }

    /**
     * Bulk update multiple salary profiles by creating new versions.
     */
    public function bulkUpdateProfiles(array $data, string $ipAddress): int
    {
        $employeeIds = $data['employee_ids'];
        $updatedCount = 0;
        $effectiveFrom = $data['effective_from'] ?? date('Y-m-d');

        foreach ($employeeIds as $empId) {
            $employee = Employee::find($empId);
            if (!$employee) {
                continue;
            }

            // Prevent overlaps: skip if future profile exists
            $futureProfile = SalaryProfile::where('employee_id', $empId)
                ->where('status', 'Active')
                ->where('effective_from', '>=', $effectiveFrom)
                ->first();
                
            if ($futureProfile) {
                continue; // Skip this employee to prevent overlap
            }

            $currentProfile = SalaryProfile::where('employee_id', $empId)
                ->where('status', 'Active')
                ->whereNull('effective_to')
                ->orderBy('effective_from', 'desc')
                ->first();

            $baseProfileData = [
                'basic_salary' => 0,
                'allowances_json' => [],
                'deductions_json' => [],
                'epf_eligible' => true,
                'etf_eligible' => true
            ];

            if ($currentProfile) {
                $effectiveTo = \Carbon\Carbon::parse($effectiveFrom)->subDay()->format('Y-m-d');
                if ($effectiveTo >= $currentProfile->effective_from) {
                    $currentProfile->effective_to = $effectiveTo;
                    $currentProfile->save();
                    
                    $baseProfileData = [
                        'basic_salary' => $currentProfile->basic_salary,
                        'allowances_json' => $currentProfile->allowances_json,
                        'deductions_json' => $currentProfile->deductions_json,
                        'epf_eligible' => $currentProfile->epf_eligible,
                        'etf_eligible' => $currentProfile->etf_eligible
                    ];
                } else {
                    continue; // Overlaps with current profile start date
                }
            }

            if (isset($data['update_basic_salary'])) {
                $baseProfileData['basic_salary'] = $data['basic_salary'] ?? 0;
            }

            if (isset($data['update_epf_eligible'])) {
                $baseProfileData['epf_eligible'] = isset($data['epf_eligible']);
            }

            if (isset($data['update_etf_eligible'])) {
                $baseProfileData['etf_eligible'] = isset($data['etf_eligible']);
            }

            if (isset($data['update_allowances'])) {
                $allowances = [];
                if (isset($data['allowances'])) {
                    foreach ($data['allowances'] as $name => $amount) {
                        if ($amount !== null && $amount !== '') {
                            $allowances[] = ['name' => $name, 'amount' => (float) $amount];
                        }
                    }
                }
                $baseProfileData['allowances_json'] = $allowances;
            }

            if (isset($data['update_deductions'])) {
                $deductions = [];
                if (isset($data['deductions'])) {
                    foreach ($data['deductions'] as $name => $amount) {
                        if ($amount !== null && $amount !== '') {
                            $deductions[] = ['name' => $name, 'amount' => (float) $amount];
                        }
                    }
                }
                $baseProfileData['deductions_json'] = $deductions;
            }

            $profile = SalaryProfile::create(array_merge([
                'employee_id' => $empId,
                'effective_from' => $effectiveFrom,
                'effective_to' => null,
                'status' => 'Active',
            ], $baseProfileData));

            $updatedCount++;

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'BULK_CREATE_SALARY_PROFILE_VERSION',
                'module' => 'Payroll',
                'record_id' => $profile->id,
                'old_value' => $currentProfile ? json_encode($currentProfile) : null,
                'new_value' => json_encode($profile),
                'ip_address' => $ipAddress,
            ]);
        }

        return $updatedCount;
    }
}
