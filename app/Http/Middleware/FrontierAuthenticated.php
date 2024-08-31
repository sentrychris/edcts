<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class FrontierAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('cmdr_token');

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                Auth::setUser($accessToken->tokenable);

                return $next($request->merge([
                    'user' => new UserResource(Auth::user())
                ]));

            } else {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }
        }

        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }
}
