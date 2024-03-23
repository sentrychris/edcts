<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\FlightLogRequest;
use App\Models\Commander;

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
     * Get commander flight log
     * 
     * @param Request
     */
    public function index(FlightLogRequest $request)
    {
        $request->validated();

        try {
            $commander = $request->user()->commander;
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
            ], 400);
        }
    }
}
