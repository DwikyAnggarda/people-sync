<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['jwt.auth', 'active.user'])->get('/me', fn (Request $r) => [
    'id' => $r->user()->id,
    'email' => $r->user()->email,
    'name' => $r->user()->name,
]);

// Route::middleware(['jwt.auth', 'active.user'])->get('/me', function (Request $request) {
//     return response()->json([
//         'id' => $request->get('auth_user')->id,
//         'name' => $request->get('auth_user')->name,
//         'email' => $request->get('auth_user')->email,
//     ]);
// });

Route::middleware(['jwt.auth'])->get('/me-test', function (Request $request) {
    return 'OK';
});