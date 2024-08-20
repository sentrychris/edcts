<?php

namespace App\Http\Controllers;

use App\Traits\UseStatistics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
    public function index(Request $request): Response
    {
        return response([
            'data' => $this->getAllStatistics("statistics", $request->all())
        ]);
    }
}
