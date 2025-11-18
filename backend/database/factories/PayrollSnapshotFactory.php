<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayrollSnapshot>
 */
class PayrollSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gross = $this->faker->randomFloat(2, 4_000_000, 20_000_000);
        $net = round($gross * $this->faker->randomFloat(2, 0.7, 0.95), 2);

        return [
            'employee_id' => Employee::factory(),
            'period_year' => (int) now()->format('Y'),
            'period_month' => $this->faker->numberBetween(1, 12),
            'gross_amount' => $gross,
            'net_amount' => $net,
        ];
    }
}
