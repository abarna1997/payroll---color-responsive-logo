<?php

namespace Database\Seeders;

use App\Models\AccessLevel;
use Illuminate\Database\Seeder;

class AccessLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'level'       => 0,
                'code'        => 'L0',
                'name'        => 'Intern / Trainee',
                'description' => 'Read-only access. Can view own profile, attendance, and payslips. No edit or approval rights.',
                'priority'    => 0,
                'color'       => '#6c757d',
                'icon'        => 'bi-person',
                'status'      => 'Active',
            ],
            [
                'level'       => 1,
                'code'        => 'L1',
                'name'        => 'Assistant / Junior',
                'description' => 'Can create records and upload documents. Cannot approve requests.',
                'priority'    => 1,
                'color'       => '#0dcaf0',
                'icon'        => 'bi-person-plus',
                'status'      => 'Active',
            ],
            [
                'level'       => 2,
                'code'        => 'L2',
                'name'        => 'Officer / Executive',
                'description' => 'Can edit records, process corrections, and generate reports. No final approvals.',
                'priority'    => 2,
                'color'       => '#0d6efd',
                'icon'        => 'bi-person-badge',
                'status'      => 'Active',
            ],
            [
                'level'       => 3,
                'code'        => 'L3',
                'name'        => 'Senior / Lead',
                'description' => 'Can approve requests, manage payroll processing, manage onboarding.',
                'priority'    => 3,
                'color'       => '#6f42c1',
                'icon'        => 'bi-star',
                'status'      => 'Active',
            ],
            [
                'level'       => 4,
                'code'        => 'L4',
                'name'        => 'Manager / Head',
                'description' => 'Final department approval authority. Can manage department resources and reporting.',
                'priority'    => 4,
                'color'       => '#fd7e14',
                'icon'        => 'bi-briefcase',
                'status'      => 'Active',
            ],
            [
                'level'       => 5,
                'code'        => 'L5',
                'name'        => 'Director / Executive',
                'description' => 'Company-wide approval authority. Executive dashboards, budget approval, company reporting.',
                'priority'    => 5,
                'color'       => '#dc3545',
                'icon'        => 'bi-shield-star',
                'status'      => 'Active',
            ],
        ];

        foreach ($levels as $level) {
            AccessLevel::updateOrCreate(['level' => $level['level']], $level);
        }
    }
}
