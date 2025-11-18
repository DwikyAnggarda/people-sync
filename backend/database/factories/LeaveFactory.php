<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leave>
 */
class LeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+' . $this->faker->numberBetween(1, 5) . ' days');
        $days = max(1, $start->diff($end)->days);

        return [
            'employee_id' => Employee::factory(),
            'leave_type' => $this->faker->randomElement(['annual', 'sick', 'parental', 'unpaid']),
            'start_date' => $start,
            'end_date' => $end,
            'days' => $days,
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => $this->faker->boolean(40) ? User::factory() : null,
        ];
    }
}
