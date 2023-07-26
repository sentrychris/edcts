<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchSystemRequest;
use App\Http\Resources\SystemResource;
use App\Libraries\EliteAPIManager;
use App\Models\System;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    private EliteAPIManager $api;

    public function __construct(EliteAPIManager $api) {
        $this->api = $api;
    }

    /**
    * Display a listing of the resource.
    */
    public function index(SearchSystemRequest $request)
    {
        $validated = $request->validated();
        $systems = System::with(['information', 'bodies'])
            ->filter($validated, $request->get('operand', 'in'));

        return SystemResource::collection(
            $systems->paginate($request->get('limit', config('app.pagination.limit')))
                ->appends($request->all())
        );
    }

    /**
    * Display the specified resource.
    */
    public function show(string $slug)
    {
        $system = System::whereSlug($slug)->first();

        if (!$system) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->checkForSystemInformation($system)
            ->checkForSystemBodies($system);

        return response()->json(
            new SystemResource($system->load(['information', 'bodies']))
        );
    }
    
    /**
    * Update the specified resource in storage.
    */
    public function update(Request $request, string $id)
    {
        //
    }
    
    /**
    * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        //
    }

    private function checkForSystemInformation(System $system)
    {
        if (!$system->information()->exists()) {
            $response =$this->api->setConfig(config('elite.edsm'))
                ->setCategory('systems')
                ->get('system', [
                    'systemName' => $system->name,
                    'showInformation' => true
                ]);

            if ($response->information) {
                $data = [];
                $this->api->convertResponse($response->information, $data);
                $system->information()->updateOrCreate($data);
            }
        }

        return $this;
    }

    private function checkForSystemBodies(System $system)
    {
        if (!$system->bodies()->exists()) {
            $response =$this->api->setConfig(config('elite.edsm'))
                ->setCategory('system')
                ->get('bodies', [
                    'systemName' => $system->name
                ]);

            $bodies = $response->bodies;

            if ($bodies) {
                foreach($bodies as $body) {
                    $system->bodies()->updateOrCreate([
                        'id64' => $body->id64,
                        'name' => $body->name,
                        'discovered_by' => $body->discovery->commander,
                        'discovered_at' => $body->discovery->date,
                        'type' => $body->type,
                        'sub_type' => $body->subType
                    ]);
                }
            }
        }

        return $this;
    }
}
