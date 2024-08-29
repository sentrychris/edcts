<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Models\FrontierUser;
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

    public function __construct(FrontierAuthService $frontierAuthService)
    {
        $this->frontierAuthService = $frontierAuthService;
    }

    public function login()
    {
        return response()->json([
            'data' => $this->frontierAuthService
                ->getAuthorizationServerInformation()
        ]);
    }

    public function callback(Request $request)
    {
        try {
            $auth = $this->frontierAuthService->authorize($request);
            $profile = $this->frontierAuthService->decode($auth->access_token);

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
            } else {
                $user = $user->frontierUser()->first();
            }

            return response()->json([
                'access_token' => $auth->access_token,
                'expires_on' => Carbon::parse(Carbon::now())
                    ->addSeconds($auth->expires_in)
                    ->toIso8601String(),
                'profile' => $profile
            ]);
        } catch (Exception $e) {
            Log::error('Frontier Auth Error: ' . $e->getMessage());

            abort(401, 'Unauthorized');
        }
    }
}