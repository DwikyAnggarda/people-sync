<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ClockInRequest;
use App\Http\Requests\Api\V1\ClockOutRequest;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\AttendanceSummaryResource;
use App\Http\Resources\Api\V1\TodayAttendanceResource;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Location;
use App\Models\WorkSchedule;
use App\Services\AttendanceService;
use App\Enums\AttendanceSource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Get paginated attendance history.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $perPage = min($request->input('per_page', 15), 50);

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        return $this->successWithPagination(
            AttendanceResource::collection($attendances),
            $this->getPaginationMeta($attendances)
        );
    }

    /**
     * Get today's attendance status.
     */
    public function today(): JsonResponse
    {
        $employee = auth()->user()->employee;
        $today = now()->startOfDay();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        $workSchedule = WorkSchedule::forDayOfWeek($today->dayOfWeek);
        $isHoliday = Holiday::whereDate('date', $today)->exists();
        $isWorkingDay = $workSchedule?->is_working_day ?? false;

        if ($attendance) {
            return $this->success(new TodayAttendanceResource($attendance));
        }

        // No attendance yet
        return $this->success([
            'date' => $today->format('Y-m-d'),
            'clock_in_at' => null,
            'clock_out_at' => null,
            'is_working_day' => $isWorkingDay && !$isHoliday,
            'is_holiday' => $isHoliday,
            'can_clock_in' => $isWorkingDay && !$isHoliday,
            'can_clock_out' => false,
            'work_schedule' => $workSchedule ? [
                'work_start_time' => $workSchedule->work_start_time?->format('H:i'),
                'work_end_time' => $workSchedule->work_end_time?->format('H:i'),
            ] : null,
        ]);
    }

    /**
     * Get monthly attendance summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Only count up to today if current month
        if ($endDate->isFuture()) {
            $endDate = now()->endOfDay();
        }

        // Get all attendances for the month
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Get holidays
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();

        // Get work schedules
        $workSchedules = WorkSchedule::all()->keyBy('day_of_week');

        // Calculate statistics
        $present = 0;
        $absent = 0;
        $late = 0;
        $onLeave = 0; // TODO: Integrate with leaves
        $totalWorkMinutes = 0;
        $totalWorkingDays = 0;
        $clockInTimes = [];
        $clockOutTimes = [];

        $currentDate = $startDate->copy();

        \Log::info("currentdate", ['date' => $currentDate->toDateString(), 'enddate' => $endDate->toDateString()]);

        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $dateStr = $currentDate->format('Y-m-d');
            $schedule = $workSchedules->get($dayOfWeek);

            // Check if it's a working day and not a holiday
            if ($schedule?->is_working_day && !in_array($dateStr, $holidays)) {
                $totalWorkingDays++;

                $attendance = $attendances->firstWhere(function($item) use ($dateStr) {
                    return $item->date->format('Y-m-d') === $dateStr;
                });

                if ($attendance) {
                    $present++;
                    
                    // Check is_late safely - only count if attendance was clocked in
                    if ($attendance->clock_in_at && $attendance->is_late === true) {
                        $late++;
                    }
                    
                    // Only count work duration if both clock in and clock out exist
                    if ($attendance->clock_in_at && $attendance->clock_out_at && $attendance->work_duration_minutes) {
                        $totalWorkMinutes += $attendance->work_duration_minutes;
                    }
                    
                    if ($attendance->clock_in_at) {
                        $clockInTimes[] = $attendance->clock_in_at->format('H:i');
                    }
                    if ($attendance->clock_out_at) {
                        $clockOutTimes[] = $attendance->clock_out_at->format('H:i');
                    }
                } else {
                    // Only count as absent if date is in the past
                    if ($currentDate->isPast()) {
                        $absent++;
                    }
                }
            }

            $currentDate->addDay();
        }

        // Calculate averages
        $averageClockIn = null;
        $averageClockOut = null;

        if (count($clockInTimes) > 0) {
            $totalMinutes = collect($clockInTimes)->map(function ($time) {
                [$h, $m] = explode(':', $time);
                return (int)$h * 60 + (int)$m;
            })->average();
            $averageClockIn = sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
        }

        if (count($clockOutTimes) > 0) {
            $totalMinutes = collect($clockOutTimes)->map(function ($time) {
                [$h, $m] = explode(':', $time);
                return (int)$h * 60 + (int)$m;
            })->average();
            $averageClockOut = sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
        }

        return $this->success([
            'month' => (int)$month,
            'year' => (int)$year,
            'total_working_days' => $totalWorkingDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'on_leave' => $onLeave,
            'holidays' => count($holidays),
            'total_work_hours' => round($totalWorkMinutes / 60, 1),
            'average_clock_in' => $averageClockIn,
            'average_clock_out' => $averageClockOut,
        ]);
    }

    /**
     * Clock in.
     */
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $employee = auth()->user()->employee;
        $today = now()->startOfDay();

        // Check if already clocked in
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            return $this->error('Anda sudah melakukan clock in hari ini', null, 422);
        }

        // Validate location
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $nearestLocation = Location::findNearest($latitude, $longitude);
        $isWithinRadius = false;
        $distance = null;

        if ($nearestLocation) {
            $distance = $nearestLocation->calculateDistance($latitude, $longitude);
            $isWithinRadius = $nearestLocation->isWithinRadius($latitude, $longitude);
        }

        if (!$isWithinRadius) {
            $message = $nearestLocation
                ? "Jarak Anda " . round($distance) . "m dari lokasi terdekat (maks {$nearestLocation->radius_meters}m)"
                : "Tidak ada lokasi kehadiran yang tersedia";

            return $this->error('Lokasi Anda di luar area yang diizinkan', [
                'location' => [$message],
            ], 422);
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances/' . $employee->id, 'private');
        }

        // Create attendance
        $attendance = DB::transaction(function () use ($employee, $today, $latitude, $longitude, $photoPath) {
            $attendanceService = app(AttendanceService::class);
            $lateValues = $attendanceService->calculateLateValues($today, now());

            return Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'clock_in_at' => now(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'photo_path' => $photoPath,
                'source' => AttendanceSource::Mobile,
                'is_late' => $lateValues['is_late'],
                'late_duration_minutes' => $lateValues['late_duration_minutes'],
            ]);
        });

        return $this->success([
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d'),
            'clock_in_at' => $attendance->clock_in_at->format('Y-m-d H:i:s'),
            'clock_out_at' => null,
            'is_late' => $attendance->is_late,
            'late_duration_minutes' => $attendance->late_duration_minutes,
            'late_duration_formatted' => $attendance->late_duration_formatted,
            'location' => [
                'id' => $nearestLocation->id,
                'name' => $nearestLocation->name,
                'is_within_radius' => true,
                'distance_meters' => round($distance),
            ],
        ], 'Clock in berhasil', 201);
    }

    /**
     * Clock out.
     */
    public function clockOut(ClockOutRequest $request): JsonResponse
    {
        $employee = auth()->user()->employee;
        $today = now()->startOfDay();

        // Check if clocked in
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return $this->error('Anda belum melakukan clock in hari ini', null, 422);
        }

        if ($attendance->clock_out_at) {
            return $this->error('Anda sudah melakukan clock out hari ini', null, 422);
        }

        // Validate location (optional for clock out, just record it)
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Handle photo upload
        $photoPath = $attendance->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances/' . $employee->id, 'private');
        }

        // Update attendance
        $attendance = DB::transaction(function () use ($attendance, $latitude, $longitude, $photoPath) {
            $attendance->update([
                'clock_out_at' => now(),
                'latitude' => $latitude ?? $attendance->latitude,
                'longitude' => $longitude ?? $attendance->longitude,
                'photo_path' => $photoPath,
            ]);

            return $attendance->fresh();
        });

        return $this->success(new AttendanceResource($attendance), 'Clock out berhasil');
    }
}
