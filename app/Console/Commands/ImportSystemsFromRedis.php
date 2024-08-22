<?php

namespace App\Console\Commands;

use App\Models\System;
use App\Services\EdsmApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ImportSystemsFromRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edcts:import-systems-from-redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import systems from redis cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cacheKey = "eddn_systems_from_listener";
        $cachedSystems = Redis::smembers($cacheKey);
        $edsmApiService = app(EdsmApiService::class);

        if (empty($cachedSystems)) {
            $this->info("No systems to process from Redis {$cacheKey}.");
            return;
        }

        $this->info("Processing systems from Redis {$cacheKey}.");

        $count = 0;
        $inserts = 0;
        foreach($cachedSystems as $systemId64WithName) {
            $systemParts = explode('-', $systemId64WithName, 2);
            $systemId64 = $systemParts[0];
            $systemName = $systemParts[1];

            $systemRecordExists = System::whereId64($systemId64)
                ->whereName($systemName)
                ->exists();

            if ($systemRecordExists) {
                continue;
            }

            $system = $edsmApiService->updateSystemData($systemId64WithName);
            if ($system !== false && $system instanceof System) {
                $this->info("Record for {$system->name} successfully updated.");
                Redis::srem("eddn_systems_from_listener", $systemId64WithName);
                $inserts++;
            }

            // Be mindful of the rate limits on the EDSM API
            if ($count === 50) {
                $this->line("Sleeping for 60 seconds...");
                sleep(60);
            }

            $count++;
            sleep(2);
        }

        $this->info("Processed {$inserts} systems from EDDN.");
    }
}
