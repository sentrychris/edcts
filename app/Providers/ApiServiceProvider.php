<?php

namespace App\Providers;

use App\Services\EdsmApiService;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EdsmApiService::class, fn() => new EdsmApiService());
    }
}
