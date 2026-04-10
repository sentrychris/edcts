<?php

namespace App\Traits;

use Spatie\DiscordAlerts\Facades\DiscordAlert;

trait UseDiscordAlert
{
    /**
     * Send an embed.
     * 
     * @param string $webhook - the webhook URL
     * @param string $title   - the embed title
     * @param string $message - the embed message
     * @param string|null $color - the embed color
     * @return void
     */
    public function sendDiscordAlert(
        string $webhook,
        string $title,
        string $message,
        ?string $color = '#E77625'
    ): void {
        DiscordAlert::to($webhook)->message("", $this->createEmbed($title, $message, $color));
    }

    /**
     * Create an embed for discord alerts.
     * 
     * @param string $title - the embed title
     * @param string $desc  - the embed description
     * @param string $color - the embed color
     * @return array - the embed config
     */
    public function createEmbed(string $title, string $desc, ?string $color = '#E77625'): array
    {
        return [
            [
                'title' => $title,
                'description' => $desc,
                'color' => $color,
                'author' => [
                    'name' => 'EDCS',
                    'url' => 'https://edcs.app/'
                ]    
            ]
        ];
    }
}