<?php

namespace App\Libraries;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class EliteAPIManager extends BaseAPIManager
{
    protected array $config;

    protected string $category;
    
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function setCategory(string $category)
    {
        $this->category = $category;

        return $this;
    }

    public function get(string $key, array $params = null)
    {
        $url = $this->config['base_url']
            . $this->resolveUri($this->category, $key)
            . $this->buildQueryString($params);

        $response = Http::withHeaders($this->headers)->get($url);

        return $this->getContents($response, true);
    }
    
    public function resolveUri(string $section, string $key, string $subKey = null)
    {
        $section = $this->config[$section];
        if ($section && $section[$key]) {

            if (is_array($section[$key]) && $subKey && $section[$key][$subKey]) {
                return $section[$key][$subKey];
            }

            return $section[$key];
        }
    }

    private function buildQueryString(array $params = null)
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