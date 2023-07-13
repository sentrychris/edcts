<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFleetScheduleRequest;
use App\Http\Requests\StoreFleetScheduleRequest;
use App\Http\Resources\FleetScheduleResource;
use App\Models\FleetSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Display a listing of the resource.
     * 
     * @param SearchFleetScheduleRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(SearchFleetScheduleRequest $request ): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::with('carrier.commander')->filter($validated);

        return FleetScheduleResource::collection(
            $schedule->paginate($request->get('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Display the specified resource.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $schedule = FleetSchedule::find($id);

        if (!$schedule) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json(
            new FleetScheduleResource($schedule->load('carrier.commander'))
        );
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreFleetScheduleRequest $request
     * @return JsonResponse
     */
    public function store(StoreFleetScheduleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $schedule = FleetSchedule::create($validated);

        return response()->json(
            new FleetScheduleResource($schedule->load('carrier.commander')),
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
    public function update(string $id, Request $request): JsonResponse
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
    public function destroy(string $id, Request $request): JsonResponse
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
