<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCommanderRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CommanderController extends Controller
{
    /**
     * Update the authenticated commander's third-party API keys.
     */
    #[OA\Put(
        path: '/commander',
        summary: 'Update commander third-party API keys',
        description: 'Updates the Inara and/or EDSM API keys for the authenticated commander.',
        security: [['sanctum' => []]],
        tags: ['Commander'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'inara_api_key', type: 'string', nullable: true, example: 'abc123'),
                    new OA\Property(property: 'edsm_api_key', type: 'string', nullable: true, example: 'xyz789'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commander updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Commander updated successfully'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'No associated commander'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateCommanderRequest $request): JsonResponse
    {
        $commander = $request->user()->commander;

        $commander->update($request->validated());

        return response()->json([
            'message' => 'Commander updated successfully',
        ]);
    }
}
