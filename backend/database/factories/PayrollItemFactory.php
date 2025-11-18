<?php

namespace Database\Factories;

use App\Models\Payroll;
use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayrollItem>
 */
class PayrollItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payroll_id' => Payroll::factory(),
            'component_id' => SalaryComponent::factory(),
            'amount' => $this->faker->randomFloat(2, 50_000, 5_000_000),
            'note' => $this->faker->sentence(),
        ];
    }
}
