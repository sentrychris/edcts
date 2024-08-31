<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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

        $this->middleware('frontier.auth')->only('me');
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
     * Access the user based on cookie.
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function me(Request $request)
    {
        return response()->json(
            new UserResource($request->user()->load('commander.carriers'))
        );
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
            // If the user does not exist, create a new user
            $user = User::create([
                'name' => $profile->usr->customer_id,
                'email' => $email,
                'password' => bcrypt(Str::random(32))
            ]);

            // Create a new associated Frontier user
            $user->frontierUser()->create([
                'frontier_id' => $profile->usr->customer_id,
                'access_token' => $accessToken
            ]);
        }

        if ($user->frontierUser) {
            // Update the Frontier user's access token
            $user->frontierUser()->update([
                'access_token' => $accessToken
            ]);
        } else {
            // Just in case the user does exist but does not have an associated Frontier user
            $user->frontierUser()->create([
                'frontier_id' => $profile->usr->customer_id,
                'access_token' => $accessToken
            ]);
        }

        Redis::set("user_{$user->id}_frontier_token", $accessToken, 'EX', 3600*3);

        return $user;
    }
}