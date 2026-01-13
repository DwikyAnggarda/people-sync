<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 1. Ambil user TERMASUK soft-deleted
        $user = \App\Models\User::withTrashed()
            ->where('email', $credentials['email'])
            ->first();

        // 2. User tidak ditemukan
        if (! $user) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // 3. User soft-deleted
        if ($user->deleted_at !== null) {
            return response()->json([
                'message' => 'User account is inactive',
            ], 403);
        }

        // 4. Password salah
        if (! \Illuminate\Support\Facades\Hash::check(
            $credentials['password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // 5. Issue token (PASTIKAN pakai user instance)
        $token = auth()->login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}
