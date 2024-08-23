<?php

namespace App\Console\Commands;

use App\Services\GalnetJsonService;
use App\Services\GalnetRssService;
use Illuminate\Console\Command;

class ImportGalnet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edcts:import:galnet
        {--format=json : The format to use (rss or json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import galnet articles from RSS or JSON sources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->option('format');
        
        if (!in_array($format, ['rss', 'json'])) {
            $this->error('format must either be rss or json');
            return false;
        }

        $this->info('Importing Galnet articles, please wait...');
        
        $parser = $format === 'rss'
            ? new GalnetRssService(config('elite.galnet.rss'))
            : new GalnetJsonService(config('elite.galnet.json'));

        $parser->import();

        $this->info("\nImported galnet news articles.");
    }
}
