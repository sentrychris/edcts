<?php

namespace App\Traits;

use Spatie\DiscordAlerts\Facades\DiscordAlert;

trait UseDiscordAlert
{
    /**
     * Create an embed for discord alerts.
     * 
     * @param array $config
     * @return array
     */
    public function createEmbed(array $config = ['title' => 'EDCS', 'description' => 'EDCS Alerts'])
    {
        return [
            [
                'title' => $config['title'],
                'description' => $config['description'],
                'color' => '#E77625',
                'author' => [
                    'name' => 'EDCS',
                    'url' => 'https://edcs.app/'
                ]    
            ]
        ];
    }
}