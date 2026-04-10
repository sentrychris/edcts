<?php

namespace App\Console\Commands;

use App\Services\Eddn\EddnListenerService;
use App\Services\Eddn\EddnSystemService;
use App\Services\Eddn\EddnMarketService;
use Illuminate\Console\Command;

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
    protected $description = "Listen to EDDN and process incoming data";

    /**
     * EDDN listener.
     * 
     * @var EddnListenerService
     */
    private EddnListenerService $eddnListenerService;

    /**
     * EDDN data management service.
     * 
     * @var EddnSystemService
     */
    private EddnSystemService $eddnSystemService;

    /**
     * EDDN data management service.
     * 
     * @var EddnMarketService
     */
    private EddnMarketService $eddnMarketService;
    
    public function __construct(
        EddnListenerService $eddnListenerService,
        EddnSystemService $eddnSystemService,
        EddnMarketService $eddnMarketService
    ) {
        $this->eddnListenerService = $eddnListenerService;
        $this->eddnSystemService = $eddnSystemService;
        $this->eddnMarketService = $eddnMarketService;

        return parent::__construct();
    }

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle()
    {    
        $this->info("Started EDDN listener...");
        $this->eddnListenerService->listen([$this, "processBatch"]);
    }

    /**
     *  Callback to process message batches.
     * 
     * @param array $data
     * @return void
     */
    public function processBatch(array $data)
    {
        $this->eddnSystemService->process($data);
        $this->eddnMarketService->process($data);
    }
}
