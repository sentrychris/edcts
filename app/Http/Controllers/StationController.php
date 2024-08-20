<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchStationRequest;
use App\Http\Resources\SystemStationResource;
use App\Models\SystemStation;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Response;

class StationController extends Controller
{
    use HasValidatedQueryRelations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAllowedQueryRelations([
            'withSystem' => 'system'
        ]);
    }

    
    /**
     * Show station.
     * 
     * User can provide the following request parameters.
     * 
     * withSystem: 0 or 1 - Return station with associated system.
     * 
     * @param string $slug
     * @param SearchStationRequest $request
     * @return SystemStationResource|Response
     */
    public function show(string $slug, SearchStationRequest $request): SystemStationResource|Response
    {
        $station = SystemStation::whereSlug($slug)->first();

        if (! $station) {
            return response([], 404);
        }

        // Load related data for the station depending on query parameters passed.
        $station = $this->loadValidatedRelationsForQuery($request->validated(), $station);

        return new SystemStationResource($station);
    }
}
