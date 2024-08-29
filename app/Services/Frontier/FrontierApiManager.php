<?php

namespace App\Services\Frontier;

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
     * @return array
     */
    public function getAuthorizationServerInformation(): array
    {
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        $url = config('elite.frontier.auth.url') . '/auth?audience=frontier';
        $url .= $this->attachAuthorizationScopes(config('elite.frontier.auth.scopes'));
        $url .= '&response_type=code';
        $url .= '&client_id=' . $this->clientId;
        $url .= '&code_challenge=' . $codeChallenge;
        $url .= '&code_challenge_method=S256';
        $url .= '&state=' . Str::random(32);
        $url .= '&redirect_uri=' . urlencode(route('frontier.auth.callback'));

        return [
            'authorization_url' => $url,
            'code_verifier' => $codeVerifier
        ];
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
        $code = $request->get('code');
        $codeVerifier = $request->get('code_verifier');
        $redirectUri = urlencode(route('frontier.auth.callback'));

        // Retrieve the code verifier from the session
        $codeVerifier = $request->get('code_verifier');

        // Use it to obtain a valid access token
        $response = $this->client->request('POST', '/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => "redirect_uri={$redirectUri}&code={$code}&grant_type=authorization_code&code_verifier={$codeVerifier}client_id={$this->clientId}"
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

    /**
     * Generate a secure random string for the code verifier.
     *
     * @return string
     */
    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generate the code challenge from the code verifier.
     *
     * @param string $codeVerifier
     * @return string
     */
    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}