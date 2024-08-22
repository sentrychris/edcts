<?php

namespace App\Providers;

use App\Services\Eddn\EddnJournalService;
use App\Services\Eddn\EddnService;
use Illuminate\Support\ServiceProvider;

class EddnServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EddnService::class, fn() => new EddnService());
    }
}
