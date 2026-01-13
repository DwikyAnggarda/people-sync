<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

}
