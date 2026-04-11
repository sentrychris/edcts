<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketDataResource;
use App\Models\SystemStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use OpenApi\Attributes as OA;

class MarketController extends Controller
{
    /**
     * Get market data for a specific station.
     *
     * @param  string  $slug  - The slug of the station.
     */
    #[OA\Get(
        path: '/station/{slug}/market',
        summary: 'Get live commodity market data for a station',
        description: 'Returns real-time commodity prices for a station sourced from EDDN. Data is stored in Redis as it arrives from the network. Commodity internal names are mapped to human-readable display names.',
        tags: ['Market'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                description: 'Station slug in format {market_id}-{name}',
                schema: new OA\Schema(type: 'string', example: '128016384-daedalus')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Market data. Returns an empty object if no EDDN data has been received for this station yet.',
                content: new OA\JsonContent(ref: '#/components/schemas/MarketData')
            ),
            new OA\Response(response: 404, description: 'Station not found'),
        ]
    )]
    public function getMarketDataForStation(string $slug): JsonResponse|MarketDataResource
    {
        $station = SystemStation::whereSlug($slug)
            ->with('system')
            ->first();

        if (! $station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        $stationName = str_replace(' ', '_', $station->name);
        $marketData = Redis::get("{$station->system->id64}_{$stationName}_eddn_market_data");

        return new MarketDataResource(json_decode($marketData));
    }
}
