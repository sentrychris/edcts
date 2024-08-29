<?php

namespace App\Http\Controllers\Auth;

use App\Services\Frontier\FrontierApiManager;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontierAuthController extends Controller
{
    /**
     * The Frontier API manager.
     * 
     * @var FrontierApiManager
     */
    private $frontierApiManager;

    public function __construct(FrontierApiManager $frontierApiManager)
    {
        $this->frontierApiManager = $frontierApiManager;
    }

    public function login()
    {
        $authorizationUrl = $this->frontierApiManager->getAuthorizationServerURL();
        dd($authorizationUrl);
        return redirect($authorizationUrl);
    }

    public function callback(Request $request)
    {
        try {
            $accessToken = $this->frontierApiManager
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