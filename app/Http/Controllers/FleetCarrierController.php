<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetCarrierRequest;
use App\Http\Requests\StoreFleetCarrierRequest;
use App\Http\Resources\FleetCarrierResource;
use App\Models\FleetCarrier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class FleetCarrierController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'has.cmdr'], [
            'only' => ['store', 'update', 'destroy']
        ]);
    }

    /**
     * List carriers.
     * 
     * User can provide the following request parameters.
     * 
     * name: - Filter carriers by name.
     * identifier: - Filter carriers by identifier.
     * withCommanderInformation: 0 or 1 - Return carrier with associated commander information.
     * withScheduleInformation: 0 or 1  - Return carrier with associated schedule information.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit.
     * 
     * @param SearchFleetCarrierRequest $request
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchFleetCarrierRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $carriers = FleetCarrier::filter($validated, (int)$request->exactSearch)
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $this->loadValidatedRelations($validated, $carriers);

        return FleetCarrierResource::collection($carriers);
    }

    /**
     * Show carrier.
     * 
     * User can provide the following request parameters.
     * 
     * withCommanderInformation: 0 or 1 - Return carrier with associated commander information.
     * withScheduleInformation: 0 or 1  - Return carrier with associated schedule information.
     * 
     * @param string $slug
     * @param SearchFleetCarrierRequest $request
     * 
     * @return Response
     */
    public function show(string $slug, SearchFleetCarrierRequest $request): Response
    {
        $validated = $request->validated();
        $carrier = FleetCarrier::whereSlug($slug)->first();

        if (!$carrier) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->loadValidatedRelations($validated, $carrier);

        return response(new FleetCarrierResource($carrier));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreFleetCarrierRequest $request
     * @return Response
     */
    public function store(StoreFleetCarrierRequest $request): Response
    {
        $validated = $request->validated();
        $carrier = $request->user()->commander->carriers()->create($validated);

        return response(
            new FleetCarrierResource($carrier->load('commander')),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param string $id
     * @param Request $request
     * 
     * @return Response
     */
    public function update(string $id, Request $request): Response
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $carrier->update($request->toArray());

        return response(
            new FleetCarrierResource($carrier->load(['commander', 'schedule']))
        );
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id
     * @param Request $request
     * 
     * @return Response
     */
    public function destroy(string $id, Request $request): Response
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $carrier->delete();

        return response([
            'message' => 'Fleet carrier and associated schedule has been deleted'
        ]);
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
            'withCommanderInformation' => 'commander',
            'withScheduleInformation' => ['schedule.departure', 'schedule.destination'],
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                $data->load($relation);
            }
        }

        return $data;
    }
}
