<?php

namespace App\Console\Commands;

use App\Services\Eddn\EddnListener;
use Illuminate\Console\Command;
use App\Services\Eddn\EddnService;
use Illuminate\Support\Facades\Redis;

class EddnListen extends Command
{
    /**
     * The console command signature.
     * 
     * @var string
     */
    protected $signature = "eddn:listen";

    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = "Listen to EDDN and import system data";

    /**
     * EDDN listener.
     * 
     * @var EddnListener
     */
    private EddnListener $eddnListener;

    /**
     * EDDN data management service.
     * 
     * @var EddnService
     */
    private EddnService $eddnService;
    
    public function __construct(EddnListener $eddnListener, EddnService $eddnService)
    {
        parent::__construct();

        $this->eddnListener = $eddnListener;
        $this->eddnService = $eddnService;
    }

    public function handle()
    {    
        $this->info("Starting EDDN listener...");
        Redis::del("eddn_systems_not_inserted");
        $this->eddnListener->collectMessagesForBatchProcess([$this, "processBatch"]);
    }

    /**
     *  Callback to process message batches.
     * 
     * @param array $data
     * @return void
     */
    public function processBatch(array $data)
    {
        $this->eddnService->updateSystemsData($data);
    }
}
