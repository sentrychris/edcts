<?php

namespace App\Http\Api;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\System;

/**
 * Frontier auth client.
 */
class FrontierApiManager
{
    /** @var Client $client */
    protected Client $client;

    /** @var string $base */
    protected string $url;

    /** @var string $code */
    protected string $code;

    /** @var mixed $clientId */
    protected $clientId;

    /** @var mixed $clientKey */
    protected $clientKey;

    /**
     * ESIClient constructor.
     *
     * @param string|null $server
     */
    public function __construct()
    {
        $this->clientId = config('elite.frontier.auth.client_id');
        $this->clientKey = config('elite.frontier.auth.client_key');
        $this->client = new Client([
            'base_uri' => $this->url ?? config('elite.frontier.auth.url') 
        ]);
    }

    /**
     * Set the base URL for the client
     * 
     * @param string $url
     * @return void
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Fetch data from endpoints that require authentication.
     *
     * @param string $endpoint
     * @param string $method
     * @return mixed
     */
    public function fetch(
        string $endpoint = '',
        string $method = 'GET',
        bool $isAssociated = false
    ): mixed {
        try {

            $options = [];
            if (session('character.access_token')) {
                $options['headers'] = [
                    'Authorization' => 'Bearer ' . session('character.access_token')
                ];
            }

            if (! str_starts_with($endpoint, '/')) {
                $endpoint = "/{$endpoint}";
            }

            $response = $this->client->request($method, $this->url . $endpoint, $options);
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            if ($status === 403 || $status === 401) {
                throw new Exception('Unauthorized, redirect to SSO login');
            }

            return false;
        }

        if ($response && $response->getStatusCode() === 200)
        {
            return json_decode($response->getBody()->getContents(), $isAssociated);
        }

        return false;
    }

    /**
     * Redirect to login to obtain an authorization token.
     *
     * return mixed
     * @param array $scopes
     * @return string
     */
    public function getAuthorizationServerURL(array $scopes = []): string
    {
        $url = config('elite.frontier.auth.url') . '/v2/oauth/authorize?response_type=code';
        $url .= '&redirect_uri=' . urlencode(route('esi.sso.callback'));
        $url .= '&client_id=' . $this->clientId;
        $url .= !empty($scopes) ? $this->attachAuthorizationScopes($scopes) : '';
        $url .= '&state=' . Str::random();

        return $url;
    }

    /**
     * Callback method to receive the authorization code from Frontier Auth
     *
     * @param Request $request
     * @return mixed
     *
     * @throws ClientException
     */
    public function issueAccessToken(Request $request): mixed
    {
        $this->code = $request->get('code');

        $response = $this->client->request('POST', '/v2/oauth/token', [
            'auth' => [
                $this->clientId,
                $this->clientKey
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $this->code,
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Refresh access token.
     *
     * @throws ClientException
     * @return void
     */
    public function refreshAccessToken(): void
    {
        $response = $this->client->request('POST', '/v2/oauth/token', [
            'auth' => [
                $this->clientId,
                $this->clientKey
            ],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => session('character.refresh_token')
            ]
        ]);

        $auth = json_decode($response->getBody()->getContents());
        $expires_on = Carbon::parse(Carbon::now())->addSeconds($auth->expires_in)->toIso8601String();

        Session::put('character.access_token', $auth->access_token);
        Session::put('character.expires_on', $expires_on);
        Session::put('character.refresh_token', $auth->refresh_token);
        Session::save();
    }

    /**
     * Verify login and return character information.
     *
     * @return bool|mixed
     * @throws ClientException
     */
    public function verifyAuthorization(): mixed
    {
        if (!session('character.access_token')) {
            return false;
        }

        $response = $this->client->request('GET', '/oauth/verify', [
            'headers' => [
                'Authorization' => 'Bearer ' . session('character.access_token')
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Generate query string for ESI scopes.
     *
     * @param array $scopes
     * @return string
     */
    private function attachAuthorizationScopes(array $scopes): string
    {
        $query = '&scope=';
        $count = count($scopes);
        $delim = '%20';
        foreach ($scopes as $name => $key) {
            if (--$count <= 0) $delim = null;
            $query .= $key . $delim;
        }

        return $query;
    }

    /**
     * Obtain information required for route.
     *
     * @param int $origin
     * @param int $destination
     * @return array
     */
    public function fetchRoute(int $origin, int $destination): array
    {
        $route = [];
        $route['origin'] = System::whereSystemId($origin)->first();
        $route['destination'] = System::whereSystemId($destination)->first();

        $systems = $this->fetch('/route/'.$origin.'/'.$destination);

        foreach ($systems as $id) {
            $system = System::whereSystemId($id)
                ->with('constellation.region')
                ->first();
                // dd($system);
            $route['route'][] = $system;
        }

        $route['jumps'] = count($route['route']);

        return $route;
    }
}