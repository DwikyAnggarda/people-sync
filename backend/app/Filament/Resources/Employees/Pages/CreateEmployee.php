<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $roleName = 'employee';

        // Pre-check: Ensure the role exists OUTSIDE the transaction
        // This prevents poisoning the PostgreSQL transaction if the role is missing
        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

        if (!$role) {
            // Auto-create the role if it doesn't exist (safety net for fresh deployments)
            Log::warning("Role '{$roleName}' not found. Creating it now. Consider running: php artisan db:seed --class=RoleSeeder");
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                return DB::transaction(function () use ($data, $role) {
                    $password = $data['password'];

                    // Create User
                    /** @var User $user */
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($password),
                    ]);

                    // Assign Role using the pre-fetched role model (avoids extra SELECT inside transaction)
                    $user->assignRole($role);

                    // Prepare Employee Data
                    $employeeData = Arr::except($data, ['password', 'role']);
                    $employeeData['user_id'] = $user->id;

                    // Create Employee
                    return static::getModel()::create($employeeData);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                Log::warning("CreateEmployee attempt {$attempt}/{$maxAttempts} failed: {$e->getMessage()}");

                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                // Wait before retrying (exponential backoff: 500ms, 1s, 2s)
                usleep(250_000 * (2 ** $attempt));
            }
        }

        // This should never be reached, but satisfies static analysis
        throw new \RuntimeException('Failed to create employee after maximum attempts.');
    }
}

