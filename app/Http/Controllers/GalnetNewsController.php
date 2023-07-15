<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalnetNewsResource;
use App\Models\GalnetNews;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalnetNewsController extends Controller
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
     */
    public function index(Request $request)
    {        
        return GalnetNewsResource::collection(
            GalnetNews::paginate($request->get('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = GalnetNews::find($id);

        if  (!$article) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json($article);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        $article = GalnetNews::find($id);

        if  (!$article) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $article->delete();

        return response()->json([
            'message' => 'Galnet news article has been successfully deleted.'
        ]);
    }
}
