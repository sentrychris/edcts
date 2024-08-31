<?php

namespace App\Services\Frontier;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Frontier CAPI client.
 */
class FrontierCApiService extends FrontierAuthService
{

    /**
     * Get the user's profile information.
     * 
     * @param string $token - the access token
     * @return mixed - the user profile
     */
    public function profile(string $token)
    {
        $response = $this->client->request('GET', '/profile', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
}