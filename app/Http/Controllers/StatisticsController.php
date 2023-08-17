<?php

namespace App\Http\Controllers;

use App\Traits\UsesStatistics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StatisticsController extends Controller
{
    use UsesStatistics;

    /** @var string */
    private string $cacheKey = 'edcts:statistics';
    
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
        return Response([
            'data' => $this->getAllStatistics($this->cacheKey, $request->all())
        ]);
    }
}
