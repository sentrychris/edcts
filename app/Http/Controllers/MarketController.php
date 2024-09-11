<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketDataResource;
use App\Models\SystemStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MarketController extends Controller
{
    /**
     *  Get market data for a specific station.
     * 
     * @param string $slug - The slug of the station.
     * @return JsonResponse|MarketDataResource
     */
    public function getMarketDataForStation(string $slug): JsonResponse|MarketDataResource
    {
        $station = SystemStation::whereSlug($slug)
            ->with('system')
            ->first();

        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        $stationName = str_replace(" ", "_", $station->name);
        $marketData = Redis::get("{$station->system->id64}_{$stationName}_eddn_market_data");

        return new MarketDataResource(json_decode($marketData));
    }
}
