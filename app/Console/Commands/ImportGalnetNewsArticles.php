<?php

namespace App\Console\Commands;

use App\Libraries\GalnetJSONParser;
use App\Libraries\GalnetRSSParser;
use Illuminate\Console\Command;

class ImportGalnetNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elite:import-galnet-news
        {--f|--format= : The format to use (rss or json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import galnet news articles from elite RSS feed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->option('format');
        
        if (!in_array($format, ['rss', 'json'])) {
            $this->output->error('format must either be rss or json');
            return false;
        }

        $this->output->info('Importing GalNet news articles, please wait...');
        
        $parser = $format === 'rss'
            ? new GalnetRSSParser(config('elite.galnet.rss'))
            : new GalnetJSONParser(config('elite.galnet.json'));

        $progress = $this->output->createProgressBar();

        $parser->import($progress);

        $this->output->info('Importing complete.');
    }
}
