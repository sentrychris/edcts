<?php

namespace App\Console\Commands;

use App\Services\StatService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CacheStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:stats
        {--ttl=3600 : Time to live (default: 3600)}
        {--flush= : Force flush}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the statistics cache';

    /**
     * The injected statistics service.
     */
    private StatService $statService;

    /**
     * Constructor
     */
    public function __construct(StatService $statService)
    {
        $this->statService = $statService;

        return parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ttl = $this->option('ttl') ?? 60;

        return $this->runCache([
            'ttl' => (int) $ttl,
            'flushCache' => $this->hasOption('flush'),
        ]);
    }

    private function runCache(array $options)
    {
        try {
            $this->statService->fetch('statistics', $options);
            $this->info('Statistics refreshed.');

            return 0;
        } catch (Exception $e) {
            Log::channel('statistics:cache')->error($e->getMessage());
            $this->error($e->getMessage());

            return 1;
        }
    }
}
