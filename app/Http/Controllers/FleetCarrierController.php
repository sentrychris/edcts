<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetCarrierRequest;
use App\Http\Requests\StoreFleetCarrierRequest;
use App\Http\Resources\FleetCarrierResource;
use App\Models\FleetCarrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * name: - Filter carriers by name
     * 
     * identifier: - Filter carriers by identifier
     * 
     * withCommanderInformation: 0 or 1 - Return carrier with associated commander information.
     * 
     * withScheduleInformation: 0 or 1  - Return carrier with associated schedule information.
     * 
     * operand: "in" or "like" - Search for exact matches or based on a partial
     *                           string.
     * 
     * limit: - page limit
     */
    public function index(SearchFleetCarrierRequest $request)
    {
        $validated = $request->validated();

        $carriers = FleetCarrier::filter($validated, $request->get('operand', 'in'))
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        if ((int)$request->get('withCommanderInformation') === 1) {
            $carriers->load('commander');
        }

        if ((int)$request->get('withScheduleInformation') === 1) {
            $carriers->load(['schedule.departure', 'schedule.destination']);
        }

        return FleetCarrierResource::collection($carriers);
    }

    /**
     * Show carrier.
     * 
     * User can provide the following request parameters.
     * 
     * withCommanderInformation: 0 or 1 - Return carrier with associated commander information.
     * 
     * withScheduleInformation: 0 or 1  - Return carrier with associated schedule information.
     * 
     */
    public function show(string $slug, Request $request)
    {
        $carrier = FleetCarrier::whereSlug($slug)->first();

        if (!$carrier) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        if ((int)$request->get('withCommanderInformation') === 1) {
            $carrier->load('commander');
        }

        if ((int)$request->get('withScheduleInformation') === 1) {
            $carrier->load(['schedule.departure', 'schedule.destination']);
        }

        return response()->json(
            new FleetCarrierResource($carrier)
        );
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreFleetCarrierRequest $request
     * @return JsonResponse
     */
    public function store(StoreFleetCarrierRequest $request)
    {
        $validated = $request->validated();
        $carrier = $request->user()->commander->carriers()->create($validated);

        return response()->json(
            new FleetCarrierResource($carrier->load('commander')),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(string $id, Request $request)
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $carrier->update($request->toArray());

        return response()->json(
            new FleetCarrierResource($carrier->load(['commander', 'schedule']))
        );
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(string $id, Request $request)
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $carrier->delete();

        return response()->json([
            'message' => 'Fleet carrier and associated schedule has been deleted'
        ]);
    }
}
