<?php

namespace App\Console\Commands;

use App\Jobs\PreCacheSystems;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Console\Command;

class PreCacheData extends Command
{
    use HasValidatedQueryRelations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "edcts:precache:data
        {--type= : The type of data to pre-cache.}
        {--channel= : The log channel for the dispatch job.}
        {--flush : Force flush the cache before pre-caching.}
        {--ttl=3600 : Time to live (default: 3600).}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Pre-cache data for the frontend";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ttl = (int)$this->option('ttl');

        // Check type aliases for the system pre-cache job
        if (in_array($this->option('type'), ['sys', 'system', 'systems'])) {
            $this->info('Dispatching job to pre-cache system pages...');

            PreCacheSystems::dispatch(
                $this->option('channel'),
                $this->option('flush'),
                $ttl
            );
        } else {
            $this->error('Type does not match a valid pre-cache job type.');
        }

        // More types can be added here...

        $this->info("Job dispatched!");
    }
}
