<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFleetScheduleRequest;
use App\Models\FleetCarrier;
use App\Models\FleetSchedule;
use Illuminate\Http\Request;

class FleetScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedule = FleetCarrier::with('schedule')->get();
        return response()->json($schedule);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFleetScheduleRequest $request)
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::create($validated);

        return response()->json($schedule);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
