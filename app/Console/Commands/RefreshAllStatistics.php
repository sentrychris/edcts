<?php

namespace App\Console\Commands;

use App\Http\Resources\SystemResource;
use App\Models\Commander;
use App\Models\FleetCarrier;
use App\Models\FleetSchedule;
use App\Models\System;
use App\Models\SystemBody;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Support\Facades\Log;
use Exception;

class RefreshAllStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edcts:refresh-all-statistics
        {--ttl= : Time to live}
        {--flush= : Force flush}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh EDCTS statistics';

    /**
     * @var string
     */
    private $cacheKey = 'edcts:statistics';

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
            if ($options['resetCache']) {
                Cache::forget($this->cacheKey);
            }

            Cache::remember($this->cacheKey, $options['ttl'], function () {
                $latestSystem = System::with(['information'])
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestSystem instanceof System) {
                    $latestSystem
                        ->checkAPIForSystemInformation()
                        ->checkAPIForSystemBodies();
                }

                $data = [
                    'cartographical' => [
                        'systems' => System::count(),
                        'bodies' => SystemBody::count(),
                        'stars' => SystemBody::whereType('Star')->count(),
                        'orbiting' => SystemBody::whereType('planet')->count(),
                        'latest_system' => new SystemResource($latestSystem->load(['information', 'bodies'])),
                    ],
                    
                    'carriers' => FleetCarrier::count(),
                    
                    'commanders' => Commander::count(),

                    'journeys' => [
                        'total' => FleetSchedule::count(),
                        'boarding' => FleetSchedule::whereIsBoarding(1)->count(),
                        'cancelled' => FleetSchedule::whereIsCancelled(1)->count(),
                        'leaving_in' => [
                            'two_days' => FleetSchedule::leavingInNextNDays(2),
                            'one_week' => FleetSchedule::leavingInNextNDays(7),
                            'one_month' => FleetSchedule::leavingInNextNDays(31),
                            'six_months' => FleetSchedule::leavingInNextNDays(31*6),
                        ]
                    ]
                ];

                return $data;
            });

            $this->info('Statistics refreshed.');

            return 0;
        } catch (Exception $e) {
            Log::channel('statistics:cache')->error($e->getMessage());
            $this->error($e->getMessage());
            
            return 1;
        }
    }
}
