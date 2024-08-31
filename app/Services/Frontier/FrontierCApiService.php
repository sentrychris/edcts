<?php

namespace App\Services\Frontier;

use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
            $frontierToken = Redis::get("user_{$user->id}_token");

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
}