<?php

namespace App\Traits;

use App\Models\Commander;
use App\Models\FleetCarrier;
use App\Models\FleetSchedule;
use App\Models\System;
use App\Models\SystemBody;
use App\Http\Resources\SystemResource;
use App\Services\EdsmApiService;
use Illuminate\Support\Facades\Cache;

trait UseStatistics
{
    public function getAllStatistics(string $key, array $options)
    {
        if (array_key_exists('resetCache', $options) && $options['resetCache']) {
            Cache::forget($key);
        }

        $ttl = array_key_exists('ttl', $options)
            ? (int)$options['ttl']
            : 3600;

        return Cache::remember($key, $ttl, function ()
        {
            $api = app(EdsmApiService::class);

            $lastAddedSystem = System::with(['information'])
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastAddedSystem instanceof System) {
                $api->updateSystemBodiesData($lastAddedSystem);
                $api->updateSystemInformationData($lastAddedSystem);
            }
            
            $data = [
                'cartographical' => [
                    'systems' => System::count(),
                    'bodies' => SystemBody::count(),
                    'stars' => SystemBody::whereType('Star')->count(),
                    'orbiting' => SystemBody::whereType('planet')->count(),
                    'latest_system' => new SystemResource($lastAddedSystem->load(['information', 'bodies'])),
                ],
                
                'carriers' => FleetCarrier::count(),
                
                'commanders' => Commander::count(),
                
                'journeys' => [
                    'total' => FleetSchedule::count(),
                    'boarding' => FleetSchedule::whereIsBoarding(1)->count(),
                    'cancelled' => FleetSchedule::whereIsCancelled(1)->count(),
                    'leaving_in' => [
                        'two_days' => FleetSchedule::leavingInNextNumberOfDays(2),
                        'one_week' => FleetSchedule::leavingInNextNumberOfDays(7),
                        'one_month' => FleetSchedule::leavingInNextNumberOfDays(31),
                        'six_months' => FleetSchedule::leavingInNextNumberOfDays(31*6),
                    ]
                ]
            ];
                    
            return $data;
        });
    }
}