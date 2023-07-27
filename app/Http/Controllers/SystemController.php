<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * List systems.
     * 
     * User can provide the following request parameters.
     * 
     * name: - Filter systems by name
     * 
     * withInformation: 0 or 1 - Return systems with associated information
     *                           e.g. governance, economy, security etc.
     * 
     * withBodies: 0 or 1      - Return systems with associated celestial bodies
     *                           e.g. stars, moons, planets.
     * 
     * operand: "in" or "like" - Search for exact matches or based on a partial
     *                           string.
     * 
     * limit: - page limit
     */
    public function index(SearchSystemRequest $request)
    {
        $validated = $request->validated();
        $systems = System::filter($validated, $request->get('operand', 'in'))
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        if ((int)$request->get('withInformation') === 1) {
            $systems->load('information');
        }

        if ((int)$request->get('withBodies') === 1) {
            $systems->load('bodies');
        }

        return SystemResource::collection($systems);
    }

    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withInformation: 0 or 1 - Return system with associated information
     *                           e.g. governance, economy, security etc.
     * 
     * withBodies: 0 or 1      - Return system with associated celestial bodies
     *                           e.g. stars, moons, planets.
     */
    public function show(string $slug, Request $request)
    {
        $source = 'edsm';
        $system = System::whereSlug($slug)->first();

        if (!$system) {
            $system = System::importfromAPI($source, $slug);
        }

        if (!$system) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }
        
        $system->checkForSystemInformation($source)
            ->checkForSystemBodies($source);

        if ((int)$request->get('withInformation') === 1) {
            $system->load('information');
        }

        if ((int)$request->get('withBodies') === 1) {
            $system->load('bodies');
        }

        return response()->json(
            new SystemResource($system)
        );
    }
}
