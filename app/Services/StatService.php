<?php

namespace App\Services;

use App\Models\Commander;
use App\Models\System;
use App\Models\SystemBody;
use Illuminate\Support\Facades\Cache;

class StatService
{
    public function fetch(string $key, array $options)
    {
        if (array_key_exists('flushCache', $options) && $options['flushCache']) {
            Cache::forget($key);
        }

        $ttl = array_key_exists('ttl', $options)
            ? (int)$options['ttl']
            : 3600;

        return Cache::remember($key, $ttl, function ()
        {            
            $data = [
                'cartographical' => [
                    'systems'  => System::count(),
                    'bodies'   => SystemBody::count(),
                    'stars'    => SystemBody::whereType('Star')->count(),
                    'orbiting' => SystemBody::whereType('planet')->count()
                ],
                'commanders' => Commander::count()
            ];
                    
            return $data;
        });
    }
}