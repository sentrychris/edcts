<?php

namespace App\Console\Commands;

use App\Services\Eddn\EddnListenerService;
use App\Services\Eddn\EddnMarketService;
use App\Services\Eddn\EddnSystemService;
use Illuminate\Console\Command;

class EddnListenCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'eddn:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to EDDN and process incoming data';

    /**
     * EDDN listener.
     */
    private EddnListenerService $eddnListenerService;

    /**
     * EDDN data management service.
     */
    private EddnSystemService $eddnSystemService;

    /**
     * EDDN data management service.
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
        $this->info('Started EDDN listener...');
        $this->eddnListenerService->listen([$this, 'processBatch']);
    }

    /**
     *  Callback to process message batches.
     *
     * @return void
     */
    public function processBatch(array $batch)
    {
        $this->eddnSystemService->process($batch);
        $this->eddnMarketService->process($batch);
    }
}
