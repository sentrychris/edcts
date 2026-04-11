<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalnetNewsResource;
use App\Models\GalnetNews;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class GalnetNewsController extends Controller
{
    /**
     * List galnet news articles.
     */
    #[OA\Get(
        path: '/galnet/news',
        summary: 'List GalNet news articles',
        description: 'Returns a paginated list of in-game GalNet news articles, most recent first.',
        tags: ['GalNet'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Number of results per page', schema: new OA\Schema(type: 'integer', example: 15)),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of GalNet articles',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/GalnetNews')
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return GalnetNewsResource::collection(
            GalnetNews::paginate($request->input('limit', config('app.pagination.limit')))
        );
    }

    /**
     * Display a galnet news article.
     */
    #[OA\Get(
        path: '/galnet/news/{slug}',
        summary: 'Get a single GalNet news article by slug',
        tags: ['GalNet'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                description: 'Article slug in format {date}-{title-kebab}, e.g. 16-aug-3310-the-assault-on-thor',
                schema: new OA\Schema(type: 'string', example: '16-aug-3310-the-assault-on-thor')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'GalNet article',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/GalnetNews'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Article not found'),
        ]
    )]
    public function show(string $slug): GalnetNewsResource|Response
    {
        $article = GalnetNews::whereSlug($slug)->first();

        if (! $article) {
            return response([], 404);
        }

        return new GalnetNewsResource($article);
    }

    #[OA\Delete(
        path: '/galnet/news/{id}',
        summary: 'Delete a GalNet news article',
        security: [['sanctum' => []]],
        tags: ['GalNet'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Article ID', schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Article deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Article not found'),
        ]
    )]
    public function destroy(GalnetNews $news)
    {
        $news->delete();

        return response(null, 204);
    }
}
