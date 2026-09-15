<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait BulkImportTrait {
    public function bulkImportEmployees(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        if (count($data) < 2) {
            return back()->with('error', 'CSV file is empty or missing data rows.');
        }
        
        // Remove header row
        $headers = array_shift($data);

        $successCount = 0;
        $errorCount = 0;

        foreach ($data as $index => $row) {
            // Pad array to avoid undefined offset
            $row = array_pad($row, 18, null);

            $empNo = trim($row[0] ?? '');
            $epfNo = trim($row[1] ?? '');
            
            // Auto use EPF for Employee Number if missing
            if (empty($empNo) && !empty($epfNo)) {
                $empNo = $epfNo;
            }
            
            if (empty($empNo)) {
                $errorCount++;
                continue; // Cannot proceed without an identifier
            }

            $firstName = trim($row[2] ?? '');
            $lastName = trim($row[3] ?? '');
            $gender = trim($row[4] ?? '');
            $dob = trim($row[5] ?? '');
            $nic = trim($row[6] ?? '');
            $email = trim($row[7] ?? '');
            $mobile = trim($row[8] ?? '');
            $designation = trim($row[9] ?? '');
            $joinDate = trim($row[10] ?? '');
            $departmentStr = trim($row[11] ?? '');
            $companyStr = trim($row[12] ?? '');
            $branchStr = trim($row[13] ?? '');
            $shiftStr = trim($row[14] ?? '');
            $admsUserId = trim($row[15] ?? '');
            $admsPin = trim($row[16] ?? '');

            // Auto generate ADMS details based on EPF
            if (empty($admsUserId) && !empty($epfNo)) {
                $admsUserId = $epfNo;

                // Prepend company code if P1 (10) or A1 (20)
                if (stripos($companyStr, 'Prime') !== false || strtoupper($companyStr) === 'P1') {
                    $admsUserId = '10' . str_pad($epfNo, 2, '0', STR_PAD_LEFT);
                } elseif (stripos($companyStr, 'Altitude') !== false || strtoupper($companyStr) === 'A1') {
                    $admsUserId = '20' . str_pad($epfNo, 2, '0', STR_PAD_LEFT);
                }
            }
            if (empty($admsPin) && !empty($epfNo)) {
                $admsPin = $admsUserId; // Sync PIN matches the newly generated ID
            }

            // Map Date Formats
            $parsedDob = null;
            if (!empty($dob)) {
                try {
                    $parsedDob = Carbon::parse(str_replace(['.', '/'], '-', $dob))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignore invalid
                }
            }

            $parsedJoinDate = null;
            if (!empty($joinDate)) {
                try {
                    $parsedJoinDate = Carbon::parse(str_replace(['.', '/'], '-', $joinDate))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            // Map Company to company_id
            $companyId = null;
            if (!empty($companyStr)) {
                $company = Company::where('company_name', 'like', "%{$companyStr}%")
                                  ->orWhere('company_code', $companyStr)
                                  ->first();
                if (!$company) {
                    $company = Company::create([
                        'company_name' => $companyStr,
                        'company_code' => strtoupper(substr($companyStr, 0, 10)),
                    ]);
                }
                if ($company) {
                    $companyId = $company->id;
                }
            }

            // Map Branch to branch_id
            $branchId = null;
            if (!empty($branchStr)) {
                $branch = Branch::where('branch_name', 'like', "%{$branchStr}%")->first();
                if (!$branch) {
                    $branch = Branch::create([
                        'branch_name' => $branchStr,
                        'company_id' => $companyId,
                    ]);
                }
                if ($branch) {
                    $branchId = $branch->id;
                }
            }

            // Map Department to department_id
            $departmentId = null;
            if (!empty($departmentStr)) {
                $department = Department::where('department_name', 'like', "%{$departmentStr}%")->first();
                if (!$department) {
                    $department = Department::create([
                        'department_name' => $departmentStr,
                        'company_id' => $companyId,
                    ]);
                }
                if ($department) {
                    $departmentId = $department->id;
                }
            }

            // Auto-create Designation if missing
            if (!empty($designation)) {
                $desig = \App\Models\Designation::where('title', 'like', "%{$designation}%")->first();
                if (!$desig) {
                    \App\Models\Designation::create([
                        'title' => $designation,
                        'company_id' => $companyId,
                    ]);
                }
            }
            // Map Shift to shift_id
            $shiftId = null;
            if (!empty($shiftStr)) {
                $shift = \App\Models\Shift::where('shift_name', 'like', "%{$shiftStr}%")->first();
                if (!$shift) {
                    $shift = \App\Models\Shift::create([
                        'shift_name' => $shiftStr,
                        'company_id' => $companyId,
                    ]);
                }
                if ($shift) {
                    $shiftId = $shift->id;
                }
            }

            try {
                Employee::updateOrCreate(
                    ['employee_number' => $empNo], // Match by employee_number
                    [
                        'employee_id' => $epfNo,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'gender' => $gender,
                        'date_of_birth' => $parsedDob,
                        'nic' => $nic,
                        'email' => $email,
                        'mobile_number' => $mobile,
                        'designation' => $designation,
                        'join_date' => $parsedJoinDate,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'department_id' => $departmentId,
                        'shift_id' => $shiftId,
                        'device_user_id' => $admsUserId,
                        'sync_pin' => $admsPin,
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Failed to import employee row $index: " . $e->getMessage());
                $errorCount++;
            }
        }

        return back()->with('success', "CSV Import Complete: $successCount employees processed, $errorCount failed.");
    }
}
