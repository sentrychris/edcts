<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Register users
     */
    public function register(RegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('adminLogin')->plainTextToken;

        return response()->json([
            'name' => $user->name,
            'avatar' => $user->avatar,
            'token' => $token,
            'expiry' => (config('sanctum.expiration') * 60),
        ]);
    }

    /**
     * Login users
     */
    public function login(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        $auth = $request->only('email', 'password');

        if (! Auth::guard('web')->attempt($auth)) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $token = $user->createToken('adminLogin')->plainTextToken;

        return response()->json([
            'name' => $user->name,
            'token' => $token,
            'expiry' => (config('sanctum.expiration') * 60),
        ]);
    }

    /**
     * Logout users
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    /**
     * Fetch authenticated user
     */
    public function me(Request $request)
    {
        if ($request->user()) {
            return response()->json(
              new UserResource($request->user()->load('commander.carriers'))
            );
        }

        return response()->json([
            'message' => 'Unauthorized.',
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}