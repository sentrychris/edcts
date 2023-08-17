<?php

namespace App\Traits;

use App\Models\Commander;
use App\Models\FleetCarrier;
use App\Models\FleetSchedule;
use App\Models\System;
use App\Models\SystemBody;
use App\Http\Resources\SystemResource;
use Illuminate\Support\Facades\Cache;

trait UsesStatistics
{
    public function getAllStatistics(string $key, array $options)
    {
        if (array_key_exists('resetCache', $options) && $options['resetCache']) {
            Cache::forget($key);
        }

        $ttl = array_key_exists('ttl', $options)
            ? (int)$options['ttl']
            : 3600;

        return Cache::remember($key, $ttl, function () {
            $latestSystem = System::with(['information'])
                ->orderBy('id', 'desc')
                ->first();
            
            if ($latestSystem instanceof System) {
                $latestSystem
                    ->checkAPIForSystemInformation()
                    ->checkAPIForSystemBodies();
            }
            
            $data = [
                'cartographical' => [
                    'systems' => System::count(),
                    'bodies' => SystemBody::count(),
                    'stars' => SystemBody::whereType('Star')->count(),
                    'orbiting' => SystemBody::whereType('planet')->count(),
                    'latest_system' => new SystemResource($latestSystem->load(['information', 'bodies'])),
                ],
                
                'carriers' => FleetCarrier::count(),
                
                'commanders' => Commander::count(),
                
                'journeys' => [
                    'total' => FleetSchedule::count(),
                    'boarding' => FleetSchedule::whereIsBoarding(1)->count(),
                    'cancelled' => FleetSchedule::whereIsCancelled(1)->count(),
                    'leaving_in' => [
                        'two_days' => FleetSchedule::leavingInNextNDays(2),
                        'one_week' => FleetSchedule::leavingInNextNDays(7),
                        'one_month' => FleetSchedule::leavingInNextNDays(31),
                        'six_months' => FleetSchedule::leavingInNextNDays(31*6),
                    ]
                ]
            ];
                    
            return $data;
        });
    }
}