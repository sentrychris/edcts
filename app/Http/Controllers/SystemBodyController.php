<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemBodyRequest;
use App\Http\Resources\SystemBodyResource;
use App\Models\SystemBody;
use App\Traits\HasValidatedRelations;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SystemBodyController extends Controller
{
    use HasValidatedRelations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAllowedRelations([
            'withSystem' => 'system',
            'withStations' => 'system.stations'
        ]);
    }

    /**
     * List system bodies.
     * 
     * User can provide the following request parameters.
     * 
     * system - Filter bodies by system.
     * name: - Filter bodies by name.
     * type: - Filter bodies by type.
     * withSystem: 0 or 1 - Return body with associated system.
     * withStations: 0 or 1 - Return body with associated stations and outposts.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit.
     * 
     * @param SearchSystemBodyRequest $request
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchSystemBodyRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $bodies = SystemBody::filter($validated, (int)$request->exactSearch)
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $bodies = $this->loadValidatedRelations($validated, $bodies);

        return SystemBodyResource::collection($bodies);
    }

    
    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withSystem: 0 or 1 - Return body with associated system.
     * withStations: 0 or 1 - Return body with associated stations and outposts.
     * 
     * @param string $slug
     * @param SearchSystemBodyRequest $request
     * 
     * @return Response
     */
    public function show(string $slug, SearchSystemBodyRequest $request): Response
    {
        $validated = $request->validated();
        $body = SystemBody::whereSlug($slug)->first();

        if (!$body) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        // Load related data for the system depending on query parameters passed.
        $body = $this->loadValidatedRelations($validated, $body);

        return response(new SystemBodyResource($body));
    }
}