<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockIn = $this->faker->dateTimeBetween('-1 month', 'now');
        $clockOut = (clone $clockIn)->modify('+8 hours');

        return [
            'employee_id' => Employee::factory(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'clock_in_client_ts' => $clockIn,
            'clock_out_client_ts' => $clockOut,
            'lat' => $this->faker->randomFloat(7, -90, 90),
            'lng' => $this->faker->randomFloat(7, -180, 180),
            'location' => $this->faker->address(),
            'location_accuracy' => $this->faker->randomFloat(3, 5, 100),
            'device_id' => $this->faker->uuid(),
            'photo_url' => $this->faker->imageUrl(),
            'source' => $this->faker->randomElement(['pwa', 'import', 'manual']),
            'sync_status' => $this->faker->randomElement(['pending', 'synced', 'failed']),
        ];
    }
}
