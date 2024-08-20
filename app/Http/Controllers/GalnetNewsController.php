<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalnetNewsResource;
use App\Models\GalnetNews;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * List galnet news articles.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return GalnetNewsResource::collection(
            GalnetNews::paginate($request->get('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Display the specified resource.
     * 
     * @param string $slug
     * @return GalnetNewsResource|Response
     */
    public function show(string $slug): GalnetNewsResource|Response
    {
        $article = GalnetNews::whereSlug($slug)->first();

        if (!$article) {
            return response([], 404);
        }

        return new GalnetNewsResource($article);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id
     */
    public function destroy(string $id): Response
    {
        $article = GalnetNews::find($id);

        if  (!$article) {
            return response([], 404);
        }

        $article->delete();

        return response([
            'message' => 'Galnet news article has been successfully deleted.'
        ]);
    }
}
