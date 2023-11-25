<?php

namespace App\Mail\Transport;

class APIPayload
{
    /**
    * @var array
    */
    public array $data;
    
    /**
    * @var string
    */
    public string $endpoint;
    
    /**
    * API payload constructor
    *
    * @param string $endpoint
    * @param array $data
    */
    public function __construct(string $endpoint, array $data)
    {
        $this->data = $data;
        $this->endpoint = $endpoint;
    }
}