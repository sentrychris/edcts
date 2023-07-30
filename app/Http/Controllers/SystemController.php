<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * withDepartures: 0 or 1 - Return systems with associated carrier departures schedule.
     * withArrivals: 0 or 1 - Return systems with associated carrier arrivals schedule.
     * limit: - page limit.
     * 
     * @param SearchSystemRequest $request
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchSystemRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $systems = System::filter($validated, (int)$request->exactSearch)
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $systems = $this->loadValidatedRelations($validated, $systems);

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
        $source = 'edsm';
        $validated = $request->validated();
        $system = System::whereSlug($slug)->first();

        if (!$system) {
            $system = System::checkApi($source, $slug);
        }

        if (!$system) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $system = $this->loadValidatedRelations($validated, $system, $source);

        return response(new SystemResource($system));
    }

    /**
     * Load validated relations based on query.
     * 
     * @param array $validated
     * @param Model|LengthAwarePaginator $data
     * @param string|null $source
     * 
     * @return Model|LengthAwarePaginator $data
     */
    private function loadValidatedRelations(
        array $validated,
        Model | LengthAwarePaginator $data,
        ?string $source = null): Model|LengthAwarePaginator
    {
        $allowed = [
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withDepartures' => 'departures.destination',
            'withArrivals' => 'arrivals.departure'
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                if ($source && $relation === 'bodies') {
                    $data->checkApiForSystemBodies($source);
                }

                if ($source && $relation === 'information') {
                    $data->checkApiForSystemInformation($source);
                }

                $data->load($relation);
            }
        }

        return $data;
    }
}
