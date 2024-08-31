<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Services\Frontier\FrontierAuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Frontier\FrontierCApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\PersonalAccessToken;

class FrontierCApiController extends Controller
{
    /**
     * The Frontier API manager.
     * 
     * @var FrontierCApiService
     */
    private $frontierCApiService;

    /**
     * Create a new controller instance.
     * 
     * @param FrontierCApiService $frontierCApiService - the frontier auth service
     */
    public function __construct(FrontierCApiService $frontierCApiService)
    {
        $this->frontierCApiService = $frontierCApiService;

        $this->middleware('frontier.auth');
    }

    /**
     * Get commander profile.
     * 
     * @return
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        $profile = $this->frontierCApiService->getCommanderProfile($user);
        if (!property_isset($profile, 'commander')) {
            throw new Exception('Commander profile not found.');
        }

        $commander = $profile->commander;

        // Check if the user has a commander profile
        $user->commander()->updateOrCreate([
            'cmdr_name' => $commander->name,
        ]);

        return response()->json([
            'data' => $profile
        ]);
    }
}