<?php

namespace App\Http\Controllers\Auth;

use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\Frontier\FrontierCApiService;
use Illuminate\Http\Request;

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
     * Create a new controller instance.
     * 
     * @param FrontierAuthService $frontierAuthService - the frontier auth service
     * @param FrontierCApiService $frontierCApiService - the frontier CAPI service
     */
    public function __construct(
        FrontierAuthService $frontierAuthService,
        FrontierCApiService $frontierCApiService,
    ) {
        $this->frontierAuthService = $frontierAuthService;
        $this->frontierCApiService = $frontierCApiService;

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

        // Confirm the user, creates a new user record in our db if they do not exist
        $user = $this->frontierAuthService->confirmUser($frontier, $auth->access_token);

        // Confirm the CMDR, creates a new CMDR record in our db if they do not exist
        $this->frontierCApiService->confirmCommander($user);

        // Create a sanctum access token, we're using BFF proxy to handle the auth
        // between the front-end, the back-end, and Frontier.
        $token = $user->createToken('frontier')->plainTextToken;

        return redirect()->to(config('app.frontend_url').'/api/auth/callback')->cookie(
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
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()->load('commander')),
                'token' => $request->cookie('cmdr_token')
            ]
        ]);
    }
}