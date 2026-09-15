<?php

namespace Database\Seeders;

use App\Models\AppRegistry;
use Illuminate\Database\Seeder;

class AppRegistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            [
                'name' => 'Bio-metrics HR System',
                'slug' => 'biometrics-hr-system',
                'icon_class' => 'bi bi-fingerprint',
                'gradient_css' => 'linear-gradient(135deg, #f95716, #ff804a)',
                'category' => 'hr',
                'category_label' => 'Enterprise HR System',
                'tag' => 'Main App',
                'launch_url' => route('dashboard'),
                'description' => 'Central workforce management, attendance tracking, device administration, shifts, onboarding, and payroll center.',
                'target_blank' => false,
                'roles_allowed' => ['Admin', 'Manager', 'Employee'],
                'sort_order' => 1,
                'status' => 'Active',
            ],
            [
                'name' => 'Carbonio Webmail',
                'slug' => 'carbonio-webmail',
                'icon_class' => 'bi bi-envelope-paper-fill',
                'gradient_css' => 'linear-gradient(135deg, #0d6efd, #0dcaf0)',
                'category' => 'mail',
                'category_label' => 'Communication',
                'tag' => 'Communication',
                'launch_url' => 'https://mail.axisnextgen.dev/',
                'description' => 'Enterprise email, calendar, and collaboration suite with multi-domain support.',
                'target_blank' => true,
                'roles_allowed' => ['Admin', 'Manager', 'Employee'],
                'sort_order' => 2,
                'status' => 'Active',
            ],
        ];

        foreach ($apps as $app) {
            AppRegistry::updateOrCreate(
                ['slug' => $app['slug']],
                $app
            );
        }
    }
}
