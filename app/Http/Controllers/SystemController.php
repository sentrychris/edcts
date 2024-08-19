<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use App\Traits\HasValidatedRelations;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller
{
    use HasValidatedRelations;

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
        $cacheTTL = 3600;

        $validated = $request->validated();
        $page = $request->get('page', 1);

        $query = $request->only('name', 'exactSearch');
        $prevQuery = Cache::get('systems_search_query');
        if ($query !== $prevQuery) {
            Cache::forget('systems_search_query');
            Cache::forget("systems_page_{$page}");
        }

        $systems = Cache::remember("systems_page_{$page}", $cacheTTL, function() use ($validated, $request) {
            $records = System::filter($validated, (int)$request->exactSearch)
                ->paginate($request->get('limit', config('app.pagination.limit')))
                ->appends($request->all());

            $records = $this->loadValidatedRelationsForSystem($validated, $records);

            return $records;
        });

        Cache::set('systems_search_query', $query, $cacheTTL);

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
        $validated = $request->validated();

        $system = System::whereSlug($slug)->first();
        if (!$system) {
            $system = System::retrieveBy($slug);
        }

        if (!$system) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $system = $this->loadValidatedRelationsForSystem($validated, $system);

        return response(new SystemResource($system));
    }
}
