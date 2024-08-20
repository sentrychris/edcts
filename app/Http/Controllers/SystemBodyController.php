<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemBodyRequest;
use App\Http\Resources\SystemBodyResource;
use App\Models\SystemBody;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Http\Response;

class SystemBodyController extends Controller
{
    use HasValidatedQueryRelations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAllowedQueryRelations([
            'withSystem' => 'system',
            'withStations' => 'system.stations'
        ]);
    }

    
    /**
     * Show system body.
     * 
     * User can provide the following request parameters.
     * 
     * withSystem: 0 or 1 - Return body with associated system.
     * withStations: 0 or 1 - Return body with associated stations and outposts.
     * 
     * @param string $slug
     * @param SearchSystemBodyRequest $request
     * @return SystemBodyResource|Response
     */
    public function show(string $slug, SearchSystemBodyRequest $request): SystemBodyResource|Response
    {
        $body = SystemBody::whereSlug($slug)->first();

        if (!$body) {
            return response([], 404);
        }

        // Load related data for the system depending on query parameters passed.
        $body = $this->loadValidatedRelationsForQuery($request->validated(), $body);

        return new SystemBodyResource($body);
    }
}