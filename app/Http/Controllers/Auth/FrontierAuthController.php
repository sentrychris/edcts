<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\System;
use App\Models\User;
use App\Services\EdsmApiService;
use App\Services\Frontier\FrontierCApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FrontierAuthController extends Controller
{
    /**
     * Frontier Auth service.
     * 
     * @var FrontierAuthService
     */
    private $frontierAuthService;

    /**
     * Frontier CAPI service.
     * 
     * @var FrontierCApiService
     */
    private $frontierCApiService;

    /**
     * EDSM API Service.
     *
     * @var EdsmApiService
     */
    private EdsmApiService $edsmApiService;

    /**
     * Create a new controller instance.
     * 
     * @param FrontierAuthService $frontierAuthService - the frontier auth service
     * @param FrontierCApiService $frontierCApiService - the frontier CAPI service
     * @param EdsmApiService $edsmApiService - the EDSM API service
     */
    public function __construct(
        FrontierAuthService $frontierAuthService,
        FrontierCApiService $frontierCApiService,
        EdsmApiService $edsmApiService
    ) {
        $this->frontierAuthService = $frontierAuthService;
        $this->frontierCApiService = $frontierCApiService;
        $this->edsmApiService = $edsmApiService;

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
        // Authorize the user and decode the token to get their Frontier profile
        $auth = $this->frontierAuthService->authorize($request);
        $frontier = $this->frontierAuthService->decode($auth->access_token);

        // Confirm the user, creates a new user if they do not exist
        $user = $this->confirmUser($frontier, $auth->access_token);

        // Confirm the user's commander, creates a new commander if they do not exist
        $this->confirmCommander($user);

        // Create a sanctum access token, we're using BFF proxy to handle the auth
        // between the front-end, the back-end, and Frontier.
        $token = $user->createToken('frontier')->plainTextToken;

        return redirect()->to('http://localhost:4201/api/auth/callback')->cookie(
            'cmdr_token', $token, 60, '/', null, true, true
        );
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
     *  Confirm the user.
     * 
     * @param mixed $frontierProfile - the user details from the decoded token
     * @param string $accessToken - the access token
     * @return User - the user model
     */
    private function confirmUser(mixed $frontierProfile, string $accessToken): User
    {
        $email = $frontierProfile->usr->customer_id  . '@versyx.net';
        $user = User::whereEmail($email)->first();

        if (! $user) {
            // If the user does not exist, create a new user
            $user = User::create([
                'name' => $frontierProfile->usr->customer_id,
                'email' => $email,
                'password' => bcrypt(Str::random(32))
            ]);

            // Create a new associated Frontier user
            $user->frontierUser()->create([
                'frontier_id' => $frontierProfile->usr->customer_id,
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
                'frontier_id' => $frontierProfile->usr->customer_id,
                'access_token' => $accessToken
            ]);
        }

        Redis::set("user_{$user->id}_frontier_token", $accessToken, 'EX', 3600*3);

        return $user;
    }

    /**
     * Confirm the user's commander profile.
     * 
     * @param User $user - the user model
     * @return void
     */
    private function confirmCommander(User $user)
    {
        // Get the commander profile
        $profile = $this->frontierCApiService->getCommanderProfile($user);
        if (!property_isset($profile, 'commander')) {
            throw new Exception('Commander profile not found.');
        }

        // Update or create the user's commander profile
        $commander = $profile->commander;
        $user->commander()->updateOrCreate([
            'cmdr_name' => $commander->name
        ], [
            'cmdr_name' => $commander->name,
            'credits' => $commander->credits,
            'debt' => $commander->debt,
            'alive' => $commander->alive,
            'docked' => $commander->docked,
            'onfoot' => $commander->onfoot,
            'rank' => json_encode($commander->rank)
        ]);

        // Check the commander's last system and add to our records if it does not exist
        if (property_isset($profile, 'lastSystem')) {
            $lastSystem = $profile->lastSystem;
            $system = System::whereId64($lastSystem->id)
                ->whereName($lastSystem->name)
                ->first();

            if (!$system) {
                $system = $this->edsmApiService->updateSystemData($lastSystem->name);
            }

            // Update the commander's last system
            $user->commander()->update([
                'last_system_id64' => $system->id64
            ]);
        }
    }
}