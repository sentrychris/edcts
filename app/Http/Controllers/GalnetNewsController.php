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
     * Display a galnet news article.
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
}
