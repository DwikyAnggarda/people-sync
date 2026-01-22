<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\EmployeeResource;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Try to invalidate token, gracefully handle if blacklist is disabled.
     */
    private function tryInvalidateToken(string $token): void
    {
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // Blacklist not enabled, token won't be invalidated server-side
        }
    }

    /**
     * Login and get JWT token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->error('Email atau password salah', null, 401);
        }

        $user = auth()->user();

        // Check if user is active
        if ($user->deleted_at !== null) {
            $this->tryInvalidateToken($token);
            return $this->error('Akun Anda tidak aktif', null, 403);
        }

        // Check if user has employee record
        $employee = $user->employee;
        if (!$employee) {
            $this->tryInvalidateToken($token);
            return $this->error('Akun Anda tidak terdaftar sebagai karyawan', null, 403);
        }

        // Check if employee is active
        if ($employee->status !== 'active') {
            $this->tryInvalidateToken($token);
            return $this->error('Status karyawan Anda tidak aktif', null, 403);
        }

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => new UserResource($user),
            'employee' => new EmployeeResource($employee->load('department')),
        ], 'Login berhasil');
    }

    /**
     * Get authenticated user and employee data.
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        $employee = $user->employee;

        return $this->success([
            'user' => new UserResource($user),
            'employee' => $employee ? new EmployeeResource($employee->load('department')) : null,
        ]);
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): JsonResponse
    {
        $token = JWTAuth::refresh();

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Token berhasil diperbarui');
    }

    /**
     * Logout and invalidate token.
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // Token blacklist not enabled, just proceed with logout
            // The client should discard the token on their side
        }

        return $this->success(null, 'Logout berhasil');
    }
}
