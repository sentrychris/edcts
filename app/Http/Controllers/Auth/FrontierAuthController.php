<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            $accessToken = $this->frontierAuthService
                ->issueAccessToken($request);

            $expiresOn = Carbon::parse(Carbon::now())
                ->addSeconds($accessToken->expires_in)
                ->toIso8601String();

            return response()->json([
                'access_token' => $accessToken->access_token,
                'expires_on' => $expiresOn
            ]);
        } catch (Exception $e) {
            Log::error('Frontier Auth Error: ' . $e->getMessage());

            abort(401, 'Unauthorized');
        }
    }
}