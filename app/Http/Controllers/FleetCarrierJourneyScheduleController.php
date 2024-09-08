<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetCarrierJourneyScheduleRequest;
use App\Http\Requests\StoreFleetCarrierJourneyScheduleRequest;
use App\Http\Resources\FleetCarrierJourneyScheduleResource;
use App\Models\FleetCarrierJourneySchedule;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FleetCarrierJourneyScheduleController extends Controller
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
            'withCarrierInformation' => 'carrier.commander',
            'withSystemInformation' => ['departure.information', 'destination.information'],
        ]);
    }
    
    /**
     * List scheduled fleet carrier journeys.
     * 
     * User can provide the following request parameters.
     * 
     * departure:   - Filter schedule by departure points.
     * destination: - Filter schedule by destination points.
     * withCarrierInformation: 0 or 1 - Return schedule with associated carrier/commander
     *                                  information.
     * withSystemInformation:  0 or 1 - Return schedule with associated departure/destination
     *                                  information.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit
     * 
     * @param SearchFleetCarrierJourneyScheduleRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(SearchFleetCarrierJourneyScheduleRequest $request): AnonymousResourceCollection
    {        
        $validated = $request->validated();
        $schedule = FleetCarrierJourneySchedule::filter($validated, (int)$request->exactSearch)
            ->simplePaginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $this->loadValidatedRelationsForQuery($validated, $schedule);
        
        return FleetCarrierJourneyScheduleResource::collection($schedule);
    }
    
    /**
     * Show a scheduled carrier journey.
     * 
     * User can provide the following request parameters.
     * 
     * withCarrierInformation: 0 or 1 - Return schedule with associated carrier/commander
     *                                  information.
     * withSystemInformation: 0 or 1  - Return schedule with associated departure/destination
     *                                  information.
     * 
     * @param string $slug
     * @param SearchFleetCarrierJourneyScheduleRequest $request
     * @return FleetCarrierJourneyScheduleResource|Response
     */
    public function show(string $slug, SearchFleetCarrierJourneyScheduleRequest $request): FleetCarrierJourneyScheduleResource|Response
    {
        $validated = $request->validated();
        $schedule = FleetCarrierJourneySchedule::whereSlug($slug)->first();
        
        if (!$schedule) {
            return response([], 404);
        }

        $this->loadValidatedRelationsForQuery($validated, $schedule);
        
        return new FleetCarrierJourneyScheduleResource($schedule);
    }
    
    /**
    * Store a newly created resource in storage.
    * 
    * @param FleetCarrierJourneyScheduleResource $request
    * @return Response
    */
    public function store(StoreFleetCarrierJourneyScheduleRequest $request): FleetCarrierJourneyScheduleResource|Response
    {
        $validated = $request->validated();
        $schedule = FleetCarrierJourneySchedule::create($validated);

        if (!$schedule) {
            return response([], 404);
        }
        
        return new FleetCarrierJourneyScheduleResource(
            $schedule->load(['carrier.commander', 'departure', 'destination'])
        );
    }
    
    /**
    * Update the specified resource in storage.
    * 
    * @param string $id
    * @param Request $request
    * @return Response
    */
    public function update(string $id, Request $request): FleetCarrierJourneyScheduleResource|Response
    {
        $schedule = $request->user()->commander->carrierJourneySchedule()->find($id);
        
        if (!$schedule) {
            return response([], 404);
        }
        
        $schedule->update($request->toArray());
        
        return new FleetCarrierJourneyScheduleResource($schedule->load('carrier.commander'));
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
        $schedule = $request->user()->commander->carrierJourneySchedule()->find($id);
        
        if (!$schedule) {
            return response([], 404);
        }
        
        $schedule->delete();
        
        return response([
            'message' => 'Scheduled carrier journey has been deleted'
        ]);
    }
}
