<?php

namespace App\Console\Commands;

use App\Models\System;
use App\Traits\HasValidatedRelations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PreCacheSystems extends Command
{
    use HasValidatedRelations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "edcts:precache:systems
        {--flush : Flush systems from the cache before pre-caching.}
        {--ttl=3600 : Time to live (default: 3600).}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Pre-cache system pages for the frontend";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Pre-caching system pages for the frontend...");

        $params = [
            "withArrivals"    => 1,
            "withBodies"      => 1,
            "withDepartures"  => 1,
            "withInformation" => 1,
            "withStations"    => 1,
        ];

        $this->line("\nQuery parameters: " . json_encode($params, 128));
        $this->info("\nCounting number of pages to cache, please wait...");

        $count = System::count();
        $limit = config("app.pagination.limit");
        $pages = ceil($count / $limit);

        $this->line("Systems: " . number_format($count));
        $this->line("Number per page: " . number_format($limit));
        $this->line("Number of pages: " . number_format($pages));

        $this->info("\nPre-caching pages, please wait...");
        $bar = $this->output->createProgressBar($pages);

        for ($page = 1; $page <= $pages; $page++) {
            $key = "systems_page_{$page}";

            if ($this->option('flush')) {
                Cache::forget($key);
            }

            $value = System::filter($params, 0)
                ->paginate($limit)
                ->appends(array_merge($params, ["page" => $page]));

            Cache::set($key, $value, (int)$this->option('ttl'));

            $bar->advance();
        }

        $bar->finish();

        $this->info("\nPre-caching is complete!");
    }
}
