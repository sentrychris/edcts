<?php

namespace App\Http\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

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
     * Redirect to login to obtain an authorization token.
     *
     * return mixed
     * @param array $scopes
     * @return string
     */
    public function getAuthorizationServerURL(): string
    {
        $url = config('elite.frontier.auth.url') . '/v2/oauth/authorize?response_type=code';
        $url .= '&redirect_uri=' . urlencode(route('esi.sso.callback'));
        $url .= '&client_id=' . $this->clientId;
        $url .= $this->attachAuthorizationScopes(config('elite.frontier.auth.scopes'));
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
        // Get the authorization code from the callback request
        $this->code = $request->get('code');

        // Use it to obtain a valid access token
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

        // TODO check somewhere on FrontierUser, if the user is not in the database, create
        //      the user record and the frontier user record.
        

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
}