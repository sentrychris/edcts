<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetCarrierRequest;
use App\Http\Requests\StoreFleetCarrierRequest;
use App\Http\Resources\FleetCarrierResource;
use App\Models\FleetCarrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetCarrierController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'has.cmdr'], [
            'only' => ['store', 'update', 'destroy']
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SearchFleetCarrierRequest $request)
    {
        $validated = $request->validated();
        $carriers = FleetCarrier::with(['commander', 'schedule'])->filter($validated);

        return FleetCarrierResource::collection(
            $carriers->paginate($request->get('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $carrier = FleetCarrier::find($id);

        if (!$carrier) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json(
            new FleetCarrierResource($carrier->load(['commander', 'schedule']))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFleetCarrierRequest $request)
    {
        $validated = $request->validated();
        $carrier = $request->user()->commander->carriers()->create($validated);

        return response()->json(
            new FleetCarrierResource($carrier),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, Request $request)
    {
        $carrier = $request->user()->commander->carriers()->find($id);

        if (!$carrier) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $carrier->update($request->toArray());

        return response()->json(
            new FleetCarrierResource($carrier->load('schedule'))
        );
    }

    /**
     * Remove the specified resource from storage.
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
