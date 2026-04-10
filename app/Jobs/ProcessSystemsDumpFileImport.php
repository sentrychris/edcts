<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;
use App\Models\System;
use App\Services\EdsmApiService;

class ProcessSystemsDumpFileImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public string $channel;

    /**
     * @var int
     */
    public $timeout = 0; // no timeout

    /**
     * @var int
     */
    public $tries = 10;

    /**
     * @var int
     */
    public int $batchSize = 5000;

    /**
     * @var string
     */
    protected string $file;

    /**
     * Create a new job instance.
     * 
     * @param string $channel
     * @param string $file
     */
    public function __construct(string $channel, string $file) {
        $this->channel = $channel;
        $this->file = $file;
    }

    /**
     * Execute the job.
     * 
     * @return void
     */
    public function handle(): void
    {
        $file = storage_path('dumps/' . $this->file);

        if (!file_exists($file)) {
            Log::channel($this->channel)->error('No file found at ' . $this->file);
            Log::channel($this->channel)->error('Exiting...');
            return;
        }

        Log::channel($this->channel)->info('Processing data from ' . $this->file);
        Log::channel($this->channel)
            ->info($this->file . ' (batch size: ' . number_format($this->batchSize) . '): please wait...');

        $systems = Items::fromFile($file);
        $systemBatch = [];
        $infoBatch = [];
        $count = 0;

        foreach ($systems as $system) {
            $count++;

            $systemPayload = [
                'id64'   => $system->id64,
                'name'   => $system->name,
                'coords' => json_encode($system->coords),
                'updated_at' => app(EdsmApiService::class)->formatSystemUpdateTime($system)
            ];

            if (property_isset($system, 'mainStar') && $system->mainStar !== '') {
                $systemPayload['main_star'] = $system->mainStar;
            }

            $systemBatch[] = $systemPayload;

            try {
                $infoPayload = [
                    'system_id'  => $system->id64,
                    'allegiance' => property_isset($system, 'allegiance') ? $system->allegiance : null,
                    'economy'    => property_isset($system, 'economy') ? $system->economy : null,
                    'government' => property_isset($system, 'government') ? $system->government : null,
                    'population' => property_isset($system, 'population') ? $system->population : 0,
                    'security'   => property_isset($system, 'security') ? $system->security : "None"
                ];

                if (property_isset($system, 'controllingFaction')) {
                    $faction = $system->controllingFaction;
                    $infoPayload['faction'] = property_isset($faction, 'name') ? $faction->name : null;
                    $infoPayload['faction_state'] = property_isset($faction, 'allegiance') ? $faction->allegiance : null;
                }

                $infoBatch[] = $infoPayload;
            } catch (Exception $e) {
                Log::channel($this->channel)
                    ->error('Failed to process information for ' . $system->name . ' record: ' . $e->getMessage());
            }

            if (count($systemBatch) >= $this->batchSize) {
                $this->insertBatch($systemBatch, $infoBatch);
                Log::channel($this->channel)->info('Processed batch of ' . count($systemBatch) . ' records.');

                $systemBatch = [];
                $infoBatch = [];
            }
        }

        // Insert any remaining records
        if (!empty($systemBatch)) {
            $this->insertBatch($systemBatch, $infoBatch);
            Log::channel($this->channel)->info('Processed final batch of ' . count($systemBatch) . ' records.');
        }

        Log::channel($this->channel)->info('Completed processing of ' . $count . ' records from ' . $this->file);
    }

    /**
     * Insert a batch of records and their information.
     */
    private function insertBatch(array $systemBatch, array $infoBatch): void
    {
        DB::transaction(function () use ($systemBatch, $infoBatch) {
            // Insert or update records
            $inserts = 0;
            $errors = 0;
            foreach ($systemBatch as $system) {
                if (! System::whereId64($system['id64'])->whereName($system['name'])->exists()) {
                    try {
                        $result = System::create($system);
                        if ($result) {
                            $inserts++;
                        } else {
                            Log::channel($this->channel)->error('Failed to process ' . $system['name'] . ' record: create() returned false.');
                            $errors++;
                        }
                    } catch (Exception $e) {
                        Log::channel($this->channel)->error('Failed to process ' . $system['name'] . ' record: ' . $e->getMessage());
                        $errors++;
                    }
                }
            }

            Log::channel($this->channel)->info('Processed ' . $inserts . ' records with ' . $errors . ' errors.');

            // Insert or update system information if available
            try {
                foreach ($infoBatch as $info) {
                    $system = System::where('id64', $info['system_id'])->first();
                    if ($system) {
                        $system->information()->updateOrCreate($info);
                    }
                }
            } catch (Exception $e) {
                Log::channel($this->channel)
                    ->error('Failed to insert information record for ' . $system->name .':'. $e->getMessage());
            }
        });
    }
}
