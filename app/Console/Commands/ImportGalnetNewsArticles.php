<?php

namespace App\Console\Commands;

use App\Libraries\GalnetRSSParser;
use Illuminate\Console\Command;

class ImportGalnetNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elite:import-galnet-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports galnet news articles from elite RSS feed';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->output->info('Importing GalNet news articles, please wait...');

        $progress = $this->output->createProgressBar();
        $parser = new GalnetRSSParser(config('elite.urls.galnet'));
        $parser->import($progress);

        $this->output->info('Importing complete.');
    }
}
