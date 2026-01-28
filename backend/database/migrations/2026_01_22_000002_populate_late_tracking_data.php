<?php

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * PostgreSQL / Neon must not run this migration in a transaction
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get AttendanceService instance
        $attendanceService = app(AttendanceService::class);

        // Process all attendance records
        Attendance::chunk(100, function ($attendances) use ($attendanceService) {
            foreach ($attendances as $attendance) {
                // Skip if already calculated
                if ($attendance->is_late !== false || $attendance->late_duration_minutes !== 0) {
                    continue;
                }

                // Calculate late values
                $lateValues = $attendanceService->calculateLateValues(
                    $attendance->date,
                    $attendance->clock_in_at
                );

                // Update the record with calculated values
                $attendance->update([
                    'is_late' => $lateValues['is_late'],
                    'late_duration_minutes' => $lateValues['late_duration_minutes'],
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all values to default
        Attendance::query()->update([
            'is_late' => false,
            'late_duration_minutes' => 0,
        ]);
    }
};
