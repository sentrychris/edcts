<?php

namespace App\Console\Commands;

use App\Services\GalnetNewsService;
use Illuminate\Console\Command;

class ImportGalnet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edcts:import:galnet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import galnet news articles';

    /**
     * The injected Galnet service
     */
    private GalnetNewsService $galnetService;

    public function __construct(GalnetNewsService $galnetService)
    {
        $this->galnetService = $galnetService;
        return parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Importing Galnet articles, please wait...');
        $numArticles = $this->galnetService->import();
        $this->info("Imported $numArticles galnet news articles.");
    }
}
