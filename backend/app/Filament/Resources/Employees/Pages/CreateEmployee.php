<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Extract user-specific data
            // $role = Arr::get($data, 'role', 'Employee');
            $role = 'employee';
            $password = $data['password'];

            // Create User
            /** @var User $user */
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($password),
            ]);

            // Assign Role
            $user->assignRole($role);

            // Prepare Employee Data
            // We exclude password and role as they are not in employees table
            $employeeData = Arr::except($data, ['password', 'role']);
            $employeeData['user_id'] = $user->id;

            // Create Employee
            return static::getModel()::create($employeeData);
        });
    }
}
