<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchStationRequest;
use App\Http\Resources\SystemResource;
use App\Http\Resources\SystemStationResource;
use App\Models\SystemStation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class StationController extends Controller
{
    /**
     * List stations.
     * 
     * User can provide the following request parameters.
     * 
     * name: - Filter stations by name.
     * withSystem: 0 or 1 - Return stations with associated system.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit.
     * 
     * @param SearchStationRequest $request
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchStationRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $stations = SystemStation::filter($validated, (int)$request->exactSearch)
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $stations = $this->loadValidatedRelations($validated, $stations);

        return SystemStationResource::collection($stations);
    }

    
    /**
     * Show station.
     * 
     * User can provide the following request parameters.
     * 
     * withSystem: 0 or 1 - Return station with associated system.
     * 
     * @param string $slug
     * @param SearchSta $request
     * 
     * @return Response
     */
    public function show(string $slug, SearchStationRequest $request): Response
    {
        $validated = $request->validated();
        $station = SystemStation::whereSlug($slug)->first();

        if (! $station) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        // Load related data for the station depending on query parameters passed.
        $station = $this->loadValidatedRelations($validated, $station);

        return response(new SystemStationResource($station));
    }

    /**
     * Load validated relations based on query.
     * 
     * @param array $validated
     * @param Model|LengthAwarePaginator $data
     * 
     * @return Model|LengthAwarePaginator $data
     */
    private function loadValidatedRelations(array $validated, Model | LengthAwarePaginator $data): Model|LengthAwarePaginator
    {
        $allowed = [
            'withSystem' => 'system',
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                $data->load($relation);
            }
        }

        return $data;
    }
}
