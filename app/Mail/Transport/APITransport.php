<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class APITransport extends AbstractTransport
{
    /**
     * @var string
     */
    private readonly string $url;

    /**
     * @var string
     */
    private readonly string $username;

    /**
     * @var string
     */
    private readonly string $password;

    /**
     * @var array
     */
    protected APIPayload $payload;

    /**
     * @param string $url
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $url,
        string $username,
        string $password,
    ) {
        parent::__construct();
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    protected function token(): mixed
    {
        $response = Http::asForm()->post($this->url . 'auth/token', [
            'email' => $this->username,
            'password' => $this->password,
        ]);

        return $response->json('token');
    }

    /**
     * @param SentMessage $message
     *
     * @return APITransport
     */
    public function setPayload(APIPayload $payload): APITransport
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Get the "To" payload field for the API request.
     *
     * @param Envelope $envelope
     *
     * @return string
     */
    protected function getToAddress(Envelope $envelope): string
    {
        return $envelope->getRecipients()[0]->getAddress();
    }

    /**
     * Get the "From" payload field for the API request.
     *
     * @param Envelope $envelope
     *
     * @return string
     */
    protected function getFromAddress(Envelope $envelope): string
    {
        return $envelope->getSender()->getAddress();
    }

    /**
     * @param SentMessage $message
     *
     * @return void
     */
    protected function doSend(SentMessage $message): void
    {
        $token = $this->token();
    
        $response = Http::withToken($token)
            ->asJson()
            ->post($this->url . $this->payload->endpoint, $this->payload->data);


        $status = $response->status();

        if ($status !== 200) {
            if ($status === 401) {
                Log::error('Unable to authenticate against mailer API!');
            }

            Log::error('Could not send mail via mailer API! [HTTP '.$status.']. Payload:');
            Log::error(var_export($this->payload, true));

            dd($response);
        }
    }

    public function __toString(): string
    {
        return 'api';
    }
}