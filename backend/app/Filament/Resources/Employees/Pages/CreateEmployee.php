<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $roleName = 'employee';

        // Pre-fetch the role outside of any transaction context
        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

        if (!$role) {
            Log::warning("Role '{$roleName}' not found. Creating it now. Consider running: php artisan db:seed --class=RoleSeeder");
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Step 1: Create User (no transaction — PostgreSQL on Koyeb poisons transactions between slow queries)
        /** @var User $user */
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Step 2: Assign Role — with manual cleanup if it fails
        try {
            $user->assignRole($role);
        } catch (\Throwable $e) {
            Log::error("Failed to assign role to user {$user->id}: {$e->getMessage()}");
            // Clean up: delete the user we just created
            $user->forceDelete();
            throw $e;
        }

        // Step 3: Create Employee — with manual cleanup if it fails
        try {
            $employeeData = Arr::except($data, ['password', 'role']);
            $employeeData['user_id'] = $user->id;

            return static::getModel()::create($employeeData);
        } catch (\Throwable $e) {
            Log::error("Failed to create employee for user {$user->id}: {$e->getMessage()}");
            // Clean up: remove role assignment and delete the user
            $user->roles()->detach();
            $user->forceDelete();
            throw $e;
        }
    }
}
