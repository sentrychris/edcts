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
     * The EDSM API Service
     */
    private EdsmApiService $edsmApiService;

    /**
     * Constructor
     */
    public function __construct(EdsmApiService $service)
    {
        $this->edsmApiService = $service;

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
        $page = $request->get('page', 1);
        $limit = $request->get('limit', config('app.pagination.limit'));
        $validated = $request->validated();

        if ($request->get('name') !== null) {
            // Handle queries for specific systems based on system name
            $systems = System::filter($validated, (int)$request->exactSearch)
                ->paginate($limit)
                ->appends($request->all());
        } else {
            // Otherwise retrieve the current page from the cache
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

        // Load the requested and validated query relations for the collection
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
        // Retrieve the system based on the slug (id64-name composite).
        // TODO: Cache the id64 and name, if just a name is passed here, use the cache to retrieve
        //       the corresponding id64 and construct the slug
        $system = System::whereSlug($slug)->first();
        $validated = $request->validated();
        
        if (!$system) {
            // If the system doesn't exist in our database, query EDSM for it and then
            // update our records
            $system = $this->edsmApiService->updateSystemData($slug);
        }

        // If no system if found, then return a 404 not found response
        if (!$system) {
            return response([], 404);
        }

        // Update the system with the requested relations
        foreach ($this->getAllowedQueryRelations() as $query => $relation)
        {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                // TODO: Instead of not updating if records already exist, set a timer flag somewhere in the database
                //       and check it, and if the timer allows, make an update request to EDSM (we don't want to spam
                //       the API every time a user requests a system with all relations)

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

                $system->load($relation);
            }
        }

        return new SystemResource($system);
    }
}
