<?php

namespace App\Http\Controllers;

use App\Models\Commander;
use App\Models\FleetCarrier;
use App\Models\FleetSchedule;
use App\Models\System;
use App\Models\SystemBody;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    /** @var string */
    private string $cacheKey = 'edcts:statistics';
    
    /**
     * Get statistics.
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $ttl = (int)$request->get('ttl', 60);
        
        if ($request->exists('resetCache')) {
            Cache::forget($this->cacheKey);
        }
        
        $statitics = Cache::remember($this->cacheKey, $ttl, function () {
            
            $data = [
                'cartographical' => [
                    'systems' => System::count(),
                    'bodies' => SystemBody::count(),
                ],
                
                'carriers' => FleetCarrier::count(),
                
                'commanders' => Commander::count(),

                'journeys' => [
                    'total' => FleetSchedule::count(),
                    'boarding' => FleetSchedule::whereIsBoarding(1)->count(),
                    'cancelled' => FleetSchedule::whereIsCancelled(1)->count(),
                    'leaving_in' => [
                        'two_days' => $this->journeysLeavingInNextNDays(2),
                        'one_week' => $this->journeysLeavingInNextNDays(7),
                        'one_month' => $this->journeysLeavingInNextNDays(31),
                        'six_months' => $this->journeysLeavingInNextNDays(31*6),
                    ]
                ]
            ];
            
            $data['cartographical']['stars'] = $this->systemBodiesByType('star');
            $data['cartographical']['orbiting'] = $this->systemBodiesByType('planet');

            return $data;
        });
        
        return Response(['data' => $statitics]);
    }

    private function journeysLeavingInNextNDays(int $n)
    {
        $time = now()->addDays($n)->toDateTimeString();
        $count = FleetSchedule::whereIsCancelled(0)
            ->where('departs_at', '>', now()->toDateString())
            ->where('departs_at', '<=', $time)
            ->count();
        
        return $count;
    }
    
    private function systemsUpdatedLastNDays(int $n)
    {
        $time = now()->subDays($n)->toDateTimeString();
        $count = System::where('updated_at', '<=', $time)
        ->count();
        
        return $count; 
    }
    
    private function systemBodiesByType(string $type)
    {
        return SystemBody::whereType(ucfirst($type))->count();
    }
}
