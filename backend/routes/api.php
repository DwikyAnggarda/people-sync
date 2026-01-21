<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Mobile App API Routes (v1)
| Base URL: /api/v1
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);

        Route::middleware(['jwt.auth', 'active.user', 'employee.only'])->group(function () {
            Route::get('/me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
            Route::post('/refresh', [\App\Http\Controllers\Api\V1\AuthController::class, 'refresh']);
            Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Requires JWT + Active User + Employee)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['jwt.auth', 'active.user', 'employee.only'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Attendance Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('attendances')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'index']);
            Route::get('/today', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'today']);
            Route::get('/summary', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'summary']);
            Route::post('/clock-in', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'clockIn']);
            Route::post('/clock-out', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'clockOut']);
        });

        /*
        |--------------------------------------------------------------------------
        | Leave Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('leaves')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\LeaveController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\V1\LeaveController::class, 'store']);
            Route::get('/{leave}', [\App\Http\Controllers\Api\V1\LeaveController::class, 'show']);
            Route::delete('/{leave}', [\App\Http\Controllers\Api\V1\LeaveController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Overtime Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('overtimes')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\OvertimeController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\V1\OvertimeController::class, 'store']);
            Route::get('/{overtime}', [\App\Http\Controllers\Api\V1\OvertimeController::class, 'show']);
            Route::delete('/{overtime}', [\App\Http\Controllers\Api\V1\OvertimeController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Supporting Data Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/locations', [\App\Http\Controllers\Api\V1\LocationController::class, 'index']);
        Route::get('/holidays', [\App\Http\Controllers\Api\V1\HolidayController::class, 'index']);
        Route::get('/work-schedules', [\App\Http\Controllers\Api\V1\WorkScheduleController::class, 'index']);
    });

});
