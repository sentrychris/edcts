<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * List systems.
     * 
     * User can provide the following request parameters.
     * 
     * name: - Filter systems by name.
     * 
     * withInformation: 0 or 1 - Return system with associated information.
     * 
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * 
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * 
     * withDepartures: 0 or 1 - Return systems with associated carrier departures schedule.
     * 
     * withArrivals: 0 or 1 - Return systems with associated carrier arrivals schedule.
     * 
     * limit: - page limit.
     */
    public function index(SearchSystemRequest $request)
    {
        $validated = $request->validated();
        $systems = System::filter($validated, (int)$request->get('exactSearch'))
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        if (!$systems) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        if ((int)$request->get('withInformation') === 1) {
            $systems->load('information');
        }

        if ((int)$request->get('withBodies') === 1) {
            $systems->load('bodies');
        }

        if ((int)$request->get('withDepartures') === 1) {
            $systems->load('departures');
        }

        if ((int)$request->withArrivals === 1) {
            $systems->load('arrivals.departure');
        }

        return SystemResource::collection($systems);
    }

    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withInformation: 0 or 1 - Return system with associated information.
     * 
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * 
     * withDepartures: 0 or 1 - Return system with associated carrier departures schedule.
     * 
     * withArrivals: 0 or 1 - Return system with associated carrier arrivals schedule.
     */
    public function show(string $slug, Request $request)
    {
        $source = 'edsm';
        $system = System::whereSlug($slug)->first();

        if (!$system) {
            $system = System::checkApi($source, $slug);
        }

        if (!$system) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        if ((int)$request->withInformation === 1) {
            $system->checkApiForSystemInformation($source);
            $system->load('information');
        }

        if ((int)$request->withBodies === 1) {
            $system->checkApiForSystemBodies($source);
            $system->load('bodies');
        }
        
        if ((int)$request->withDepartures === 1) {
            $system->load('departures.destination');
        }

        if ((int)$request->withArrivals === 1) {
            $system->load('arrivals.departure');
        }

        return response(new SystemResource($system));
    }
}
