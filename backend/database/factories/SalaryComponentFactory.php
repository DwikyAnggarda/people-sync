<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalaryComponent>
 */
class SalaryComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Basic Salary', 'Transport Allowance', 'Meal Deduction', 'Incentive']) . ' ' . $this->faker->randomDigitNotNull(),
            'type' => $this->faker->randomElement(['earning', 'deduction']),
            'default_amount' => $this->faker->randomFloat(2, 50000, 5000000),
        ];
    }
}
