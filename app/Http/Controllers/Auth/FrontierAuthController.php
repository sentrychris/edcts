<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\PersonalAccessToken;

class FrontierAuthController extends Controller
{
    /**
     * The Frontier API manager.
     * 
     * @var FrontierAuthService
     */
    private $frontierAuthService;

    /**
     * Create a new controller instance.
     * 
     * @param FrontierAuthService $frontierAuthService - the frontier auth service
     */
    public function __construct(FrontierAuthService $frontierAuthService)
    {
        $this->frontierAuthService = $frontierAuthService;
    }

    /**
     * Return the login URL.
     * 
     * @return mixed - the response
     */
    public function login()
    {
        return response()->json([
            'data' => $this->frontierAuthService
                ->getAuthorizationServerInformation()
        ]);
    }

    /**
     * Frontier SSO callback endpoint.
     * 
     * This method receives the callback from the Frontier oauth server, containing the
     * authorization grant code. This code is then exchanged for an access token which
     * is then used to retrieve the user profile.
     *
     * @param Request $request - the request object
     * @return mixed - the response
     */
    public function callback(Request $request)
    {
        try {
            $auth = $this->frontierAuthService->authorize($request);
            $profile = $this->frontierAuthService->decode($auth->access_token);

            $user = $this->verifyUser($profile, $auth->access_token);
            $token = $user->createToken('frontier')->plainTextToken;

            return redirect()->to('http://localhost:4201/api/auth/callback')->cookie(
                'cmdr_token', $token, 60, '/', null, true, true
            );
        } catch (Exception $e) {
            Log::error('Frontier Auth Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    /**
     * Access the user token based on cookie.
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function token(Request $request)
    {
        $token = $request->cookie('cmdr_token');

        if (! $token) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                Auth::login($accessToken->tokenable);

                return response()->json([
                    'data' => [
                        'user' => new UserResource(Auth::user()),
                        'token' => $token
                    ]
                ]);
            }
        }
    }

    /**
     *  Verify the user.
     * 
     * @param mixed $profile - the user details from the decoded token
     * @param string $accessToken - the access token
     * @return User - the user model
     */
    private function verifyUser(mixed $profile, string $accessToken): User
    {
        $email = $profile->usr->customer_id  . '@versyx.net';
        $user = User::whereEmail($email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $profile->usr->customer_id,
                'email' => $email,
                'password' => bcrypt(Str::random(32))
            ]);

            $user->frontierUser()->create([
                'frontier_id' => $profile->usr->customer_id,
                'access_token' => $accessToken
            ]);
        }

        if ($user->frontierUser) {
            $user->frontierUser()->update([
                'access_token' => $accessToken
            ]);
        } else {
            $user->frontierUser()->create([
                'frontier_id' => $profile->usr->customer_id,
                'access_token' => $accessToken
            ]);
        }

        Redis::set("user_{$user->id}_frontier_token", $accessToken, 'EX', 3600*3);

        return $user;
    }
}