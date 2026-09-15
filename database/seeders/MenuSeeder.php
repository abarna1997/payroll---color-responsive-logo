<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing menus
        Menu::truncate();

        // 1. Dashboard
        Menu::create([
            'title' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'url' => '/',
            'route_name' => 'dashboard',
            'route_pattern' => 'dashboard',
            'permission_key' => 'dashboard.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        // 2. Organization (Hierarchy only - No Employees)
        $org = Menu::create([
            'title' => 'Organization',
            'icon' => 'bi-building',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Companies',
            'icon' => 'bi-building-fill',
            'url' => '/companies',
            'route_name' => 'companies',
            'route_pattern' => 'companies*',
            'parent_id' => $org->id,
            'permission_key' => 'company.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Branches',
            'icon' => 'bi-diagram-3-fill',
            'url' => '/branches',
            'route_name' => 'branches',
            'route_pattern' => 'branches*',
            'parent_id' => $org->id,
            'permission_key' => 'branch.view',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Departments',
            'icon' => 'bi-briefcase-fill',
            'url' => '/departments',
            'route_name' => 'departments',
            'route_pattern' => 'departments*',
            'parent_id' => $org->id,
            'permission_key' => 'employee.view',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Designations',
            'icon' => 'bi-award-fill',
            'url' => '/designations',
            'route_name' => 'designations',
            'route_pattern' => 'designations*',
            'parent_id' => $org->id,
            'permission_key' => 'employee.view',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Locations',
            'icon' => 'bi-geo-alt-fill',
            'url' => '/locations',
            'route_name' => 'locations',
            'route_pattern' => 'locations*',
            'parent_id' => $org->id,
            'permission_key' => 'company.view',
            'sort_order' => 50,
            'status' => 'Active',
        ]);



        // 3. Workforce (Employee Management - Cleaned Duplicate Links)
        $workforce = Menu::create([
            'title' => 'Workforce',
            'icon' => 'bi-people-fill',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Employees',
            'icon' => 'bi-person-lines-fill',
            'url' => '/employees',
            'route_name' => 'employees',
            'route_pattern' => 'employees*',
            'parent_id' => $workforce->id,
            'permission_key' => 'employee.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Employee Onboarding',
            'icon' => 'bi-person-plus-fill',
            'url' => '/onboarding',
            'route_name' => 'onboarding.dashboard',
            'route_pattern' => 'onboarding*',
            'parent_id' => $workforce->id,
            'permission_key' => 'onboarding.view',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Employee Documents',
            'icon' => 'bi-file-earmark-person-fill',
            'url' => '/onboarding/templates',
            'route_name' => 'onboarding.templates',
            'route_pattern' => 'onboarding/templates*',
            'parent_id' => $workforce->id,
            'permission_key' => 'employee.doc_view',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Employee Transfers',
            'icon' => 'bi-arrow-left-right',
            'url' => '/transfers',
            'route_name' => 'transfers',
            'route_pattern' => 'transfers*',
            'parent_id' => $workforce->id,
            'permission_key' => 'employee.edit',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Employee Resignation',
            'icon' => 'bi-box-arrow-right',
            'url' => '/resignations',
            'route_name' => 'resignations',
            'route_pattern' => 'resignations*',
            'parent_id' => $workforce->id,
            'permission_key' => 'employee.edit',
            'sort_order' => 50,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Employee Termination',
            'icon' => 'bi-person-x-fill',
            'url' => '/terminations',
            'route_name' => 'terminations',
            'route_pattern' => 'terminations*',
            'parent_id' => $workforce->id,
            'permission_key' => 'employee.delete',
            'sort_order' => 60,
            'status' => 'Active',
        ]);

        // 4. Attendance Operations (Cleaned Duplicate Links)
        $attendance = Menu::create([
            'title' => 'Attendance',
            'icon' => 'bi-calendar-check-fill',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Attendance Logs',
            'icon' => 'bi-journal-text',
            'url' => '/attendance',
            'route_name' => 'attendance',
            'route_pattern' => 'attendance*',
            'parent_id' => $attendance->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Attendance Corrections',
            'icon' => 'bi-patch-check-fill',
            'url' => '/manual-logs',
            'route_name' => 'manual-logs',
            'route_pattern' => 'manual-logs*',
            'parent_id' => $attendance->id,
            'permission_key' => 'attendance.correction',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Overtime',
            'icon' => 'bi-clock-history',
            'url' => '/overtime',
            'route_name' => 'overtime',
            'route_pattern' => 'overtime*',
            'parent_id' => $attendance->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Late Arrivals',
            'icon' => 'bi-alarm-fill',
            'url' => '/late-arrivals',
            'route_name' => 'late-arrivals',
            'route_pattern' => 'late-arrivals*',
            'parent_id' => $attendance->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Early Departures',
            'icon' => 'bi-box-arrow-left',
            'url' => '/early-departures',
            'route_name' => 'early-departures',
            'route_pattern' => 'early-departures*',
            'parent_id' => $attendance->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 50,
            'status' => 'Active',
        ]);

        // 5. Shift Management (Standalone Module)
        $shifts = Menu::create([
            'title' => 'Shift Management',
            'icon' => 'bi-clock-fill',
            'sort_order' => 50,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Shift Templates',
            'icon' => 'bi-sliders',
            'url' => '/shifts',
            'route_name' => 'shifts',
            'route_pattern' => 'shifts*',
            'parent_id' => $shifts->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Shift Rotation',
            'icon' => 'bi-arrow-repeat',
            'url' => '/shifts/rotations',
            'route_name' => 'shifts.rotations',
            'route_pattern' => 'shifts/rotations*',
            'parent_id' => $shifts->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Weekly Schedule',
            'icon' => 'bi-calendar-week-fill',
            'url' => '/shifts/weekly',
            'route_name' => 'shifts.weekly',
            'route_pattern' => 'shifts/weekly*',
            'parent_id' => $shifts->id,
            'permission_key' => 'attendance.view',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        // 6. Leave Management
        $leave = Menu::create([
            'title' => 'Leave Management',
            'icon' => 'bi-calendar3-range-fill',
            'sort_order' => 60,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Leave Requests',
            'icon' => 'bi-calendar-check-fill',
            'url' => '/leaves',
            'route_name' => 'leaves',
            'route_pattern' => 'leaves',
            'parent_id' => $leave->id,
            'permission_key' => 'leave.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Leave Types',
            'icon' => 'bi-list-task',
            'url' => '/leaves/types',
            'route_name' => 'leaves.types',
            'route_pattern' => 'leaves/types*',
            'parent_id' => $leave->id,
            'permission_key' => 'leave.types',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Leave Balances',
            'icon' => 'bi-percent',
            'url' => '/leaves/balances',
            'route_name' => 'leaves.balances',
            'route_pattern' => 'leaves/balances*',
            'parent_id' => $leave->id,
            'permission_key' => 'leave.balances',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'WFH Requests',
            'icon' => 'bi-laptop',
            'url' => '/wfh',
            'route_name' => 'wfh.index',
            'route_pattern' => 'wfh*',
            'parent_id' => $leave->id,
            'permission_key' => 'leave.view',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Leave Calendar',
            'icon' => 'bi-calendar-date-fill',
            'url' => '/holidays',
            'route_name' => 'holidays',
            'route_pattern' => 'holidays*',
            'parent_id' => $leave->id,
            'permission_key' => 'leave.holidays',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        // 7. Payroll Management
        $payroll = Menu::create([
            'title' => 'Payroll',
            'icon' => 'bi-wallet2',
            'sort_order' => 70,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Payroll Dashboard',
            'icon' => 'bi-speedometer2',
            'url' => '/payroll',
            'route_name' => 'payroll.index',
            'route_pattern' => 'payroll',
            'parent_id' => $payroll->id,
            'permission_key' => 'payroll.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Salary Profiles',
            'icon' => 'bi-person-badge-fill',
            'url' => '/payroll/profiles',
            'route_name' => 'payroll.profiles',
            'route_pattern' => 'payroll/profiles*',
            'parent_id' => $payroll->id,
            'permission_key' => 'payroll.salary_profiles',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Payroll Processing',
            'icon' => 'bi-cpu-fill',
            'url' => '/payroll/processing',
            'route_name' => 'payroll.processing',
            'route_pattern' => 'payroll/processing*',
            'parent_id' => $payroll->id,
            'permission_key' => 'payroll.processing',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Payslips',
            'icon' => 'bi-file-pdf-fill',
            'url' => '/payroll/payslips',
            'route_name' => 'payroll.payslips',
            'route_pattern' => 'payroll/payslips*',
            'parent_id' => $payroll->id,
            'permission_key' => 'payroll.payslips',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Payroll Setup Console',
            'icon' => 'bi-sliders',
            'url' => '/admin/payroll',
            'route_name' => 'admin.payroll.index',
            'route_pattern' => 'admin/payroll*',
            'parent_id' => $payroll->id,
            'permission_key' => 'payroll.settings',
            'sort_order' => 50,
            'status' => 'Active',
        ]);

        // 8. Biometric Devices (Centralized Hardware Module - Cleaned Duplicate Links)
        $devices = Menu::create([
            'title' => 'Biometric Devices',
            'icon' => 'bi-cpu-fill',
            'sort_order' => 80,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Device Management',
            'icon' => 'bi-hdd-network-fill',
            'url' => '/devices',
            'route_name' => 'devices',
            'route_pattern' => 'devices*',
            'parent_id' => $devices->id,
            'permission_key' => 'device.view',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Pending Commands',
            'icon' => 'bi-inboxes-fill',
            'url' => '/queue-manager',
            'route_name' => 'queue-manager.index',
            'route_pattern' => 'queue-manager*',
            'parent_id' => $devices->id,
            'permission_key' => 'admin.settings',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Device Groups',
            'icon' => 'bi-collection-fill',
            'url' => '/devices/groups',
            'route_name' => 'devices.groups',
            'route_pattern' => 'devices/groups*',
            'parent_id' => $devices->id,
            'permission_key' => 'device.view',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Device Settings',
            'icon' => 'bi-gear-fill',
            'url' => '/devices/settings',
            'route_name' => 'devices.settings',
            'route_pattern' => 'devices/settings*',
            'parent_id' => $devices->id,
            'permission_key' => 'admin.settings',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        // 9. Reports & Analytics
        $reports = Menu::create([
            'title' => 'Reports & Analytics',
            'icon' => 'bi-file-earmark-bar-graph-fill',
            'sort_order' => 90,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Attendance Reports',
            'icon' => 'bi-journal-check',
            'url' => '/reports',
            'route_name' => 'reports',
            'route_pattern' => 'reports*',
            'parent_id' => $reports->id,
            'permission_key' => 'report.attendance',
            'sort_order' => 10,
            'status' => 'Active',
        ]);



        Menu::create([
            'title' => 'Audit Logs',
            'icon' => 'bi-shield-check',
            'url' => '/audit-logs',
            'route_name' => 'audit-logs',
            'route_pattern' => 'audit-logs*',
            'parent_id' => $reports->id,
            'permission_key' => 'admin.audit',
            'sort_order' => 50,
            'status' => 'Active',
        ]);

        // 10. Administration & Access Management
        $admin = Menu::create([
            'title' => 'Administration',
            'icon' => 'bi-sliders2',
            'sort_order' => 100,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Users',
            'icon' => 'bi-person-circle',
            'url' => '/users',
            'route_name' => 'users',
            'route_pattern' => 'users*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.users',
            'sort_order' => 10,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Business Roles',
            'icon' => 'bi-person-badge-fill',
            'url' => '/roles',
            'route_name' => 'roles',
            'route_pattern' => 'roles*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.roles',
            'sort_order' => 20,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Access Levels',
            'icon' => 'bi-award-fill',
            'url' => '/access-levels',
            'route_name' => 'access-levels.index',
            'route_pattern' => 'access-levels*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.roles',
            'sort_order' => 30,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Permissions Matrix',
            'icon' => 'bi-grid-3x3-gap-fill',
            'url' => '/permissions',
            'route_name' => 'permissions.index',
            'route_pattern' => 'permissions*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.permissions',
            'sort_order' => 40,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Permission Audit Logs',
            'icon' => 'bi-journal-text',
            'url' => '/permissions/history',
            'route_name' => 'permissions.history',
            'route_pattern' => 'permissions/history*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.audit',
            'sort_order' => 50,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Queue Manager',
            'icon' => 'bi-inboxes',
            'url' => '/queue-manager',
            'route_name' => 'queue-manager.index',
            'route_pattern' => 'queue-manager*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.settings',
            'sort_order' => 60,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'System Settings',
            'icon' => 'bi-sliders',
            'url' => '/settings',
            'route_name' => 'settings',
            'route_pattern' => 'settings*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.settings',
            'sort_order' => 70,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Backup & Restore',
            'icon' => 'bi-database-fill-check',
            'url' => '/backups',
            'route_name' => 'backups',
            'route_pattern' => 'backups*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.backup',
            'sort_order' => 80,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Menu Builder',
            'icon' => 'bi-list-nested',
            'url' => '/menus',
            'route_name' => 'menus.index',
            'route_pattern' => 'menus*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.menus',
            'sort_order' => 90,
            'status' => 'Active',
        ]);

        Menu::create([
            'title' => 'Approval Workflows',
            'icon' => 'bi-diagram-3-fill',
            'url' => '/approval-workflows',
            'route_name' => 'approval-workflows.index',
            'route_pattern' => 'approval-workflows*',
            'parent_id' => $admin->id,
            'permission_key' => 'admin.workflows',
            'sort_order' => 100,
            'status' => 'Active',
        ]);
    }
}
