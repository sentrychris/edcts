<?php

namespace App\Providers;

use App\Libraries\EliteAPIManager;
use Illuminate\Support\ServiceProvider;

class EliteAPIManagerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EliteAPIManager::class, function() {
          return new EliteAPIManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
