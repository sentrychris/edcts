<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchStationRequest;
use App\Http\Resources\SystemStationResource;
use App\Models\SystemStation;
use App\Traits\HasQueryRelations;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class StationController extends Controller
{
    use HasQueryRelations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setQueryRelations([
            'withSystem' => 'system.bodies',
        ]);
    }

    /**
     * Show station.
     *
     * User can provide the following request parameters.
     *
     * withSystem: 0 or 1 - Return station with associated system.
     */
    #[OA\Get(
        path: '/stations/{slug}',
        summary: 'Get a single station by slug',
        description: 'Retrieves a station, outpost, or megaship by its slug ({market_id}-{name}). Pass withSystem to embed the parent system and its bodies.',
        tags: ['Stations'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                description: 'Station slug in format {market_id}-{name}',
                schema: new OA\Schema(type: 'string', example: '128016384-daedalus')
            ),
            new OA\Parameter(name: 'withSystem', in: 'query', required: false, description: 'Embed the parent system and its bodies', schema: new OA\Schema(type: 'integer', enum: [0, 1])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Station',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Station'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Station not found'),
        ]
    )]
    public function show(string $slug, SearchStationRequest $request): SystemStationResource|Response
    {
        $station = SystemStation::whereSlug($slug)->first();

        if (! $station) {
            return response([], 404);
        }

        // Load related data for the station depending on query parameters passed.
        $station = $this->loadQueryRelations($request->validated(), $station);

        return new SystemStationResource($station);
    }
}
