<?php

namespace App\Services;

abstract class ApiService
{
    /**
     * @var array $headers
     */
    protected $headers = [];

    /**
     * Set API headers
     * 
     * @param array $headers
     * 
     * @return ApiService
     */
    public function setHeaders(array $headers): ApiService
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Set API Header
     * 
     * @param string $header
     * @param string $value
     * 
     * @return ApiService
     */
    public function setAPIHeader(string $header, string $value): ApiService
    {
        $this->headers[$header] = $value;
        
        return $this;
    }
    
    /**
     * Get response content.
     * 
     * @param $response
     * @param bool $decode
     * 
     * @return mixed
     */
    public function getContents($response, bool $decode = true): mixed
    {
        $content = $response->getBody()->getContents();
        
        return $decode ? json_decode($content) : $content;
    }
}