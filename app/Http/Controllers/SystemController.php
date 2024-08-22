<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use App\Services\EdsmApiService;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    use HasValidatedQueryRelations;

    /**
     * EDSM API Service
     */
    private EdsmApiService $edsmApiService;

    /**
     * Constructor
     * 
     * @param EdsmApiService $service - injected EDSM API service
     */
    public function __construct(EdsmApiService $service)
    {
        $this->edsmApiService = $service;

        // Map the allowed query parameters to the relations that can be loaded
        // for the system model e.g. withBodies will load bodies for the system
        $this->setAllowedQueryRelations([
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations',
            'withDepartures' => 'departures.destination',
            'withArrivals' => 'arrivals.departure'
        ]);
    }

    /**
     * List systems.
     * 
     * User can provide the following request parameters.
     * 
     * name: - Filter systems by name.
     * withInformation: 0 or 1 - Return system with associated information.
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * withStations: 0 or 1 - Return system with associated stations and outposts.
     * withDepartures: 0 or 1 - Return systems with associated carrier journey departures schedule.
     * withArrivals: 0 or 1 - Return systems with associated carrier journey arrivals schedule.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * page: - page number.
     * limit: - page limit.
     * 
     * @param SearchSystemRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(SearchSystemRequest $request): AnonymousResourceCollection
    {
        // Get the request parameters
        $page = $request->get('page', 1);
        $limit = $request->get('limit', config('app.pagination.limit'));
        $validated = $request->validated();

        // Handle the request
        if ($request->get('name') !== null) {
            // Handle queries for systems if searching for systems by name, with
            // or without exact search
            $systems = System::filter($validated, (int)$request->exactSearch)
                ->paginate($limit)
                ->appends($request->all());
        } else {
            // Otherwise attempt to retrieve the current page from the cache
            $systems = Cache::get("systems_page_{$page}");

            // If the page does not exist in the cache, then retrieve it from the database
            if (!$systems) {
                Log::channel('pages:cache')
                    ->info("systems_page_{$page} cache MISS - refreshing cache for this page");

                $systems = System::filter($validated, 0)
                    ->paginate($limit)
                    ->appends($request->all());

                // Cache the page for 1 hour
                Cache::set("systems_page_{$page}", $systems, 3600);
            }
        }

        // Load the query relations for the collection e.g withInformation, withBodies, etc.
        $systems = $this->loadValidatedRelationsForQuery($validated, $systems);

        // Return a collection of system resources
        return SystemResource::collection($systems);
    }

    
    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withInformation: 0 or 1 - Return system with associated information.
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * withDepartures: 0 or 1 - Return system with associated carrier journey departures schedule.
     * withArrivals: 0 or 1 - Return system with associated carrier journey arrivals schedule.
     * 
     * @param string $slug
     * @param SearchSystemRequest $request
     * @return SystemResource
     */
    public function show(string $slug, SearchSystemRequest $request): SystemResource|Response
    {
        // Attempt to retrieve the system from the cache
        $system = Cache::get("system_detail_{$slug}");

        // If it exists in the cache, then return it
        if ($system) {
            return new SystemResource($system);
        }

        // Otherwise it's a cache MISS
        Log::channel('pages:cache')
                    ->info("system_detail_{$slug} cache MISS - refreshing cache for this page");

        // Attempt to retrieve the system from our database
        $system = System::whereSlug($slug)->first();
        
        if (!$system) {
            // If the system doesn't exist in our database, query EDSM for it
            // and then update our records
            $system = $this->edsmApiService->updateSystemData($slug);
        }

        // If no system if found, then return a 404 not found response
        if (!$system) {
            return response([], 404);
        }
        
        // Get the request parameters
        $validated = $request->validated();

        // Update the system with the requested relations e.g. withBodies, withInformation, etc.
        foreach ($this->getAllowedQueryRelations() as $query => $relation)
        {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1)
            {
                // Check for existing system bodies and update if necessary
                if ($relation === 'bodies' && !$system->bodies()->exists() && $system->body_count === null) {
                    $this->edsmApiService->updateSystemBodiesData($system);
                }

                // Check for existing system information and update if necessary
                if ($relation === 'information' && !$system->information()->exists()) {
                    $this->edsmApiService->updateSystemInformationData($system);
                }

                // Check for existing system stations and update if necessary
                if ($relation === 'stations' && !$system->stations()->exists()) {
                    $this->edsmApiService->updateSystemStationsData($system);
                }

                // Load the relation
                $system->load($relation);
            }
        }

        // Cache the system details for 1 hour
        Cache::set("system_detail_{$slug}", $system, 3600);

        // Return the system resource
        return new SystemResource($system);
    }

    /**
     * Get the last added system
     * 
     * @return SystemResource
     */
    public function getLastAddedSystem() {
        $lastAddedSystem = System::with(['information'])
            ->orderBy('id', 'desc')
            ->first();
    
        if ($lastAddedSystem instanceof System) {
            $this->edsmApiService->updateSystemBodiesData($lastAddedSystem);
            $this->edsmApiService->updateSystemInformationData($lastAddedSystem);
        }

        $this->loadValidatedRelationsForQuery(
            ['withBodies' => 1, 'withInformation' => 1],
            $lastAddedSystem
        );

        return new SystemResource($lastAddedSystem);
    }
}
