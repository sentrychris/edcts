<?php

namespace App\Http\Controllers;

use App\Services\StatService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class StatisticsController extends Controller
{
    private StatService $statService;

    /**
     * Constructor
     */
    public function __construct(StatService $statService)
    {
        $this->statService = $statService;
    }

    /**
     * Get statistics.
     *
     * Statistics are cached and refreshed every hour through the artisan
     * scheduler.
     */
    #[OA\Get(
        path: '/statistics',
        summary: 'Get aggregate database statistics',
        description: 'Returns counts of systems, bodies, and stations. Results are cached and refreshed hourly by the scheduler.',
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'resetCache',
                in: 'query',
                required: false,
                description: 'Pass 1 to force a cache refresh',
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/Statistics')
            ),
        ]
    )]
    public function index(Request $request): Response
    {
        return response([
            'data' => $this->statService->fetch('statistics', $request->all()),
        ]);
    }
}
