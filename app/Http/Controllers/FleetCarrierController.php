<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetCarrierRequest;
use App\Http\Requests\StoreFleetCarrierRequest;
use App\Http\Resources\FleetCarrierResource;
use App\Models\FleetCarrier;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FleetCarrierController extends Controller
{
    use HasValidatedQueryRelations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'has.cmdr'], [
            'only' => ['store', 'update', 'destroy']
        ]);

        $this->setAllowedQueryRelations([
            'withCommanderInformation' => 'commander',
            'withCarrierJourneyScheduleInformation' => [
                'carrierJourneySchedule.departure',
                'carrierJourneySchedule.destination'
            ],
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
     * withCarrierJourneyScheduleInformation: 0 or 1  - Return carrier with associated journey schedule information.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit.
     * 
     * @param SearchFleetCarrierRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(SearchFleetCarrierRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $carriers = FleetCarrier::filter($validated, (int)$request->exactSearch)
            ->simplePaginate($request->input('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $this->loadValidatedRelationsForQuery($validated, $carriers);

        return FleetCarrierResource::collection($carriers);
    }

    /**
     * Show carrier.
     * 
     * User can provide the following request parameters.
     * 
     * withCommanderInformation: 0 or 1 - Return carrier with associated commander information.
     * withCarrierJourneyScheduleInformation: 0 or 1  - Return carrier with associated journey schedule information.
     * 
     * @param string $slug
     * @param SearchFleetCarrierRequest $request
     * @return FleetCarrierResource|Response
     */
    public function show(string $slug, SearchFleetCarrierRequest $request): FleetCarrierResource|Response
    {
        $carrier = FleetCarrier::whereSlug($slug)->first();

        if (!$carrier) {
            return response([], 404);
        }

        $this->loadValidatedRelationsForQuery($request->validated(), $carrier);

        return new FleetCarrierResource($carrier);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreFleetCarrierRequest $request
     * @return FleetCarrierResource|Response
     */
    public function store(StoreFleetCarrierRequest $request): FleetCarrierResource|Response
    {
        $carrier = $request->user()->commander
            ->carriers()
            ->create($request->validated());

        return new FleetCarrierResource($carrier->load('commander'));
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param string $id
     * @param Request $request
     * @return FleetCarrierResource|Response
     */
    public function update(string $id, Request $request): FleetCarrierResource|Response
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response([], 404);
        }

        $carrier->update($request->toArray());

        return new FleetCarrierResource(
            $carrier->load(['commander', 'carrierJourneySchedule'])
        );
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function destroy(string $id, Request $request): Response
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response([], 404);
        }

        $carrier->delete();

        return response([
            'message' => 'Fleet carrier and associated journey schedule has been deleted'
        ]);
    }
}
