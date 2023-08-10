<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;

class EliteAPIManager extends BaseAPIManager
{
    /**
     * @var array $config
     */
    protected array $config;

    /**
     * var string $category;
     */
    protected string $category;
    
    /**
     * Set API config
     * 
     * @param array $config
     * 
     * @return EliteAPIManager
     */
    public function setConfig(array $config): EliteAPIManager
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set API calling category
     * 
     * @param string $category
     * 
     * @return EliteAPIManager
     */
    public function setCategory(string $category): EliteAPIManager
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Make call to Elite API
     * 
     * @param string $key
     * @param ?array $params
     * 
     * @return mixed
     */
    public function get(string $key, ?array $params = null, ?string $subKey = null): mixed
    {
        $url = $this->config['base_url']
            . $this->resolveUri($this->category, $key, $subKey)
            . $this->buildQueryString($params);

        $response = Http::withHeaders($this->headers)->get($url);

        return $this->getContents($response, true);
    }
    
    /**
     * Resolve uri from config
     * 
     * @param string $section
     * @param string $key
     * @param ?string $subKey
     * 
     * @return string
     */
    public function resolveUri(string $section, string $key, string $subKey = null): string
    {
        $section = $this->config[$section];
        if ($section && $section[$key]) {

            if (is_array($section[$key]) && $subKey && $section[$key][$subKey]) {

                return $section[$key][$subKey];
            }

            return $section[$key];
        }
    }

    /**
     * Convert elite API response
     * 
     * @param mixed $obj,
     * @param mixed &$arr
     * 
     * @return mixed
     */
    public function convertResponse($obj, &$arr): mixed
    {
        if (!is_object($obj) && !is_array($obj)) {
            $arr = $obj;
            return $arr;
        }
        
        foreach ($obj as $key => $value){
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));

            if (!empty($value)) {
                $arr[$key] = array();
                $this->convertResponse($value, $arr[$key]);
            } else {
                $arr[$key] = $value;
            }
        }
        
        return $arr;
    }

    /**
     * Build query string for request
     * 
     * @param ?array $params
     * 
     * @return string
     */
    private function buildQueryString(?array $params = null): string
    {
        if (!$params) {
            return '';
        }

        $i = 0;
        $template = '';
        foreach ($params as $k => $v) {
            $template .= ($i === 0 ? '?' : '&') . $k . '=' . rawurlencode($v);
            ++$i;
        }

        return $template;
    }
}