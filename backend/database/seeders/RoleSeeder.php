<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates all roles and permissions for the application.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions by module
        $permissions = [
            // User Management
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',

            // Employee Management
            'view_employee',
            'view_any_employee',
            'create_employee',
            'update_employee',
            'delete_employee',
            'delete_any_employee',

            // Department Management
            'view_department',
            'view_any_department',
            'create_department',
            'update_department',
            'delete_department',
            'delete_any_department',

            // Attendance Management
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            'delete_attendance',
            'delete_any_attendance',

            // Attendance Review
            'view_attendance_review_daily',
            'view_attendance_review_monthly',

            // Holiday Management
            'view_holiday',
            'view_any_holiday',
            'create_holiday',
            'update_holiday',
            'delete_holiday',
            'delete_any_holiday',

            // Work Schedule Management
            'view_work_schedule',
            'view_any_work_schedule',
            'update_work_schedule',

            // Leave Management
            'view_leave',
            'view_any_leave',
            'create_leave',
            'update_leave',
            'delete_leave',
            'delete_any_leave',
            'approve_leave',

            // Overtime Management
            'view_overtime',
            'view_any_overtime',
            'create_overtime',
            'update_overtime',
            'delete_overtime',
            'delete_any_overtime',
            'approve_overtime',

            // Payroll Management
            'view_payroll',
            'view_any_payroll',
            'create_payroll',
            'update_payroll',
            'delete_payroll',
            'delete_any_payroll',
            'process_payroll',

            // Settings Management
            'view_setting',
            'update_setting',

            // Activity Log
            'view_activity_log',
            'view_any_activity_log',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create Admin role with all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions($permissions);

        // Create Employee role with limited permissions
        $employeeRole = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web',
        ]);
        $employeeRole->syncPermissions([
            // Can view their own attendance
            'view_attendance_review_daily',
            'view_attendance_review_monthly',
            // Can view their own leave
            'view_leave',
            'create_leave',
            // Can view their own overtime
            'view_overtime',
            'create_overtime',
        ]);

        // Create HR role (optional - for future use)
        $hrRole = Role::firstOrCreate([
            'name' => 'hr',
            'guard_name' => 'web',
        ]);
        $hrRole->syncPermissions([
            // Employee management
            'view_employee',
            'view_any_employee',
            'create_employee',
            'update_employee',
            // Department management
            'view_department',
            'view_any_department',
            // Attendance
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            'view_attendance_review_daily',
            'view_attendance_review_monthly',
            // Holiday
            'view_holiday',
            'view_any_holiday',
            'create_holiday',
            'update_holiday',
            // Work Schedule
            'view_work_schedule',
            'view_any_work_schedule',
            'update_work_schedule',
            // Leave
            'view_leave',
            'view_any_leave',
            'create_leave',
            'update_leave',
            'approve_leave',
            // Overtime
            'view_overtime',
            'view_any_overtime',
            'approve_overtime',
            // Activity Log
            'view_activity_log',
            'view_any_activity_log',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('- Admin role: Full access');
        $this->command->info('- Employee role: Limited access (view own data, create leave/overtime)');
        $this->command->info('- HR role: HR-related permissions');
    }
}
