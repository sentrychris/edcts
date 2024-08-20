<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * @return ApiService
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
     * @return ApiService
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
     * Make a GET request to a third-party API
     * 
     * @param string $key
     * @param ?string $subkey
     * @param ?array $params
     * 
     * @return mixed
     */
    public function get(string $key, ?string $subkey = null, ?array $params = null): mixed
    {
        $url = $this->config['base_url']
            . $this->resolveUri($this->category, $key, $subkey)
            . $this->buildQueryString($params);

        $response = Http::withHeaders($this->headers)->get($url);
        $status = $response->getStatusCode();

        if ($status !== 200) {
            Log::channel('thirdparty')->error('API call failed', [
                'status' => $status,
                'reason' => $response->getReasonPhrase(),
                'url' => $url,
                'config' => $this->config,
            ]);
        }


        return $this->getContents($response, true);
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

    /**
     * Resolve uri from config
     * 
     * @param string $section
     * @param string $key
     * @param ?string $subKey
     * 
     * @return string|false
     */
    protected function resolveUri(
        string $section,
        string $key,
        string $subKey = null
    ): string|false {
        $section = $this->config[$section];
        if ($section && $section[$key]) {

            if (is_array($section[$key]) && $subKey && $section[$key][$subKey]) {

                return $section[$key][$subKey];
            }

            return $section[$key];
        }

        return false;
    }

    /**
     * Build query string for request
     * 
     * @param ?array $params
     * 
     * @return string
     */
    protected function buildQueryString(?array $params = null): string
    {
        if (!$params) {
            return '';
        }

        $i = 0;
        $template = '';
        foreach ($params as $k => $v) {
            $template .= ($i === 0 ? '?' : '&') . $k . '=' . $v;
            ++$i;
        }

        return $template;
    }
}