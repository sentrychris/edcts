<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SystemBody',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'body_id', type: 'integer', example: 3),
        new OA\Property(property: 'name', type: 'string', example: 'Earth'),
        new OA\Property(property: 'type', type: 'string', example: 'Planet'),
        new OA\Property(property: 'sub_type', type: 'string', example: 'Earth-like world'),
        new OA\Property(property: 'distance_to_arrival', type: 'number', format: 'float', example: 500.0),
        new OA\Property(property: 'is_main_star', type: 'boolean', example: false),
        new OA\Property(property: 'is_scoopable', type: 'boolean', nullable: true),
        new OA\Property(property: 'spectral_class', type: 'string', nullable: true, example: 'G'),
        new OA\Property(property: 'luminosity', type: 'string', nullable: true, example: 'Va'),
        new OA\Property(property: 'solar_masses', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'solar_radius', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'absolute_magnitude', type: 'number', format: 'float', nullable: true),
        new OA\Property(
            property: 'discovery',
            properties: [
                new OA\Property(property: 'commander', type: 'string', nullable: true, example: 'CMDR Explorer'),
                new OA\Property(property: 'date', type: 'string', nullable: true, example: '3302-06-01'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'radius', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'gravity', type: 'number', format: 'float', nullable: true, example: 1.0),
        new OA\Property(property: 'earth_masses', type: 'number', format: 'float', nullable: true, example: 1.0),
        new OA\Property(property: 'surface_temp', type: 'number', format: 'float', nullable: true, example: 288.0),
        new OA\Property(property: 'is_landable', type: 'boolean', example: false),
        new OA\Property(property: 'atmosphere_type', type: 'string', nullable: true, example: 'Suitable for water-based life'),
        new OA\Property(property: 'volcanism_type', type: 'string', nullable: true),
        new OA\Property(property: 'terraforming_state', type: 'string', nullable: true, example: 'Already terraformed'),
        new OA\Property(
            property: 'axial',
            properties: [
                new OA\Property(property: 'axial_tilt', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'semi_major_axis', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'rotational_period', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'is_tidally_locked', type: 'boolean', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'orbital',
            properties: [
                new OA\Property(property: 'orbital_period', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'orbital_eccentricity', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'orbital_inclination', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'arg_of_periapsis', type: 'number', format: 'float', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'rings', type: 'array', items: new OA\Items(type: 'object'), nullable: true),
        new OA\Property(property: 'parents', type: 'array', items: new OA\Items(type: 'object'), nullable: true),
        new OA\Property(property: 'slug', type: 'string', example: '108086401534265707-earth'),
        new OA\Property(property: 'system', ref: '#/components/schemas/System', nullable: true),
    ]
)]
class SystemBodySchema {}
