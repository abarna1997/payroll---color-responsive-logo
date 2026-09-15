<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Department;
use App\Models\Company;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'department_code' => $this->faker->unique()->lexify('???'),
            'department_name' => $this->faker->word(),
        ];
    }
}
