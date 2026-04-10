<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemByDistanceRequest;
use App\Http\Requests\SearchSystemByInformationRequest;
use App\Http\Requests\SearchSystemRequest;
use App\Http\Requests\SearchSystemRouteRequest;
use App\Http\Resources\SystemDistanceResource;
use App\Http\Resources\SystemResource;
use App\Http\Resources\SystemRouteResource;
use App\Models\System;
use App\Services\EdsmApiService;
use App\Services\NavRouteFinderService;
use App\Traits\HasQueryRelations;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    use HasQueryRelations;

    /**
     * EDSM API Service
     */
    private EdsmApiService $edsmApiService;

    /**
     * Route Finder Service
     */
    private NavRouteFinderService $navRouteFinderService;

    /**
     * Constructor
     *
     * @param  EdsmApiService  $edsmApiService  - injected EDSM API service
     * @param  NavRouteFinderService  $NavRouteFinderService  - injected route finder service
     */
    public function __construct(EdsmApiService $edsmApiService, NavRouteFinderService $navRouteFinderService)
    {
        $this->edsmApiService = $edsmApiService;
        $this->navRouteFinderService = $navRouteFinderService;

        // Map the allowed query parameters to the relations that can be loaded
        // for the system model e.g. withBodies will load bodies for the system
        $this->setQueryRelations([
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations',
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
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * page: - page number.
     * limit: - page limit.
     */
    public function index(SearchSystemRequest $request): AnonymousResourceCollection
    {
        // Get the request parameters
        $page = $request->input('page', 1);
        $limit = $request->input('limit', config('app.pagination.limit'));
        $validated = $request->validated();

        // Handle the request
        if ($request->input('name') !== null) {
            // Handle queries for systems if searching for systems by name, with
            // or without exact search
            $systems = System::filter($validated, (int) $request->exactSearch)
                ->simplePaginate($limit)
                ->appends($request->all());
        } else {
            // Otherwise attempt to retrieve the current page from the cache
            $systems = Cache::get("systems_page_{$page}");

            // If the page does not exist in the cache, then retrieve it from the database
            if (! $systems) {
                Log::channel('pages:cache')
                    ->info("systems_page_{$page} cache MISS - refreshing cache for this page");

                $systems = System::filter($validated, 0)
                    ->simplePaginate($limit)
                    ->appends($request->all());

                // Cache the page for 1 hour
                Cache::set("systems_page_{$page}", $systems, 3600);
            }
        }

        // Load the query relations for the collection e.g withInformation, withBodies, etc.
        $systems = $this->loadQueryRelations($validated, $systems);

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
     * withStations: 0 or 1 - Return system with associated stations and outposts.
     *
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

        if (! $system) {
            // If the system doesn't exist in our database, query EDSM for it
            // and then update our records
            $system = $this->edsmApiService->updateSystemData($slug);
        }

        // If no system if found, then return a 404 not found response
        if (! $system) {
            return response([], 404);
        }

        // Get the request parameters
        $validated = $request->validated();

        // Update the system with the requested relations e.g. withBodies, withInformation, etc.
        foreach ($this->getQueryRelations() as $query => $relation) {
            if (array_key_exists($query, $validated) && (int) $validated[$query] === 1) {
                // Check for existing system bodies and update if necessary
                if ($relation === 'bodies' && ! $system->bodies()->exists() && $system->body_count === null) {
                    $this->edsmApiService->updateSystemBodiesData($system);
                }

                // Check for existing system information and update if necessary
                if ($relation === 'information' && ! $system->information()->exists()) {
                    $this->edsmApiService->updateSystemInformationData($system);
                }

                // Check for existing system stations and update if necessary
                if ($relation === 'stations' && ! $system->stations()->exists()) {
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
     * Get the last updated system
     *
     * @return SystemResource
     */
    public function getLastUpdated()
    {
        $system = Cache::get('latest_system');
        if (! $system) {
            $system = System::latest('updated_at')->first();
            Cache::set('latest_system', $system);
        }

        if ($system->body_count === null && ! $system->bodies()->exists()) {
            $this->edsmApiService->updateSystemBodiesData($system);
        }

        if (! $system->information()->exists()) {
            $this->edsmApiService->updateSystemInformationData($system);
        }

        $this->loadQueryRelations(
            ['withBodies' => 1, 'withInformation' => 1],
            $system
        );

        return new SystemResource($system);
    }

    /**
     * Find systems by distance in light years.
     */
    public function searchByDistance(SearchSystemByDistanceRequest $request)
    {
        $cacheKey = "systems_distance_{$request->x}_{$request->y}_{$request->z}_{$request->ly}";
        $systems = Cache::get($cacheKey);

        if (! $systems) {
            $systems = System::findNearest(
                $request->only(['x', 'y', 'z']),
                $request->input('ly', 1000),
            )
                // ->with('information')
                ->simplePaginate($request->input('limit', 20));

            Cache::set($cacheKey, $systems, 86400);
        }

        return SystemDistanceResource::collection($systems);
    }

    /**
     * Find the shortest route between two systems.
     *
     * User can provide the following request parameters.
     *
     * from: - Slug of the origin system.
     * to:   - Slug of the destination system.
     * ly:   - Maximum jump range in light years.
     */
    public function searchRoute(SearchSystemRouteRequest $request): AnonymousResourceCollection|Response
    {
        $from = System::whereSlug($request->input('from'))->firstOrFail();
        $to = System::whereSlug($request->input('to'))->firstOrFail();
        $ly = (float) $request->input('ly');

        $cacheKey = "system_route_{$from->slug}_{$to->slug}_{$ly}";
        $waypoints = Cache::get($cacheKey);

        if (! $waypoints) {
            $route = $this->navRouteFinderService->findRoute($from, $to, $ly);

            if ($route === null) {
                return response(['message' => 'No route found within the given jump range.'], 404);
            }

            $totalDistance = 0.0;
            $waypoints = [];

            foreach ($route as $jump => $system) {
                $hopDistance = $jump === 0
                    ? 0.0
                    : $this->navRouteFinderService->distance(
                        [
                            'x' => (float) $route[$jump - 1]->coords_x,
                            'y' => (float) $route[$jump - 1]->coords_y,
                            'z' => (float) $route[$jump - 1]->coords_z
                        ],
                        [
                            'x' => (float) $system->coords_x,
                            'y' => (float) $system->coords_y,
                            'z' => (float) $system->coords_z
                        ],
                    );

                $totalDistance += $hopDistance;

                $waypoints[] = [
                    'jump' => $jump,
                    'system' => $system,
                    'distance' => $hopDistance,
                    'total_distance' => $totalDistance,
                ];
            }

            Cache::set($cacheKey, $waypoints, 86400);
        }

        return SystemRouteResource::collection(collect($waypoints));
    }

    /**
     * Search for systems by information.
     *
     * User can provide the following request parameters.
     *
     * population: - Filter systems by population.
     * allegiance: - Filter systems by allegiance.
     * government: - Filter systems by government.
     * economy: - Filter systems by economy.
     * security: - Filter systems by security.
     * withInformation: 0 or 1 - Return system with associated information.
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * withStations: 0 or 1 - Return system with associated stations and outposts.
     *
     * @return AnonymousResourceCollection
     */
    public function searchByInformation(SearchSystemByInformationRequest $request)
    {
        $validated = $request->validated();
        $relation = 'information';

        $systems = System::query()
            ->when($request->has('population'),
                fn ($query) => $query->whereHas($relation,
                    fn ($query) => $query->where('population', '>=', $validated['population'])
                )
            )

            ->when($request->has('allegiance'),
                fn ($query) => $query->whereHas($relation,
                    fn ($query) => $query->where('allegiance', 'LIKE', $validated['allegiance'].'%')
                )
            )

            ->when($request->has('government'),
                fn ($query) => $query->whereHas($relation,
                    fn ($query) => $query->where('government', 'LIKE', $validated['government'].'%')
                )
            )

            ->when(
                $request->has('economy'),
                fn ($query) => $query->whereHas($relation,
                    fn ($query) => $query->where('economy', 'LIKE', $validated['economy'].'%')
                )
            )

            ->when($request->has('security'),
                fn ($query) => $query->whereHas($relation,
                    fn ($query) => $query->where('security', 'LIKE', $validated['security'].'%')
                )
            )
            ->simplePaginate();

        $this->loadQueryRelations(
            $request->only(['withInformation', 'withBodies', 'withStations']),
            $systems
        );

        return SystemResource::collection($systems);
    }

    /**
     * Return a full list of system slugs and ID64s.
     */
    public function getSlugID64s()
    {
        $items = Cache::get('systems_id64_slugs');

        if (! $items) {
            $items = System::pluck('id64', 'slug');
            Cache::set('systems_id64_slugs', $items, 1800);
        }

        return response()->json($items);
    }
}
