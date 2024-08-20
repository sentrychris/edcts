<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetScheduleRequest;
use App\Http\Requests\StoreFleetScheduleRequest;
use App\Http\Resources\FleetScheduleResource;
use App\Models\FleetSchedule;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FleetScheduleController extends Controller
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
     * List schedule.
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
     * @param SearchFleetScheduleRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(SearchFleetScheduleRequest $request): AnonymousResourceCollection
    {        
        $validated = $request->validated();
        $schedule = FleetSchedule::filter($validated, (int)$request->exactSearch)
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $this->loadValidatedRelationsForQuery($validated, $schedule);
        
        return FleetScheduleResource::collection($schedule);
    }
    
    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withCarrierInformation: 0 or 1 - Return schedule with associated carrier/commander
     *                                  information.
     * withSystemInformation: 0 or 1  - Return schedule with associated departure/destination
     *                                  information.
     * 
     * @param string $slug
     * @param SearchFleetScheduleRequest $request
     * @return FleetScheduleResource|Response
     */
    public function show(string $slug, SearchFleetScheduleRequest $request): FleetScheduleResource|Response
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::whereSlug($slug)->first();
        
        if (!$schedule) {
            return response([], 404);
        }

        $this->loadValidatedRelationsForQuery($validated, $schedule);
        
        return new FleetScheduleResource($schedule);
    }
    
    /**
    * Store a newly created resource in storage.
    * 
    * @param StoreFleetScheduleRequest $request
    * @return Response
    */
    public function store(StoreFleetScheduleRequest $request): FleetScheduleResource|Response
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::create($validated);

        if (!$schedule) {
            return response([], 404);
        }
        
        return new FleetScheduleResource(
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
    public function update(string $id, Request $request): FleetScheduleResource|Response
    {
        $schedule = $request->user()->commander->schedule()->find($id);
        
        if (!$schedule) {
            return response([], 404);
        }
        
        $schedule->update($request->toArray());
        
        return new FleetScheduleResource($schedule->load('carrier.commander'));
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
        $schedule = $request->user()->commander->schedule()->find($id);
        
        if (!$schedule) {
            return response([], 404);
        }
        
        $schedule->delete();
        
        return response([
            'message' => 'Scheduled carrier trip has been deleted'
        ]);
    }
}
