<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;
    public function definition(): array
    {
        return [
            'employee_id' => $this->faker->unique()->randomNumber(5),
            'employee_number' => $this->faker->unique()->randomNumber(5),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'department_id' => Department::factory(),
            'status' => 'Active',
        ];
    }
}
