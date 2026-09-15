<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Shift;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;
    public function definition(): array
    {
        return [
            'shift_name' => $this->faker->word() . ' Shift',
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ];
    }
}
