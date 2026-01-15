<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // 1. Roles and Permissions (must be first)
        $this->call(RoleSeeder::class);
        $this->command->newLine();

        // 2. Users (depends on roles)
        $this->call(UserSeeder::class);
        $this->command->newLine();

        // 3. Departments
        $this->call(DepartmentSeeder::class);
        $this->command->newLine();

        // 4. Employees (depends on departments)
        $this->call(EmployeeSeeder::class);
        $this->command->newLine();

        // 5. Work Schedules (7 days)
        $this->call(WorkScheduleSeeder::class);
        $this->command->newLine();

        // 6. Holidays
        $this->call(HolidaySeeder::class);
        $this->command->newLine();

        $this->command->info('========================================');
        $this->command->info('Database seeding completed successfully!');
        $this->command->info('========================================');
        $this->command->newLine();
        $this->command->info('You can login with:');
        $this->command->info('  Admin: admin@gmail.com / 12345678');
        $this->command->info('  HR:    hr@gmail.com / 12345678');
    }
}
