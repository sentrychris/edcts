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
    * Display a listing of the resource.
    */
    public function index(SearchSystemRequest $request)
    {
        $validated = $request->validated();
        $systems = System::with('information')
            ->filter($validated, $request->get('operand', 'in'));

        return SystemResource::collection(
            $systems->paginate($request->get('limit', config('app.pagination.limit')))
                ->appends($request->all())
        );
    }

    /**
    * Display the specified resource.
    */
    public function show(string $id)
    {
        $system = System::find($id);

        if (!$system) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json(
            new SystemResource($system->load('information'))
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
}
