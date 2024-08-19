<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use App\Models\SystemBody;
use App\Models\SystemInformation;
use App\Models\SystemStation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller
{
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

        $systems = Cache::remember("systems_page_{$page}", $cacheTTL, function() use ($validated, $request) {
            $records = System::filter($validated, (int)$request->exactSearch)
                ->paginate($request->get('limit', config('app.pagination.limit')))
                ->appends($request->all());

            $records = $this->loadValidatedRelations($validated, $records);

            return $records;
        });

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

        $system = $this->loadValidatedRelations($validated, $system);

        return response(new SystemResource($system));
    }

    /**
     * Load validated relations based on query.
     * 
     * @param array $validated
     * @param Model|LengthAwarePaginator $data
     * 
     * @return Model|LengthAwarePaginator $data
     */
    private function loadValidatedRelations(array $validated, Model | LengthAwarePaginator $model): Model|LengthAwarePaginator
    {
        $allowed = [
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations',
            'withDepartures' => 'departures.destination',
            'withArrivals' => 'arrivals.departure'
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                if ($model instanceof Model && $relation === 'bodies') {
                    SystemBody::retrieveBy($model);
                }

                if ($model instanceof Model && $relation === 'information') {
                    SystemInformation::retrieveBy($model);
                }

                if ($model instanceof Model && $relation === 'stations') {
                    SystemStation::retrieveBy($model);
                }

                $model->load($relation);
            }
        }

        return $model;
    }
}
