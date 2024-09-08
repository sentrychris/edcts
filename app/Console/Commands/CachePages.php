<?php

namespace App\Console\Commands;

use App\Jobs\PreCacheSystemsPages;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Console\Command;

class CachePages extends Command
{
    use HasValidatedQueryRelations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "edcts:cache:pages
        {--type= : The type of data to cache.}
        {--pages= : The number of pages to cache.}
        {--channel=pages:cache : The log channel for the dispatch job.}
        {--flush : Force flush the cache before caching.}
        {--ttl=3600 : Time to live (default: 3600).}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Cache pages for the frontend";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ttl = (int)$this->option('ttl');
        $pages = (int)$this->option('pages');

        // Check type aliases for the system cache warm-up job
        if (in_array($this->option('type'), ['sys', 'system', 'systems'])) {
            $this->info('Dispatching job to warm up system pages cache...');

            PreCacheSystemsPages::dispatch(
                $this->option('channel'),
                $this->option('flush'),
                $pages,
                $ttl
            );
        } else {
            $this->error('Type does not match a valid cache job type.');
        }

        // More types can be added here...

        $this->info("Job dispatched!");
    }
}
