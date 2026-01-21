<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEmployee
{
    /**
     * Handle an incoming request.
     *
     * Ensures that the authenticated user has an associated employee record
     * and the employee status is active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => null,
            ], 401);
        }

        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan',
                'errors' => null,
            ], 403);
        }

        if ($employee->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Status karyawan Anda tidak aktif',
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
