<?php

namespace App\Services;

use App\Models\System;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdsmApiService extends ApiService
{
    /**
     * Search EDSM for system data by system name and update records if found.
     * 
     * @param string $name - the system name
     * @return System|false the created system record or false
     */
    public function updateSystemData(string $name): System|false
    {
        $response = $this->setConfig(config('elite.edsm'))
            ->setCategory('systems')
            ->get(key: 'system', params: [
                'systemName' => $name,
                'showCoordinates' => true,
                'showInformation' => true,
                'showId' => true
            ]);

        if ($response) {
            $system = System::updateOrCreate(['id64' => $response->id64], [
                'id64' => $response->id64,
                'name' => $response->name,
                'coords' => json_encode($response->coords),
                'updated_at' => now()
            ]);
        }

        if (! $system) {
            return false;
        }

        return $system;
    }

    /**
     * Make call to Elite API
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