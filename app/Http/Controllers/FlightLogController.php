<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FlightLogRequest;
use App\Http\Resources\FlightLogResource;

class FlightLogController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'has.cmdr']);
    }

    /**
     * Get commander flight log.
     * 
     * @param Request
     */
    public function index(Request $request)
    {
        try {
            $commander = $request->user()->commander;
            $flightLog = $commander->flightLog->load('systemInformation');

            return response(FlightLogResource::collection($flightLog));
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Import commander flight log from EDSM.
     * 
     * User can provide the following request parameters.
     * 
     * startDateTime: - start of flight log.
     * endDateTime: - end of flight log.
     * NOTE: The maximum interval is 1 week
     * 
     * @param FlightLogRequest $request
     */
    public function store(FlightLogRequest $request)
    {
        try {
            $commander = $request->user()->commander;

            // TODO - this will currently loop through the flight log response
            // and check to see if each system is in the database, if it is not,
            // it will make another request to EDSM to fetch the system data, this
            // could hit rate limits pretty fast, so this will need to be deferred to
            // a background queue and processed in batches
            $commander->importFlightLogFromEDSM(
                $request->get('startDateTime'),
                $request->get('endDateTime'),
            );

            return response()->json([
                'data' => $commander->flightLog
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
