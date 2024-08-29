<?php

namespace App\Services\Frontier;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Frontier auth client.
 */
class FrontierAuthService
{
    /** @var Client $client */
    protected Client $client;

    /** @var string $base */
    protected string $url;

    /** @var string $code */
    protected string $code;

    /** @var mixed $clientKey */
    protected $clientKey;

    /**
     * APIManager constructor.
     *
     * @param string|null $server
     */
    public function __construct()
    {
        $this->clientKey = config('elite.frontier.auth.client_key');
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'EDCTS-carrier-transport-services-v1.0.0'
            ],
            'base_uri' => $this->url ?? config('elite.frontier.auth.url') 
        ]);
    }

    /**
     * Set the base URL for the client.
     * 
     * @param string $url - the base URL
     * @return void
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Generate the authorization details for the Frontier auth server.
     *
     * @return array - the authorization details
     */
    public function getAuthorizationServerInformation(): array
    {
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        // Test for now
        Cache::put('code_verifier', $codeVerifier, 300);

        $url = config('elite.frontier.auth.url') . '/auth?audience=frontier,steam,epic';
        $url .= $this->attachAuthorizationScopes(config('elite.frontier.auth.scopes'));
        $url .= '&response_type=code';
        $url .= '&client_id=' . config('elite.frontier.auth.client_id');
        $url .= '&code_challenge=' . $codeChallenge;
        $url .= '&code_challenge_method=S256';
        $url .= '&state=' . Str::random(32);
        $url .= '&redirect_uri=' . route('frontier.auth.callback');

        return [
            'authorization_url' => $url,
            'code_verifier' => $codeVerifier
        ];
    }

    /**
     * Callback method for Frontier auth.
     * 
     * This method is called when the Frontier auth server redirects back to the application,
     * it retrieves the authorization code and exchanges it for an access token.
     *
     * @param Request $request - the request object
     * @return mixed - the response
     */
    public function authorize(Request $request): mixed
    {
        // Get the authorization code from the callback request
        $code = $request->get('code');
        $codeVerifier = $request->get('code_verifier');
        $redirectUri = route('frontier.auth.callback');

        // Retrieve the code verifier from the session
        $codeVerifier = Cache::get('code_verifier');

        
        // Use it to obtain a valid access token
        $response = $this->client->request('POST', '/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('elite.frontier.auth.client_id'),
                'code_verifier' => $codeVerifier,
                'code' => $code,
                'redirect_uri' => $redirectUri
            ]
        ]);
        

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Decode the token to retrieve the user profile.
     * 
     * @param string $token - the access token
     * @return mixed - the user profile
     */
    public function decode(string $token)
    {
        $response = $this->client->request('GET', '/decode', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Generate query string for oauth scopes.
     *
     * @param array $scopes - the scopes to attach
     * @return string - the query string
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
     * Generate a secure random string for the code verifier.
     *
     * @return string - the code verifier
     */
    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generate the code challenge from the code verifier.
     *
     * @param string $codeVerifier - the code verifier
     * @return string - the code challenge
     */
    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}