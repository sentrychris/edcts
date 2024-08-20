<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use App\Services\EdsmApiService;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    use HasValidatedQueryRelations;

    /**
     * @var EdsmApiService $edsmApiService
     */
    private EdsmApiService $edsmApiService;

    /**
     * Constructor
     */
    public function __construct(EdsmApiService $edsmApiService)
    {
        $this->edsmApiService = $edsmApiService;
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
     * withDepartures: 0 or 1 - Return systems with associated carrier departures schedule.
     * withArrivals: 0 or 1 - Return systems with associated carrier arrivals schedule.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit.
     * 
     * @param SearchSystemRequest $request
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchSystemRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', config('app.pagination.limit'));

        if ($request->get('name') !== null) {
            $systems = System::filter($validated, (int)$request->exactSearch)
                ->paginate($limit)
                ->appends($request->all());
        } else {
            $systems = Cache::get("systems_page_{$page}");

            if (!$systems) {
                Log::channel('pages:cache')
                    ->info("systems_page_{$page} cache MISS - refreshing cache for this page");

                $systems = System::filter($validated, 0)
                    ->paginate($limit)
                    ->appends($request->all());

                Cache::set("systems_page_{$page}", $systems, 3600);
            }
        }

        $systems = $this->loadValidatedRelationsForSystem($validated, $systems);

        return SystemResource::collection($systems);
    }

    
    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withInformation: 0 or 1 - Return system with associated information.
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * withDepartures: 0 or 1 - Return system with associated carrier departures schedule.
     * withArrivals: 0 or 1 - Return system with associated carrier arrivals schedule.
     * 
     * @param string $slug
     * @param SearchSystemRequest $request
     * 
     * @return Response
     */
    public function show(string $slug, SearchSystemRequest $request): Response
    {
        $system = System::whereSlug($slug)->first();
        
        if (!$system) {
            // If the system doesn't exist in our database, query EDSM for it and then update
            // our records.
            $system = $this->edsmApiService->updateSystemData($slug);
        }

        // If no system if found, then return a 404 not found
        if (!$system) {
            return response(null, 404);
        }

        $system = $this->loadValidatedRelationsForSystem($request->validated(), $system);

        return response(new SystemResource($system));
    }
}
