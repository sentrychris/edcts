<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Models\System;
use App\Notifications\DepartureNotification;
use App\Traits\UsesMailAPI;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class SystemController extends Controller
{
    use UsesMailAPI;

    public $mailer;

    public function __construct()
    {   
        $this->mailer = app('mail.manager');
        $this->mailer->updateMailer('api');
        
        $this->middleware([
            'transport',
            'auth:sanctum'
        ]);


    }

    /**
     * List systems.
     * 
     * User can provide the following request parameters.
     * 
     * name: - Filter systems by name.
     * withInformation: 0 or 1 - Return system with associated information.
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * withStations: 0 or 1 - Return system with associated stations and outposts.
     * withDepartures: 0 or 1 - Return systems with associated carrier departures schedule.
     * withArrivals: 0 or 1 - Return systems with associated carrier arrivals schedule.
     * exactSearch: 0 or 1 - Search for exact matches or based on a partial string.
     * limit: - page limit.
     * 
     * @param SearchSystemRequest $request
     * 
     * @return AnonymousResourceCollection
     */
    public function index(SearchSystemRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $systems = System::filter($validated, (int)$request->exactSearch)
            // ->orderBy('updated_at', 'desc')
            ->paginate($request->get('limit', config('app.pagination.limit')))
            ->appends($request->all());

        $systems = $this->loadValidatedRelations($validated, $systems);

        return SystemResource::collection($systems);
    }

    
    /**
     * Show system.
     * 
     * User can provide the following request parameters.
     * 
     * withInformation: 0 or 1 - Return system with associated information.
     * withBodies: 0 or 1 - Return system with associated celestial bodies.
     * withDepartures: 0 or 1 - Return system with associated carrier departures schedule.
     * withArrivals: 0 or 1 - Return system with associated carrier arrivals schedule.
     * 
     * @param string $slug
     * @param SearchSystemRequest $request
     * 
     * @return Response
     */
    public function show(string $slug, SearchSystemRequest $request): Response
    {
        $validated = $request->validated();
        $user = $request->user();
    
        // Attempt to retrieve system from the cache, otherwise find it and cache it for 1 hour
        $system = Cache::remember('system:'.$slug, (60*60), function() use ($slug) {
            $model = System::whereSlug($slug)->first();
            if (!$model) {
                // If the system doesn't yet exist in our database, attempt to import it from EDSM.
                $model = System::checkAPI($slug);
            }
            return $model;
        });

        if (!$system) {
            return response(null, JsonResponse::HTTP_NOT_FOUND);
        }

        // Load related data for the system depending on query parameters passed.
        $system = $this->loadValidatedRelations($validated, $system);
        $notification = new DepartureNotification($system);

        // dd($this->mailer->driver());

        $this->setPayload($request, $notification);

        if ($user) {
            Notification::send($user, new DepartureNotification($system));
        }

        return response(new SystemResource($system));
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
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations',
            'withDepartures' => 'departures.destination',
            'withArrivals' => 'arrivals.departure'
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                if ($data instanceof Model && $relation === 'bodies') {
                    // Fetches system celestial bodies e.g. stars, black holes, planets etc.
                    // Either from the database, or EDSM if the data doesn't yet exist.
                    $data->checkAPIForSystemBodies();
                }

                if ($data instanceof Model && $relation === 'information') {
                    // Fetches system information e.g. governance, economy, security etc.
                    // Either from the database, or EDSM if the data doesn't yet exist.
                    $data->checkAPIForSystemInformation();
                }

                if ($data instanceof Model && $relation === 'stations') {
                    // Fetches system stations, outposts, planetary settlements etc.
                    // Either from the database, or EDSM if the data doesn't yet exist.
                    $data->checkAPIForSystemStations();
                }

                $data->load($relation);
            }
        }

        return $data;
    }
}
