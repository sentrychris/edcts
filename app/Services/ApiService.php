<?php

namespace App\Services;

abstract class ApiService
{
    /**
     * @var array $config
     */
    protected array $config;

    /**
     * @var string $category;
     */
    protected string $category;

    /**
     * @var array $headers
     */
    protected $headers = [];

    /**
     * Set API config
     * 
     * @param array $config
     * 
     * @return EdsmService
     */
    public function setConfig(array $config): ApiService
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set API calling category
     * 
     * @param string $category
     * 
     * @return EdsmService
     */
    public function setCategory(string $category): ApiService
    {
        $this->category = $category;

        return $this;
    }

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

    /**
     * Get update time according to various 3rd party formats.
     */
    public function formatSystemUpdateTime($system): mixed
    {
        // Spansh dumps
        if (property_isset($system, 'updateTime')
            && is_string($system->updateTime)
            && $system->updateTime
        ) {
            if (str_contains($system->updateTime, '+')) {
                return substr($system->updateTime, 0, strpos($system->updateTime, '+'));
            }

            return $system->updateTime;
        }

        // EDSM dumps
        if (property_isset($system, 'updateTime')
            && is_object($system->updateTime)
            && $system->updateTime->information
        ) {
            return $system->updateTime->information;
        }

        return now();
    }
}