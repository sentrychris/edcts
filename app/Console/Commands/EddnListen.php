<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Eddn\EddnListener;
use App\Services\Eddn\EddnSystemService;

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
     * @var EddnListener
     */
    private EddnListener $eddnListener;

    /**
     * EDDN data management service.
     * 
     * @var EddnSystemService
     */
    private EddnSystemService $eddnSystemService;
    
    public function __construct(EddnListener $eddnListener, EddnSystemService $eddnSystemService)
    {
        parent::__construct();

        $this->eddnListener = $eddnListener;
        $this->eddnSystemService = $eddnSystemService;
    }

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle()
    {    
        $this->info("Starting EDDN listener...");
        $this->eddnListener->process([$this, "processBatch"]);
    }

    /**
     *  Callback to process message batches.
     * 
     * @param array $data
     * @return void
     */
    public function processBatch(array $data)
    {
        $this->eddnSystemService->updateLastTenNavRoutes($data);
        $this->eddnSystemService->updateSystemsData($data);
    }
}
