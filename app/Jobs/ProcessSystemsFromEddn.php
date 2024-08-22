<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;
use App\Models\System;
use App\Services\EdsmApiService;
use Illuminate\Support\Facades\Redis;

class ProcessSystemsFromEddn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private string $channel;

    /**
     * @var EdsmApiService
     */
    private EdsmApiService $edsmApiService;

    /**
     * @var int
     */
    public $timeout = 0; // no timeout

    /**
     * @var int
     */
    public $tries = 10;

    /**
     * Create a new job instance.
     * 
     * @param string $channel
     * @param string $file
     * @param bool $shouldValidate,
     */
    public function __construct(
        string $channel,
        EdsmApiService $service
    ) {
        $this->channel = $channel;
        $this->edsmApiService = $service;
    }

    /**
     * Execute the job.
     * 
     * @return void
     */
    public function handle(): void
    {
        $cachedSystems = Redis::smembers("eddn_system_scans");

        if (empty($cachedSystems)) {
            Log::info("No systems to process from EDDN.");
            return;
        }

        foreach($cachedSystems as $systemId64WithName) {
            $system = $this->edsmApiService->updateSystemData($systemId64WithName);
            if ($system instanceof System) {
                Redis::srem("eddn_system_scans", $systemId64WithName);

                $this->edsmApiService->updateSystemInformationData($system);
                $this->edsmApiService->updateSystemBodiesData($system);
                $this->edsmApiService->updateSystemStationsData($system);
            }

            sleep(60);
        }
    }
}
