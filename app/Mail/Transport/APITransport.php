<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
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
        $response = Http::asForm()->post($this->url . '/auth/login', [
            'username' => $this->username,
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
        $response = Http::withToken('secret123', '')
            ->asJson()
            ->post('http://host.docker.internal:8000/api/' . $this->payload->endpoint, $this->payload->data);

        if ($response->status() !== 200) {
            dd($response);
        }
    }

    public function __toString(): string
    {
        return 'api';
    }
}