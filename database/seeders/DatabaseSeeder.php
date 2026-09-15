<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Companies
        $p1 = Company::create([
            'company_code' => 'P1',
            'company_name' => 'Prime One Global',
            'registration_number' => 'PV-P1-1002',
            'address' => 'Colombo, Sri Lanka',
            'contact_number' => '+94112345678',
            'email' => 'info@primeone.lk',
            'status' => 'Active',
        ]);

        $a1 = Company::create([
            'company_code' => 'A1',
            'company_name' => 'Altitude 1',
            'registration_number' => 'PV-A1-2004',
            'address' => 'Colombo 07, Sri Lanka',
            'contact_number' => '+94117654321',
            'email' => 'info@altitude1.lk',
            'status' => 'Active',
        ]);

        // Seed some branches for demo/testing
        $p1->branches()->create([
            'branch_code' => 'P1-HO',
            'branch_name' => 'Prime One - Head Office',
            'address' => 'Colombo 03',
            'status' => 'Active',
        ]);
        $p1->branches()->create([
            'branch_code' => 'P1-COL',
            'branch_name' => 'Prime One - Colombo',
            'address' => 'Colombo 04',
            'status' => 'Active',
        ]);
        $p1->branches()->create([
            'branch_code' => 'P1-JAF',
            'branch_name' => 'Prime One - Jaffna',
            'address' => 'Jaffna Town',
            'status' => 'Active',
        ]);
        $a1->branches()->create([
            'branch_code' => 'A1-HO',
            'branch_name' => 'Altitude 1 - Head Office',
            'address' => 'Colombo 07',
            'status' => 'Active',
        ]);

        // Seed some departments
        $p1->departments()->create([
            'department_code' => 'P1-HR',
            'department_name' => 'Human Resources',
        ]);
        $p1->departments()->create([
            'department_code' => 'P1-IT',
            'department_name' => 'Information Technology',
        ]);
        $a1->departments()->create([
            'department_code' => 'A1-OPS',
            'department_name' => 'Operations',
        ]);

        // Seed some shifts
        Shift::create([
            'shift_name' => 'Morning Shift',
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
            'grace_period' => 15,
            'overtime_eligibility' => true,
        ]);
        Shift::create([
            'shift_name' => 'Night Shift',
            'start_time' => '18:00:00',
            'end_time' => '06:00:00',
            'grace_period' => 15,
            'overtime_eligibility' => true,
        ]);

        // 2. Seed User Roles / Users
        // Initial Super Admin:
        // Username: Prime1-admin
        // Password: Admin@2002@22
        User::create([
            'username' => 'Prime1-admin',
            'email' => 'admin@primeone.lk',
            'password' => Hash::make('Admin@2002@22'),
            'role' => 'Super Administrator',
            'status' => 'Active',
            'force_password_change' => false,
        ]);

        // Seed other roles for testing
        User::create([
            'username' => 'hr-admin',
            'email' => 'hr@primeone.lk',
            'password' => Hash::make('HrAdmin@123'),
            'role' => 'HR Administrator',
            'status' => 'Active',
            'force_password_change' => false,
        ]);
        User::create([
            'username' => 'manager1',
            'email' => 'manager@primeone.lk',
            'password' => Hash::make('Manager@123'),
            'role' => 'Manager',
            'status' => 'Active',
            'force_password_change' => false,
        ]);

        // 3. Seed Leave Types
        LeaveType::create(['name' => 'Annual Leave', 'status' => 'Active']);
        LeaveType::create(['name' => 'Casual Leave', 'status' => 'Active']);
        LeaveType::create(['name' => 'Medical Leave', 'status' => 'Active']);

        // 4. Seed Settings
        // Security Settings
        Setting::setVal('Security', 'PasswordMinLength', '8', false, 'Minimum password length policy.');
        Setting::setVal('Security', 'MaxLoginAttempts', '5', false, 'Maximum failed login attempts before lockout.');
        Setting::setVal('Security', 'SessionTimeout', '60', false, 'Session lifetime timeout in minutes.');

        // Attendance Settings
        Setting::setVal('Attendance', 'LateGracePeriod', '5', false, 'Late arrival grace period in minutes.');
        Setting::setVal('Attendance', 'EarlyOutGracePeriod', '0', false, 'Early out grace period in minutes.');
        Setting::setVal('Attendance', 'OvertimeStartAfterMinutes', '30', false, 'Minutes after shift end time when overtime eligibility starts.');
        Setting::setVal('System', 'DeviceOfflineThreshold', '5', false, 'Minutes after which a device is flagged as offline if no message is received.');

        // Payroll Settings
        Setting::setVal('Payroll', 'Currency', 'LKR', false, 'Standard 3-letter currency code.');
        Setting::setVal('Payroll', 'SalaryDaysPerMonth', '30', false, 'Dividing factor for unpaid leave penalty.');
        Setting::setVal('Payroll', 'WorkingHoursPerMonth', '240', false, 'Standard working hours to compute overtime hourly base rate.');
        Setting::setVal('Payroll', 'EPFEmployeeRate', '8.00', false, 'EPF employee contribution percentage.');
        Setting::setVal('Payroll', 'EPFEmployerRate', '12.00', false, 'EPF employer contribution percentage.');
        Setting::setVal('Payroll', 'ETFRate', '3.00', false, 'ETF employer contribution percentage.');
        Setting::setVal('Payroll', 'EnableEPF', 'true', false, 'Toggle switch for global EPF calculation.');
        Setting::setVal('Payroll', 'EnableETF', 'true', false, 'Toggle switch for global ETF calculation.');
        Setting::setVal('Payroll', 'EnableAPIT', 'false', false, 'Toggle switch for global withholding tax calculation.');
        Setting::setVal('Payroll', 'EnableOvertime', 'true', false, 'Toggle switch for global Overtime calculation.');
        Setting::setVal('Payroll', 'OvertimeMultiplier', '1.5', false, 'Normal day overtime rate multiplier.');
        Setting::setVal('Payroll', 'WeekendOTMultiplier', '1.5', false, 'Weekend overtime rate multiplier.');
        Setting::setVal('Payroll', 'HolidayOTMultiplier', '2.0', false, 'Public holiday overtime rate multiplier.');
        Setting::setVal('Payroll', 'PoyaOTMultiplier', '2.0', false, 'Poya holiday overtime rate multiplier.');
        Setting::setVal('Payroll', 'NoPayFormula', 'BASIC_DIV_30', false, 'Formula rule used to calculate unpaid leaves penalty.');
        Setting::setVal('Payroll', 'AttendanceBonusEnabled', 'false', false, 'Toggle switch for attendance-based bonus payouts.');
        Setting::setVal('Payroll', 'DecimalPlaces', '2', false, 'Decimal places to round calculated payroll figures.');
        Setting::setVal('Payroll', 'CurrencyPosition', 'Before', false, 'Currency symbol display placement position (Before or After).');
        Setting::setVal('Payroll', 'PayslipFooter', 'This payslip is system generated.', false, 'Print footer text displayed on employee payslips.');
        Setting::setVal('Payroll', 'PayrollApprovalRequired', 'true', false, 'Toggle switch to enforce bulk runs approval workflow.');
        Setting::setVal('Payroll', 'PayrollLockAfterApproval', 'true', false, 'Toggle switch to automatically lock bulk runs once approved.');
        Setting::setVal('Payroll', 'PayrollCycle', 'Monthly', false, 'Default payroll cycle configuration.');
        Setting::setVal('Payroll', 'DefaultWorkingDays', 'Monday-Friday', false, 'Configure default weekly working day spans.');
        Setting::setVal('Payroll', 'LoanAutoDeduction', 'true', false, 'Global toggle to deduct fixed loan installments from nets.');
        Setting::setVal('Payroll', 'AdvanceSalaryDeduction', 'true', false, 'Global toggle to recover advance salaries payouts.');
        Setting::setVal('Payroll', 'LeaveEncashment', 'false', false, 'Global toggle for leave encashment calculations.');
        Setting::setVal('Payroll', 'GratuityEnabled', 'false', false, 'Global toggle to calculate gratuity elements.');
        Setting::setVal('Payroll', 'AttendanceBasedPayroll', 'true', false, 'Global toggle to require attendance data logs processing.');

        // Seed new V3.0 Access levels, Permissions, Sidebar Menus, Workflows, and App Launcher Registries
        $this->call([
            AccessLevelSeeder::class,
            PermissionSeeder::class,
            MenuSeeder::class,
            ApprovalWorkflowSeeder::class,
            AppRegistrySeeder::class,
        ]);
    }
}
