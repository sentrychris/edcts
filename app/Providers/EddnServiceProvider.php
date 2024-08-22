<?php

namespace App\Providers;

use App\Services\Eddn\EddnListener;
use App\Services\Eddn\EddnSystemService;
use Illuminate\Support\ServiceProvider;

class EddnServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EddnListener::class, fn() => new EddnListener());
        $this->app->bind(EddnSystemService::class, fn() => new EddnSystemService());
    }
}
