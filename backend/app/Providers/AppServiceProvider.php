<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\WorkSchedule;
use App\Policies\AttendancePolicy;
use App\Policies\HolidayPolicy;
use App\Policies\WorkSchedulePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register policies
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Holiday::class, HolidayPolicy::class);
        Gate::policy(WorkSchedule::class, WorkSchedulePolicy::class);
    }
}
