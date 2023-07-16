<?php

return [
    'galnet' => [
        'rss' => 'https://community.elitedangerous.com/galnet-rss',
        'json' => 'https://cms.zaonce.net/en-GB/jsonapi/node/galnet_article?sort=-published_at'
    ],
    
    'inara' => 'https://inara.cz',
        
    'edsm' => [
        
        'base_url' => 'https://www.edsm.net/',
        
        'systems' => [
            'system' => 'api-v1/system',
            'systems' => 'api-v1/systems',
            'sphere' => 'api-v1/sphere-systems',
            'cube' => 'api-v1/cube-systems',
        ],
        
        
        'system' => [
            'bodies' => 'api-system-v1/bodies',
            'scan-value' => 'api-system-v1/estimated-value',
            'factions' => 'api-system-v1/factions',
            'traffic' => 'api-system-v1/traffic',
            'deaths' => 'api-system-v1/deaths',
            'stations' => [
                'stations' => 'api-system-v1/stations',
                'market' => 'api-system-v1/stations/market',
                'shipyard' => 'api-system-v1/stations/shipyard',
                'outfitting' => 'api-system-v1/stations/outfitting',
            ]
        ],
            
        'journal' => [
            'store' => 'api-journal-v1',
            'discard' => 'api-journal-v1/disard'
        ],

        'commander' => [
            'flight-log' => 'api-logs-v1/get-logs',
            'last-position' => 'api-logs-v1/get-position',
            'ranks' => 'api-commander-v1/get-ranks',
            'credits' => 'api-commander-v1/get-credits',
            'materials' => 'api-commander-v1/get-materials'
        ],

        'status' => 'api-status-v1/elite-server'
    ]
];