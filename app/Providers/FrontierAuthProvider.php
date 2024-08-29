<?php

namespace App\Providers;

use App\Services\Frontier\FrontierApiManager;
use Illuminate\Support\ServiceProvider;

class FrontierAuthProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FrontierApiManager::class, fn() => new FrontierApiManager());
    }
}
