<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetScheduleRequest;
use App\Http\Requests\StoreFleetScheduleRequest;
use App\Http\Resources\FleetScheduleResource;
use App\Models\FleetSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetScheduleController extends Controller
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
     * List schedule.
     * 
     * User can provide the following request parameters.
     * 
     * departure:   - Filter schedule by departure points
     * 
     * destination: - Filter schedule by destination points
     * 
     * withCarrierInformation: 0 or 1 - Return schedule with associated carrier/commander
     *                                  information.
     * 
     * withSystemInformation: 0 or 1  - Return schedule with associated departure/destination
     *                                  information.
     * 
     * operand: "in" or "like" - Search for exact matches or based on a partial
     *                           string.
     * 
     * limit: - page limit
     */
    public function index(SearchFleetScheduleRequest $request)
    {        
        $validated = $request->validated();

        $schedule = FleetSchedule::filter($validated, $request->get('operand', 'in'))
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        if ((int)$request->get('withCarrierInformation') === 1) {
            $schedule->load('carrier.commander');
        }

        if ((int)$request->get('withSystemInformation') === 1) {
            $schedule->load(['departure.information', 'destination.information']);
        }
        
        return FleetScheduleResource::collection($schedule);
    }
    
    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withCarrierInformation: 0 or 1 - Return schedule with associated carrier/commander
     *                                  information.
     * 
     * withSystemInformation: 0 or 1  - Return schedule with associated departure/destination
     *                                  information.
     */
    public function show(string $slug, Request $request)
    {
        $schedule = FleetSchedule::whereSlug($slug)->first();
        
        if (!$schedule) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        if ((int)$request->get('withCarrierInformation') === 1) {
            $schedule->load('carrier.commander');
        }

        if ((int)$request->get('withSystemInformation') === 1) {
            $schedule->load(['departure.information', 'destination.information']);
        }
        
        return response()->json(
            new FleetScheduleResource($schedule)
        );
    }
    
    /**
    * Store a newly created resource in storage.
    * 
    * @param StoreFleetScheduleRequest $request
    * @return JsonResponse
    */
    public function store(StoreFleetScheduleRequest $request)
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::create($validated);
        
        return response()->json(
            new FleetScheduleResource($schedule->load(['carrier.commander', 'departure', 'destination'])),
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
        $schedule = $request->user()->commander->schedule()->find($id);
        
        if (!$schedule) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }
        
        $schedule->update($request->toArray());
        
        return response()->json(
            new FleetScheduleResource($schedule->load('carrier.commander'))
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
        $schedule = $request->user()->commander->schedule()->find($id);
        
        if (!$schedule) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }
        
        $schedule->delete();
        
        return response()->json([
            'message' => 'Scheduled carrier trip has been deleted'
        ]);
    }
}
