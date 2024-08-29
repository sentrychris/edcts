<?php

namespace App\Providers;

use App\Services\Frontier\FrontierAuthService;
use Illuminate\Support\ServiceProvider;

class FrontierAuthProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FrontierAuthService::class, fn() => new FrontierAuthService());
    }
}
