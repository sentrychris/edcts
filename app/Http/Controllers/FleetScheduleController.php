<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetScheduleRequest;
use App\Http\Requests\StoreFleetScheduleRequest;
use App\Http\Resources\FleetScheduleResource;
use App\Models\FleetCarrier;
use App\Models\FleetSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchFleetScheduleRequest $request )
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::with('carrier')->filter($validated);

        return FleetScheduleResource::collection(
            $schedule->paginate($request->get('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFleetScheduleRequest $request)
    {
        $validated = $request->validated();
        $carrier = FleetCarrier::find($validated['fleet_carrier_id']);
        $schedule = $carrier->schedule()->create($validated);

        return response()->json(
            new FleetScheduleResource($schedule->load('carrier')),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = FleetSchedule::find($id);

        if (!$schedule) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json(
            new FleetScheduleResource($schedule->load('carrier'))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO
    }
}
