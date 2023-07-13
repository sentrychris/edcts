<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFleetCarrierRequest;
use App\Http\Resources\FleetCarrierResource;
use App\Models\FleetCarrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetCarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $carriers = FleetCarrier::with('schedule')
            ->filter($request->toArray());

        return FleetCarrierResource::collection(
            $carriers->paginate($request->get('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFleetCarrierRequest $request)
    {
        $validated = $request->validated();
        $carrier = FleetCarrier::create($validated);

        return response()->json(
            new FleetCarrierResource($carrier),
            JsonResponse::HTTP_CREATED
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
            new FleetCarrierResource($carrier->load('schedule'))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
