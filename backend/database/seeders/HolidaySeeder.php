<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // Recurring holidays (same date every year)
            [
                'name' => 'Tahun Baru Masehi',
                'date' => '2026-01-01',
                'is_recurring' => true,
                'description' => 'Perayaan tahun baru masehi',
            ],
            [
                'name' => 'Hari Buruh Internasional',
                'date' => '2026-05-01',
                'is_recurring' => true,
                'description' => 'Hari Buruh Internasional',
            ],
            [
                'name' => 'Hari Lahir Pancasila',
                'date' => '2026-06-01',
                'is_recurring' => true,
                'description' => 'Memperingati hari lahir Pancasila',
            ],
            [
                'name' => 'Hari Kemerdekaan RI',
                'date' => '2026-08-17',
                'is_recurring' => true,
                'description' => 'Hari Kemerdekaan Republik Indonesia',
            ],
            [
                'name' => 'Hari Natal',
                'date' => '2026-12-25',
                'is_recurring' => true,
                'description' => 'Hari Raya Natal',
            ],

            // Non-recurring holidays for 2026 (dates change yearly)
            [
                'name' => 'Isra Miraj Nabi Muhammad SAW',
                'date' => '2026-02-07',
                'is_recurring' => false,
                'description' => 'Memperingati Isra Miraj Nabi Muhammad SAW',
            ],
            [
                'name' => 'Tahun Baru Imlek',
                'date' => '2026-02-17',
                'is_recurring' => false,
                'description' => 'Tahun Baru Imlek 2577',
            ],
            [
                'name' => 'Hari Raya Nyepi',
                'date' => '2026-03-19',
                'is_recurring' => false,
                'description' => 'Tahun Baru Saka 1948',
            ],
            [
                'name' => 'Wafat Isa Al Masih',
                'date' => '2026-04-03',
                'is_recurring' => false,
                'description' => 'Jumat Agung',
            ],
            [
                'name' => 'Hari Raya Idul Fitri',
                'date' => '2026-03-31',
                'is_recurring' => false,
                'description' => 'Hari Raya Idul Fitri 1447 H (Hari 1)',
            ],
            [
                'name' => 'Hari Raya Idul Fitri',
                'date' => '2026-04-01',
                'is_recurring' => false,
                'description' => 'Hari Raya Idul Fitri 1447 H (Hari 2)',
            ],
            [
                'name' => 'Hari Raya Waisak',
                'date' => '2026-05-12',
                'is_recurring' => false,
                'description' => 'Hari Raya Waisak 2570',
            ],
            [
                'name' => 'Kenaikan Isa Al Masih',
                'date' => '2026-05-14',
                'is_recurring' => false,
                'description' => 'Kenaikan Isa Al Masih',
            ],
            [
                'name' => 'Hari Raya Idul Adha',
                'date' => '2026-06-07',
                'is_recurring' => false,
                'description' => 'Hari Raya Idul Adha 1447 H',
            ],
            [
                'name' => 'Tahun Baru Islam',
                'date' => '2026-06-27',
                'is_recurring' => false,
                'description' => 'Tahun Baru Islam 1448 H',
            ],
            [
                'name' => 'Maulid Nabi Muhammad SAW',
                'date' => '2026-09-05',
                'is_recurring' => false,
                'description' => 'Memperingati Maulid Nabi Muhammad SAW',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                [
                    'name' => $holiday['name'],
                    'date' => $holiday['date'],
                ],
                $holiday
            );
        }
    }
}
