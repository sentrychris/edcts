<?php

namespace App\Libraries;

abstract class BaseAPIManager
{
    protected $headers = [];

    public function setHeaders(array $headers): BaseAPIManager
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function setAPIHeader(string $header, string $value): BaseAPIManager
    {
        $this->headers[$header] = $value;
        
        return $this;
    }
    
    public function getContents($response, bool $decode = true)
    {
        $content = $response->getBody()->getContents();
        
        return $decode ? json_decode($content) : $content;
    }
}