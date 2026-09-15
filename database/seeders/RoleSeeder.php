<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Administrator', 'slug' => 'super-administrator', 'description' => 'Full administrative access across system', 'category' => 'System', 'is_system_role' => true, 'status' => 'Active', 'sort_order' => 1],
            ['name' => 'HR Administrator', 'slug' => 'hr-administrator', 'description' => 'Human resources & employee management', 'category' => 'HR', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 2],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Department and team approval manager', 'category' => 'Management', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 3],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'description' => 'Shift & attendance supervisor', 'category' => 'Management', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 4],
            ['name' => 'Employee', 'slug' => 'employee', 'description' => 'Standard staff employee portal user', 'category' => 'Staff', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 5],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}
