<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionCategory;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $schema = [
            [
                'name'  => 'Dashboard',
                'slug'  => 'dashboard',
                'icon'  => 'bi-speedometer2',
                'color' => '#0d6efd',
                'sort'  => 1,
                'permissions' => [
                    ['permission_key' => 'dashboard.view',      'permission_name' => 'View Dashboard',          'sort' => 1],
                    ['permission_key' => 'dashboard.hr',        'permission_name' => 'HR Dashboard',            'sort' => 2],
                    ['permission_key' => 'dashboard.manager',   'permission_name' => 'Manager Dashboard',       'sort' => 3],
                    ['permission_key' => 'dashboard.finance',   'permission_name' => 'Finance Dashboard',       'sort' => 4],
                    ['permission_key' => 'dashboard.executive', 'permission_name' => 'Executive Dashboard',     'sort' => 5],
                    ['permission_key' => 'dashboard.payroll',   'permission_name' => 'Payroll Dashboard',       'sort' => 6],
                ],
            ],
            [
                'name'  => 'Organization',
                'slug'  => 'organization',
                'icon'  => 'bi-building',
                'color' => '#198754',
                'sort'  => 2,
                'permissions' => [
                    ['permission_key' => 'company.view',   'permission_name' => 'View Companies',   'sort' => 1],
                    ['permission_key' => 'company.create', 'permission_name' => 'Create Company',   'sort' => 2],
                    ['permission_key' => 'company.edit',   'permission_name' => 'Edit Company',     'sort' => 3],
                    ['permission_key' => 'company.delete', 'permission_name' => 'Delete Company',   'sort' => 4],
                    ['permission_key' => 'branch.view',    'permission_name' => 'View Branches',    'sort' => 5],
                    ['permission_key' => 'branch.manage',  'permission_name' => 'Manage Branches',  'sort' => 6],
                ],
            ],
            [
                'name'  => 'Employee',
                'slug'  => 'employee',
                'icon'  => 'bi-people',
                'color' => '#0dcaf0',
                'sort'  => 3,
                'permissions' => [
                    ['permission_key' => 'employee.view',       'permission_name' => 'View Employees',      'sort' => 1],
                    ['permission_key' => 'employee.create',     'permission_name' => 'Create Employee',     'sort' => 2],
                    ['permission_key' => 'employee.edit',       'permission_name' => 'Edit Employee',       'sort' => 3],
                    ['permission_key' => 'employee.delete',     'permission_name' => 'Delete Employee',     'sort' => 4],
                    ['permission_key' => 'employee.import',     'permission_name' => 'Import Employees',    'sort' => 5],
                    ['permission_key' => 'employee.export',     'permission_name' => 'Export Employees',    'sort' => 6],
                    ['permission_key' => 'employee.print',      'permission_name' => 'Print Employee Data', 'sort' => 7],
                    ['permission_key' => 'employee.doc_view',   'permission_name' => 'View Documents',      'sort' => 8],
                    ['permission_key' => 'employee.doc_upload', 'permission_name' => 'Upload Documents',    'sort' => 9],
                    ['permission_key' => 'employee.doc_delete', 'permission_name' => 'Delete Documents',    'sort' => 10],
                    ['permission_key' => 'employee.ag_generate','permission_name' => 'Generate Agreements', 'sort' => 11],
                    ['permission_key' => 'employee.onboard',    'permission_name' => 'Onboard Employees',   'sort' => 12],
                ],
            ],
            [
                'name'  => 'Attendance',
                'slug'  => 'attendance',
                'icon'  => 'bi-calendar-check',
                'color' => '#20c997',
                'sort'  => 4,
                'permissions' => [
                    ['permission_key' => 'attendance.view',       'permission_name' => 'View Attendance',       'sort' => 1],
                    ['permission_key' => 'attendance.edit',       'permission_name' => 'Edit Attendance',       'sort' => 2],
                    ['permission_key' => 'attendance.delete',     'permission_name' => 'Delete Attendance',     'sort' => 3],
                    ['permission_key' => 'attendance.manual',     'permission_name' => 'Manual Entry',          'sort' => 4],
                    ['permission_key' => 'attendance.correction', 'permission_name' => 'Attendance Correction', 'sort' => 5],
                    ['permission_key' => 'attendance.approval',   'permission_name' => 'Approve Corrections',   'sort' => 6],
                    ['permission_key' => 'attendance.export',     'permission_name' => 'Export Attendance',     'sort' => 7],
                    ['permission_key' => 'attendance.reports',    'permission_name' => 'Attendance Reports',    'sort' => 8],
                ],
            ],
            [
                'name'  => 'Leave',
                'slug'  => 'leave',
                'icon'  => 'bi-calendar3-range',
                'color' => '#fd7e14',
                'sort'  => 5,
                'permissions' => [
                    ['permission_key' => 'leave.view',     'permission_name' => 'View Leave Requests', 'sort' => 1],
                    ['permission_key' => 'leave.request',  'permission_name' => 'Submit Leave Request','sort' => 2],
                    ['permission_key' => 'leave.approval', 'permission_name' => 'Approve Leave',       'sort' => 3],
                    ['permission_key' => 'leave.reject',   'permission_name' => 'Reject Leave',        'sort' => 4],
                    ['permission_key' => 'leave.types',    'permission_name' => 'Manage Leave Types',  'sort' => 5],
                    ['permission_key' => 'leave.balances', 'permission_name' => 'Manage Balances',     'sort' => 6],
                    ['permission_key' => 'leave.holidays', 'permission_name' => 'Holiday Calendar',    'sort' => 7],
                    ['permission_key' => 'leave.export',   'permission_name' => 'Export Leave Data',   'sort' => 8],
                ],
            ],
            [
                'name'  => 'Payroll',
                'slug'  => 'payroll',
                'icon'  => 'bi-wallet2',
                'color' => '#6f42c1',
                'sort'  => 6,
                'permissions' => [
                    ['permission_key' => 'payroll.view',            'permission_name' => 'View Payroll',          'sort' => 1],
                    ['permission_key' => 'payroll.processing',      'permission_name' => 'Process Payroll',       'sort' => 2],
                    ['permission_key' => 'payroll.approval',        'permission_name' => 'Approve Payroll',       'sort' => 3],
                    ['permission_key' => 'payroll.lock',            'permission_name' => 'Lock Payroll Period',   'sort' => 4],
                    ['permission_key' => 'payroll.unlock',          'permission_name' => 'Unlock Payroll Period', 'sort' => 5],
                    ['permission_key' => 'payroll.payslips',        'permission_name' => 'View Payslips',         'sort' => 6],
                    ['permission_key' => 'payroll.payslips_email',  'permission_name' => 'Email Payslips',        'sort' => 7],
                    ['permission_key' => 'payroll.salary_profiles', 'permission_name' => 'Manage Salary Profiles','sort' => 8],
                    ['permission_key' => 'payroll.reports',         'permission_name' => 'Payroll Reports',       'sort' => 9],
                    ['permission_key' => 'payroll.settings',        'permission_name' => 'Payroll Settings',      'sort' => 10],
                    ['permission_key' => 'payroll.export',          'permission_name' => 'Export Payroll Data',   'sort' => 11],
                ],
            ],
            [
                'name'  => 'Onboarding',
                'slug'  => 'onboarding',
                'icon'  => 'bi-person-plus',
                'color' => '#d63384',
                'sort'  => 7,
                'permissions' => [
                    ['permission_key' => 'onboarding.view',    'permission_name' => 'View Onboarding',     'sort' => 1],
                    ['permission_key' => 'onboarding.manage',  'permission_name' => 'Manage Onboarding',   'sort' => 2],
                    ['permission_key' => 'onboarding.approve', 'permission_name' => 'Approve Stages',      'sort' => 3],
                    ['permission_key' => 'onboarding.assets',  'permission_name' => 'Assign Assets',       'sort' => 4],
                    ['permission_key' => 'onboarding.docs',    'permission_name' => 'Manage Documents',    'sort' => 5],
                ],
            ],
            [
                'name'  => 'Reports',
                'slug'  => 'reports',
                'icon'  => 'bi-file-earmark-bar-graph',
                'color' => '#0d6efd',
                'sort'  => 8,
                'permissions' => [
                    ['permission_key' => 'report.attendance', 'permission_name' => 'Attendance Reports', 'sort' => 1],
                    ['permission_key' => 'report.payroll',    'permission_name' => 'Payroll Reports',    'sort' => 2],
                    ['permission_key' => 'report.employee',   'permission_name' => 'Employee Reports',   'sort' => 3],
                    ['permission_key' => 'report.audit',      'permission_name' => 'Audit Reports',      'sort' => 4],
                    ['permission_key' => 'report.export',     'permission_name' => 'Export Reports',     'sort' => 5],
                    ['permission_key' => 'report.print',      'permission_name' => 'Print Reports',      'sort' => 6],
                ],
            ],
            [
                'name'  => 'Devices',
                'slug'  => 'devices',
                'icon'  => 'bi-cpu',
                'color' => '#6c757d',
                'sort'  => 9,
                'permissions' => [
                    ['permission_key' => 'device.view',     'permission_name' => 'View Devices',      'sort' => 1],
                    ['permission_key' => 'device.register', 'permission_name' => 'Register Device',   'sort' => 2],
                    ['permission_key' => 'device.approve',  'permission_name' => 'Approve Device',    'sort' => 3],
                    ['permission_key' => 'device.disable',  'permission_name' => 'Disable Device',    'sort' => 4],
                    ['permission_key' => 'device.commands', 'permission_name' => 'Trigger Commands',  'sort' => 5],
                    ['permission_key' => 'device.logs',     'permission_name' => 'Download Logs',     'sort' => 6],
                    ['permission_key' => 'device.sync',     'permission_name' => 'Sync Time',         'sort' => 7],
                ],
            ],
            [
                'name'  => 'Documents',
                'slug'  => 'documents',
                'icon'  => 'bi-file-earmark-text',
                'color' => '#198754',
                'sort'  => 10,
                'permissions' => [
                    ['permission_key' => 'document.view',   'permission_name' => 'View Documents',    'sort' => 1],
                    ['permission_key' => 'document.upload', 'permission_name' => 'Upload Documents',  'sort' => 2],
                    ['permission_key' => 'document.delete', 'permission_name' => 'Delete Documents',  'sort' => 3],
                    ['permission_key' => 'document.sign',   'permission_name' => 'Sign Documents',    'sort' => 4],
                    ['permission_key' => 'document.print',  'permission_name' => 'Print Documents',   'sort' => 5],
                ],
            ],
            [
                'name'  => 'Assets',
                'slug'  => 'assets',
                'icon'  => 'bi-box-seam',
                'color' => '#fd7e14',
                'sort'  => 11,
                'permissions' => [
                    ['permission_key' => 'asset.view',   'permission_name' => 'View Assets',   'sort' => 1],
                    ['permission_key' => 'asset.assign', 'permission_name' => 'Assign Assets', 'sort' => 2],
                    ['permission_key' => 'asset.return', 'permission_name' => 'Return Assets', 'sort' => 3],
                    ['permission_key' => 'asset.manage', 'permission_name' => 'Manage Assets', 'sort' => 4],
                ],
            ],
            [
                'name'  => 'Administration',
                'slug'  => 'administration',
                'icon'  => 'bi-sliders2',
                'color' => '#dc3545',
                'sort'  => 12,
                'permissions' => [
                    ['permission_key' => 'admin.users',       'permission_name' => 'User Management',      'sort' => 1],
                    ['permission_key' => 'admin.roles',       'permission_name' => 'Role Management',      'sort' => 2],
                    ['permission_key' => 'admin.permissions', 'permission_name' => 'Permission Management','sort' => 3],
                    ['permission_key' => 'admin.settings',    'permission_name' => 'System Settings',      'sort' => 4],
                    ['permission_key' => 'admin.audit',       'permission_name' => 'Audit Logs',           'sort' => 5],
                    ['permission_key' => 'admin.backup',      'permission_name' => 'Database Backup',      'sort' => 6],
                    ['permission_key' => 'admin.restore',     'permission_name' => 'Database Restore',     'sort' => 7],
                    ['permission_key' => 'admin.menus',       'permission_name' => 'Menu Builder',         'sort' => 8],
                    ['permission_key' => 'admin.workflows',   'permission_name' => 'Approval Workflows',   'sort' => 9],
                    ['permission_key' => 'admin.access_levels','permission_name' => 'Access Level Matrix', 'sort' => 10],
                ],
            ],
            [
                'name'  => 'Settings',
                'slug'  => 'settings',
                'icon'  => 'bi-gear',
                'color' => '#6c757d',
                'sort'  => 13,
                'permissions' => [
                    ['permission_key' => 'settings.view',    'permission_name' => 'View Settings',    'sort' => 1],
                    ['permission_key' => 'settings.payroll', 'permission_name' => 'Payroll Settings', 'sort' => 2],
                    ['permission_key' => 'settings.system',  'permission_name' => 'System Settings',  'sort' => 3],
                ],
            ],
            [
                'name'  => 'Security',
                'slug'  => 'security',
                'icon'  => 'bi-shield-lock',
                'color' => '#dc3545',
                'sort'  => 14,
                'permissions' => [
                    ['permission_key' => 'security.view',     'permission_name' => 'View Security Logs', 'sort' => 1],
                    ['permission_key' => 'security.lock',     'permission_name' => 'Lock User Accounts', 'sort' => 2],
                    ['permission_key' => 'security.emergency','permission_name' => 'Emergency Access',    'sort' => 3],
                    ['permission_key' => 'security.sessions', 'permission_name' => 'View Sessions',       'sort' => 4],
                ],
            ],
        ];

        $sortOrder = 0;
        foreach ($schema as $cat) {
            $category = PermissionCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name'       => $cat['name'],
                    'icon'       => $cat['icon'],
                    'color'      => $cat['color'],
                    'sort_order' => $cat['sort'],
                    'status'     => 'Active',
                ]
            );

            foreach ($cat['permissions'] as $perm) {
                Permission::updateOrCreate(
                    ['permission_key' => $perm['permission_key']],
                    [
                        'category_id'     => $category->id,
                        'permission_name' => $perm['permission_name'],
                        'sort_order'      => $perm['sort'],
                        'status'          => 'Active',
                    ]
                );
            }
        }
    }
}
