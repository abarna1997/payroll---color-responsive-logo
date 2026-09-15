<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Branch;
use App\Models\Company;

class BranchFactory extends Factory
{
    protected $model = Branch::class;
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_code' => $this->faker->unique()->lexify('???'),
            'branch_name' => $this->faker->city(),
            'status' => 'Active',
        ];
    }
}
