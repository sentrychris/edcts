<?php

namespace App\Libraries;

abstract class BaseAPIManager
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
     * @return BaseAPIManager
     */
    public function setHeaders(array $headers): BaseAPIManager
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
     * @return BaseAPIManager
     */
    public function setAPIHeader(string $header, string $value): BaseAPIManager
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