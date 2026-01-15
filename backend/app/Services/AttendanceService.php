<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Get all active employees with their attendance status for a specific date.
     *
     * @return Collection<int, array{employee: Employee, status: AttendanceStatus, attendance: ?Attendance, leave: ?Leave, holiday: ?Holiday}>
     */
    public function getDailyAttendanceData(Carbon $date): Collection
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return $employees->map(function (Employee $employee) use ($date) {
            return $this->getEmployeeAttendanceData($employee, $date);
        });
    }

    /**
     * Get attendance data for a specific employee on a specific date.
     *
     * @return array{employee: Employee, status: AttendanceStatus, attendance: ?Attendance, leave: ?Leave, holiday: ?Holiday, leave_type: ?string, is_late: bool, late_duration_minutes: int}
     */
    public function getEmployeeAttendanceData(Employee $employee, Carbon $date): array
    {
        $status = $this->calculateStatus($employee->id, $date);
        $attendance = Attendance::forEmployeeAndDate($employee->id, $date);
        $leave = $this->getApprovedLeave($employee->id, $date);
        $holiday = Holiday::forDate($date);

        return [
            'employee' => $employee,
            'status' => $status,
            'attendance' => $attendance,
            'leave' => $leave,
            'leave_type' => $leave?->type,
            'holiday' => $holiday,
            'is_late' => $attendance?->is_late ?? false,
            'late_duration_minutes' => $attendance?->late_duration_minutes ?? 0,
            'is_early_leave' => $attendance?->is_early_leave ?? false,
            'early_leave_duration_minutes' => $attendance?->early_leave_duration_minutes ?? 0,
        ];
    }

    /**
     * Calculate the attendance status for a specific employee on a specific date.
     */
    public function calculateStatus(int $employeeId, Carbon $date): AttendanceStatus
    {
        // 1. Is date in future?
        if ($date->isAfter(Carbon::today())) {
            return AttendanceStatus::NotYet;
        }

        // 2. Is date a Holiday?
        if (Holiday::isHoliday($date)) {
            return AttendanceStatus::Holiday;
        }

        // 3. Is date a non-working day (weekend)?
        if (!$this->isWorkingDay($date)) {
            return AttendanceStatus::Weekend;
        }

        // 4. Has approved Leave for this date?
        if ($this->getApprovedLeave($employeeId, $date) !== null) {
            return AttendanceStatus::OnLeave;
        }

        // 5. Has Attendance record?
        if (Attendance::forEmployeeAndDate($employeeId, $date) !== null) {
            return AttendanceStatus::Present;
        }

        // 6. None of above? -> Absent
        return AttendanceStatus::Absent;
    }

    /**
     * Get monthly attendance data for a date range.
     *
     * @return array{employees: Collection, days: array, statistics: array}
     */
    public function getMonthlyAttendanceData(Carbon $startDate, Carbon $endDate): array
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $days = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $days[] = $currentDate->copy();
            $currentDate->addDay();
        }

        $employeeData = $employees->map(function (Employee $employee) use ($days) {
            $dailyStatuses = [];
            $statistics = [
                'present' => 0,
                'absent' => 0,
                'leave' => 0,
                'weekend' => 0,
                'holiday' => 0,
                'late' => 0,
                'early_leave' => 0,
            ];

            foreach ($days as $day) {
                $status = $this->calculateStatus($employee->id, $day);
                $dailyStatuses[$day->format('Y-m-d')] = $status;

                match ($status) {
                    AttendanceStatus::Present => $statistics['present']++,
                    AttendanceStatus::Absent => $statistics['absent']++,
                    AttendanceStatus::OnLeave => $statistics['leave']++,
                    AttendanceStatus::Weekend => $statistics['weekend']++,
                    AttendanceStatus::Holiday => $statistics['holiday']++,
                    default => null,
                };

                // Check late and early leave for present days
                if ($status === AttendanceStatus::Present) {
                    $attendance = Attendance::forEmployeeAndDate($employee->id, $day);
                    if ($attendance?->is_late) {
                        $statistics['late']++;
                    }
                    if ($attendance?->is_early_leave) {
                        $statistics['early_leave']++;
                    }
                }
            }

            return [
                'employee' => $employee,
                'daily_statuses' => $dailyStatuses,
                'statistics' => $statistics,
            ];
        });

        // Calculate daily totals
        $dailyTotals = [];
        foreach ($days as $day) {
            $dateKey = $day->format('Y-m-d');
            $dailyTotals[$dateKey] = [
                'present' => 0,
                'absent' => 0,
                'leave' => 0,
            ];

            foreach ($employeeData as $data) {
                $status = $data['daily_statuses'][$dateKey];
                match ($status) {
                    AttendanceStatus::Present => $dailyTotals[$dateKey]['present']++,
                    AttendanceStatus::Absent => $dailyTotals[$dateKey]['absent']++,
                    AttendanceStatus::OnLeave => $dailyTotals[$dateKey]['leave']++,
                    default => null,
                };
            }
        }

        return [
            'employees' => $employeeData,
            'days' => $days,
            'daily_totals' => $dailyTotals,
        ];
    }

    /**
     * Check if a given date is a working day.
     */
    public function isWorkingDay(Carbon $date): bool
    {
        return WorkSchedule::isWorkingDay($date->dayOfWeek);
    }

    /**
     * Get approved leave for an employee on a specific date.
     */
    public function getApprovedLeave(int $employeeId, Carbon $date): ?Leave
    {
        return Leave::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }

    /**
     * Get work schedule for a specific day.
     */
    public function getWorkSchedule(Carbon $date): ?WorkSchedule
    {
        return WorkSchedule::forDayOfWeek($date->dayOfWeek);
    }
}
