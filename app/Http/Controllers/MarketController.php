<?php

namespace App\Http\Controllers;

use App\Models\SystemStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MarketController extends Controller
{
    /**
     *  Get market data for a specific station.
     * 
     * @param string $slug - The slug of the station.
     * @return JsonResponse
     */
    public function getMarketDataForStation(string $slug): JsonResponse
    {
        $station = SystemStation::whereSlug($slug)
            ->with('system')
            ->first();

        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        $stationName = str_replace(" ", "_", $station->name);
        $key = "{$station->system->id64}_{$stationName}_station_market_data";

        $marketData = Cache::get($key);
        if (!$marketData) {
            return response()->json([
                'message' => "No market data found for {$station->name} in {$station->system->name}."
            ], 404);
        }

        return response()->json($marketData);
    }
}
