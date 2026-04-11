<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthToken',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Chris Rowles'),
        new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
        new OA\Property(property: 'expiry', type: 'integer', example: 3600),
    ]
)]
#[OA\Schema(
    schema: 'Commander',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'CMDR Rowles'),
        new OA\Property(
            property: 'api',
            nullable: true,
            properties: [
                new OA\Property(property: 'edsm', type: 'string', nullable: true),
                new OA\Property(property: 'inara', type: 'string', nullable: true),
            ],
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Chris Rowles'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'me@rowles.ch'),
        new OA\Property(property: 'commander', ref: '#/components/schemas/Commander', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'GalnetNews',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 42),
        new OA\Property(property: 'title', type: 'string', example: 'The Assault on Thor'),
        new OA\Property(property: 'content', type: 'string', example: '<p>In a bold manoeuvre...</p>'),
        new OA\Property(property: 'audio_file', type: 'string', nullable: true, example: 'https://audio.example.com/file.mp3'),
        new OA\Property(property: 'uploaded_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'banner_image', type: 'string', nullable: true),
        new OA\Property(property: 'slug', type: 'string', example: '16-aug-3310-the-assault-on-thor'),
    ]
)]
#[OA\Schema(
    schema: 'Statistics',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'systems', type: 'integer', example: 123456),
                new OA\Property(property: 'bodies', type: 'integer', example: 789012),
                new OA\Property(property: 'stations', type: 'integer', example: 34567),
            ],
            type: 'object'
        ),
    ]
)]
class AuthSchema {}
