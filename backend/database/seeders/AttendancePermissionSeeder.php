<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AttendancePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Attendance permissions
        $attendancePermissions = [
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            'delete_attendance',
            'delete_any_attendance',
        ];

        // Holiday permissions
        $holidayPermissions = [
            'view_holiday',
            'view_any_holiday',
            'create_holiday',
            'update_holiday',
            'delete_holiday',
            'delete_any_holiday',
        ];

        // Work Schedule permissions
        $workSchedulePermissions = [
            'view_work_schedule',
            'view_any_work_schedule',
            'update_work_schedule',
        ];

        // Attendance Review permissions
        $reviewPermissions = [
            'view_attendance_review_daily',
            'view_attendance_review_monthly',
        ];

        $allPermissions = array_merge(
            $attendancePermissions,
            $holidayPermissions,
            $workSchedulePermissions,
            $reviewPermissions
        );

        // Create permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all permissions to admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($allPermissions);

        // Employee role - only view attendance review
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employeeRole->syncPermissions([
            'view_attendance_review_daily',
            'view_attendance_review_monthly',
        ]);
    }
}
