<?php

return [
    /*
     * The webhook URLs that we'll use to send a message to Discord.
     */
    'webhook_urls' => [
        'monitor' => env('DISCORD_WEBHOOK_MONITOR_URL'),
        'eddn-listener' => env('DISCORD_WEBHOOK_EDDN_LISTENER_URL'),
        'pages-cache' => env('DISCORD_WEBHOOK_PAGES_CACHE_URL'),
    ],

    /*
     * This job will send the message to Discord. You can extend this
     * job to set timeouts, retries, etc...
     */
    'job' => Spatie\DiscordAlerts\Jobs\SendToDiscordChannelJob::class,
];
