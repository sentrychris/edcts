<?php

namespace App\Console\Commands;

use App\Traits\UsesStatistics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class RefreshAllStatistics extends Command
{

    use UsesStatistics;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edcts:stats:refresh
        {--ttl=3600 : Time to live (default: 3600)}
        {--flush= : Force flush}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh EDCTS statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ttl = $this->option('ttl') ?? 60;

        return $this->runCache([
            'ttl' => (int) $ttl,
            'resetCache' => $this->hasOption('flush')
        ]);
    }

    private function runCache(array $options)
    {       
        try {
            $this->getAllStatistics("statistics", $options);
            $this->info('Statistics refreshed.');

            return 0;
        } catch (Exception $e) {
            Log::channel('statistics:cache')->error($e->getMessage());
            $this->error($e->getMessage());
            
            return 1;
        }
    }
}
