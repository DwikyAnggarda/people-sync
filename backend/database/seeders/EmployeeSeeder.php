<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments (skip parent departments, use child departments)
        $departments = Department::whereNotNull('parent_id')->get();

        if ($departments->isEmpty()) {
            $this->command->warn('No departments found. Please run DepartmentSeeder first.');
            return;
        }

        // Sample employees data
        $employees = [
            [
                'employee_number' => 'EMP001',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@company.com',
                'status' => 'active',
                'joined_at' => '2023-01-15',
            ],
            [
                'employee_number' => 'EMP002',
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@company.com',
                'status' => 'active',
                'joined_at' => '2023-02-01',
            ],
            [
                'employee_number' => 'EMP003',
                'name' => 'Ahmad Hidayat',
                'email' => 'ahmad.hidayat@company.com',
                'status' => 'active',
                'joined_at' => '2023-03-10',
            ],
            [
                'employee_number' => 'EMP004',
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@company.com',
                'status' => 'active',
                'joined_at' => '2023-04-05',
            ],
            [
                'employee_number' => 'EMP005',
                'name' => 'Rudi Hartono',
                'email' => 'rudi.hartono@company.com',
                'status' => 'active',
                'joined_at' => '2023-05-20',
            ],
            [
                'employee_number' => 'EMP006',
                'name' => 'Putri Wulandari',
                'email' => 'putri.wulandari@company.com',
                'status' => 'active',
                'joined_at' => '2023-06-15',
            ],
            [
                'employee_number' => 'EMP007',
                'name' => 'Agus Setiawan',
                'email' => 'agus.setiawan@company.com',
                'status' => 'active',
                'joined_at' => '2023-07-01',
            ],
            [
                'employee_number' => 'EMP008',
                'name' => 'Maya Sari',
                'email' => 'maya.sari@company.com',
                'status' => 'active',
                'joined_at' => '2023-08-10',
            ],
            [
                'employee_number' => 'EMP009',
                'name' => 'Doni Pratama',
                'email' => 'doni.pratama@company.com',
                'status' => 'inactive',
                'joined_at' => '2022-01-15',
            ],
            [
                'employee_number' => 'EMP010',
                'name' => 'Linda Permata',
                'email' => 'linda.permata@company.com',
                'status' => 'active',
                'joined_at' => '2024-01-05',
            ],
        ];

        foreach ($employees as $index => $empData) {
            // Assign to random department
            $department = $departments->random();

            // Create employee (without user account for now)
            Employee::firstOrCreate(
                ['employee_number' => $empData['employee_number']],
                [
                    'name' => $empData['name'],
                    'email' => $empData['email'],
                    'department_id' => $department->id,
                    'status' => $empData['status'],
                    'joined_at' => $empData['joined_at'],
                    'user_id' => null, // No user account linked
                ]
            );
        }

        $this->command->info('Employees seeded successfully! (' . Employee::count() . ' employees)');
    }
}
