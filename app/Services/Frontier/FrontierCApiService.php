<?php

namespace App\Services\Frontier;

use App\Models\System;
use App\Models\User;
use App\Services\EdsmApiService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Frontier CAPI client.
 */
class FrontierCApiService
{
    /** @var Client $client */
    protected Client $client;

    /**
     * APIManager constructor.
     *
     * @param string|null $server
     */
    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'EDCTS-carrier-transport-services-v1.0.0'
            ],
            'base_uri' => config('elite.frontier.capi.url') 
        ]);
    }

    public function getHeaders(string $token) {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ]
        ];
    }

    /**
     * Get the commander's profile information.
     * 
     * @param string $token - the access token
     * @return mixed - the user profile
     */
    public function getCommanderProfile(User $user)
    {
        try {    
            $frontierToken = Redis::get("user_{$user->id}_frontier_token");;

            $response = $this->client->request('GET', '/profile', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $frontierToken,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * Confirm the user's commander profile.
     * 
     * @param User $user - the user model
     * @return mixed
     */
    public function confirmCommander(User $user): mixed
    {
        // Get the commander profile
        $profile = $this->getCommanderProfile($user);
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
                // Resolve EDSM API service from the container to update the system data
                $system = app(EdsmApiService::class)->updateSystemData($lastSystem->name);
            }

            // Update the commander's last system
            $user->commander()->update([
                'last_system_id64' => $system->id64
            ]);
        }

        return $profile;
    }
}