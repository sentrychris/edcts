<?php

namespace App\Traits;

use App\Models\Commander;
use App\Models\FleetCarrier;
use App\Models\FleetCarrierJourneySchedule;
use App\Models\System;
use App\Models\SystemBody;
use App\Http\Resources\SystemResource;
use App\Services\EdsmApiService;
use Illuminate\Support\Facades\Cache;

trait UseStatistics
{
    public function getAllStatistics(string $key, array $options)
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
                    'systems' => System::count(),
                    'bodies' => SystemBody::count(),
                    'stars' => SystemBody::whereType('Star')->count(),
                    'orbiting' => SystemBody::whereType('planet')->count()
                ],
                
                'carriers' => FleetCarrier::count(),
                
                'commanders' => Commander::count(),
                
                'journeys' => [
                    'total' => FleetCarrierJourneySchedule::count(),
                    'boarding' => FleetCarrierJourneySchedule::whereIsBoarding(1)->count(),
                    'cancelled' => FleetCarrierJourneySchedule::whereIsCancelled(1)->count(),
                    'leaving_in' => [
                        'two_days' => FleetCarrierJourneySchedule::leavingInNextNumberOfDays(2),
                        'one_week' => FleetCarrierJourneySchedule::leavingInNextNumberOfDays(7),
                        'one_month' => FleetCarrierJourneySchedule::leavingInNextNumberOfDays(31),
                        'six_months' => FleetCarrierJourneySchedule::leavingInNextNumberOfDays(31*6),
                    ]
                ]
            ];
                    
            return $data;
        });
    }
}