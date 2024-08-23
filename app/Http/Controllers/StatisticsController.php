<?php

namespace App\Http\Controllers;

use App\Traits\UseStatistics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class StatisticsController extends Controller
{
    use UseStatistics;
    
    /**
     * Get statistics.
     * 
     * Statistics are cached and refreshed every hour through the artisan
     * scheduler.
     * 
     * @param Request $request
     * @return Response
     */
    public function getStatistics(Request $request): Response
    {
        return response([
            'data' => $this->getAllStatistics("statistics", $request->all())
        ]);
    }

    /**
     * Get the last ten received nav routes.
     * 
     * @return Response
     */
    public function getLastTenNavRoutes(): Response
    {
        $navRoutes = Redis::lrange("eddn_navroutes", 0, -1);

        foreach($navRoutes as $key => $route) {
            $navRoutes[$key] = json_decode($route, true);
        }

        return response([
            'count' => count($navRoutes),
            'data' => $navRoutes
        ]);
    }
}
