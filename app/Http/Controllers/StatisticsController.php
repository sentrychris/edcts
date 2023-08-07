<?php

namespace App\Http\Controllers;

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
     * Statistics are cached and refreshed every hour through the artisan
     * scheduler.
     * 
     * @param Request $request
     * @return Response
     */
    public function index(): Response
    {          
        return Response(['data' => Cache::get($this->cacheKey)]);
    }
}
