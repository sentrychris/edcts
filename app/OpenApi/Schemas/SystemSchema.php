<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SystemInformation',
    properties: [
        new OA\Property(property: 'allegiance', type: 'string', example: 'Federation'),
        new OA\Property(property: 'government', type: 'string', example: 'Democracy'),
        new OA\Property(property: 'population', type: 'integer', example: 22780000000),
        new OA\Property(property: 'security', type: 'string', example: 'High'),
        new OA\Property(property: 'economy', type: 'string', example: 'Refinery'),
        new OA\Property(
            property: 'controlling_faction',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Mother Gaia'),
                new OA\Property(property: 'state', type: 'string', example: 'None'),
            ],
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'SystemCoords',
    properties: [
        new OA\Property(property: 'x', type: 'number', format: 'float', example: 0.0),
        new OA\Property(property: 'y', type: 'number', format: 'float', example: 0.0),
        new OA\Property(property: 'z', type: 'number', format: 'float', example: 0.0),
    ]
)]
#[OA\Schema(
    schema: 'System',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'id64', type: 'integer', example: 10477373803),
        new OA\Property(property: 'name', type: 'string', example: 'Sol'),
        new OA\Property(property: 'coords', ref: '#/components/schemas/SystemCoords'),
        new OA\Property(property: 'slug', type: 'string', example: '10477373803-sol'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'information', ref: '#/components/schemas/SystemInformation', nullable: true),
        new OA\Property(
            property: 'bodies',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/SystemBody'),
            nullable: true
        ),
        new OA\Property(
            property: 'stations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Station'),
            nullable: true
        ),
    ]
)]
#[OA\Schema(
    schema: 'SystemDistance',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'id64', type: 'integer', example: 10477373803),
        new OA\Property(property: 'name', type: 'string', example: 'Sol'),
        new OA\Property(property: 'coords', ref: '#/components/schemas/SystemCoords'),
        new OA\Property(property: 'distance', type: 'number', format: 'float', example: 4.38),
        new OA\Property(property: 'slug', type: 'string', example: '10477373803-sol'),
    ]
)]
#[OA\Schema(
    schema: 'SystemRouteWaypoint',
    properties: [
        new OA\Property(property: 'jump', type: 'integer', example: 0),
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'id64', type: 'integer', example: 8216113749),
        new OA\Property(property: 'name', type: 'string', example: 'Maia'),
        new OA\Property(property: 'coords', ref: '#/components/schemas/SystemCoords'),
        new OA\Property(property: 'slug', type: 'string', example: '8216113749-maia'),
        new OA\Property(property: 'distance', type: 'number', format: 'float', example: 38.2),
        new OA\Property(property: 'total_distance', type: 'number', format: 'float', example: 38.2),
    ]
)]
class SystemSchema {}
