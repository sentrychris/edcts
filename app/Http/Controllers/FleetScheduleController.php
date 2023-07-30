<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetScheduleRequest;
use App\Http\Requests\StoreFleetScheduleRequest;
use App\Http\Resources\FleetScheduleResource;
use App\Models\FleetSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

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
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchFleetScheduleRequest $request): AnonymousResourceCollection
    {        
        $validated = $request->validated();
        $schedule = FleetSchedule::filter($validated, (int)$request->exactSearch)
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $this->loadValidatedRelations($validated, $schedule);
        
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
     * 
     * @return Response
     */
    public function show(string $slug, SearchFleetScheduleRequest $request): Response
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::whereSlug($slug)->first();
        
        if (!$schedule) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->loadValidatedRelations($validated, $schedule);
        
        return response(new FleetScheduleResource($schedule));
    }
    
    /**
    * Store a newly created resource in storage.
    * 
    * @param StoreFleetScheduleRequest $request

    * @return Response
    */
    public function store(StoreFleetScheduleRequest $request): Response
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::create($validated);
        
        return response(
            new FleetScheduleResource($schedule->load(['carrier.commander', 'departure', 'destination'])),
            JsonResponse::HTTP_CREATED
        );
    }
    
    /**
    * Update the specified resource in storage.
    * 
    * @param string $id
    * @param Request $request

    * @return Response
    */
    public function update(string $id, Request $request): Response
    {
        $schedule = $request->user()->commander->schedule()->find($id);
        
        if (!$schedule) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }
        
        $schedule->update($request->toArray());
        
        return response(
            new FleetScheduleResource($schedule->load('carrier.commander'))
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
        $schedule = $request->user()->commander->schedule()->find($id);
        
        if (!$schedule) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }
        
        $schedule->delete();
        
        return response([
            'message' => 'Scheduled carrier trip has been deleted'
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
            'withCarrierInformation' => 'carrier.commander',
            'withSystemInformation' => ['departure.information', 'destination.information'],
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                $data->load($relation);
            }
        }

        return $data;
    }
}
