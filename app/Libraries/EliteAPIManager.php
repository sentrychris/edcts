<?php

namespace App\Libraries;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class EliteAPIManager extends BaseAPIManager
{
    protected array $config;
    
    public function setConfig(array $config) {
        $this->config = $config;
    }
    
    public function resolveUri(string $section, string $key, string $subKey = null) {
        $section = $this->config[$section];
        if ($section && $section[$key]) {

            if (is_array($section[$key]) && $subKey && $section[$key][$subKey]) {
                return $section[$key][$subKey];
            }

            return $section[$key];
        }
    }
}