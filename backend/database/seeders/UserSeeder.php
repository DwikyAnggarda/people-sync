<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Administrator',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role using Spatie
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("Admin user created/updated:");
        $this->command->info("  Email: admin@gmail.com");
        $this->command->info("  Password: 12345678");

        // Create HR User (optional)
        $hr = User::firstOrCreate(
            ['email' => 'hr@gmail.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'HR Manager',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        if (!$hr->hasRole('hr')) {
            $hr->assignRole('hr');
        }

        $this->command->info("HR user created/updated:");
        $this->command->info("  Email: hr@gmail.com");
        $this->command->info("  Password: 12345678");
    }
}
