<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'day_of_week' => DayOfWeek::Sunday->value,
                'is_working_day' => false,
                'work_start_time' => null,
                'work_end_time' => null,
            ],
            [
                'day_of_week' => DayOfWeek::Monday->value,
                'is_working_day' => true,
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'day_of_week' => DayOfWeek::Tuesday->value,
                'is_working_day' => true,
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'day_of_week' => DayOfWeek::Wednesday->value,
                'is_working_day' => true,
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'day_of_week' => DayOfWeek::Thursday->value,
                'is_working_day' => true,
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'day_of_week' => DayOfWeek::Friday->value,
                'is_working_day' => true,
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'day_of_week' => DayOfWeek::Saturday->value,
                'is_working_day' => false,
                'work_start_time' => null,
                'work_end_time' => null,
            ],
        ];

        foreach ($schedules as $schedule) {
            WorkSchedule::updateOrCreate(
                ['day_of_week' => $schedule['day_of_week']],
                $schedule
            );
        }
    }
}
