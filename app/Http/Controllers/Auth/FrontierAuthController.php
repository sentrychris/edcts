<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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

            $user = $this->createUserIfNotExists($profile);

            return response()->json([
                'access_token' => $auth->access_token,
                'expires_on' => Carbon::parse(Carbon::now())
                    ->addSeconds($auth->expires_in)
                    ->toIso8601String(),
                'profile' => $user->load('frontierUser')
            ]);
        } catch (Exception $e) {
            Log::error('Frontier Auth Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    /**
     *  Create a user if they do not exist.
     * 
     * @param mixed $profile - the user details from the decoded token
     * @return User - the user model
     */
    private function createUserIfNotExists(mixed $profile): User
    {
        $user = User::whereEmail($profile->usr->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $profile->usr->firstname . ' ' . $profile->usr->lastname,
                'email' => $profile->usr->email,
                'password' => bcrypt(Str::random(32))
            ]);

            $user->frontierUser()->create([
                'frontier_id' => $profile->usr->customer_id,
            ]);
        }

        return $user;
    }
}