<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Frontier\FrontierCApiService;
use Illuminate\Http\Request;

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
    }

    /**
     * Get commander profile.
     * 
     * @return
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        $commanderProfile = $this->frontierCApiService->confirmCommander($user);

        return response()->json([
            'data' => $commanderProfile
        ]);
    }
}