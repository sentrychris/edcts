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
use App\Traits\LargeJsonFile;

class ProcessFileImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LargeJsonFile, Queueable, SerializesModels;

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
    public $tries = 100;

    /**
     * @var int
     */
    public int $batchSize = 100000;

    /**
     * @var string
     */
    protected string $file;

    /**
     * @var bool
     */
    protected bool $hasInfo;

    /**
     * @var bool
     */
    protected bool $shouldValidate;

    /**
     * Create a new job instance.
     * 
     * @param string $channel
     * @param string $file
     * @param bool $hasInfo
     * @param bool $isLargeFile
     * @param bool $shouldValidate
     */
    public function __construct(
        string $channel,
        string $file,
        bool $hasInfo = false,
        bool $isLargeFile = false,
        bool $shouldValidate = false
    ) {
        $this->channel = $channel;
        $this->file = $file;
        $this->hasInfo = $hasInfo;
        $this->shouldValidate = $shouldValidate;

        if ($isLargeFile) {
            $this->setLargeJsonFileLogChannel($this->channel);
        }
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

        if ($this->shouldValidate) {
            Log::channel($this->channel)->info('Validating ' . $this->file . ', please wait...');
            if (! $this->validateJsonFile($file)) {
                Log::channel($this->channel)->error('Validation failed for ' . $this->file);
                Log::channel($this->channel)->error('Exiting...');
                return;
            } else {
                Log::channel($this->channel)->info('Validation passed for ' . $this->file);
                Log::channel($this->channel)->info('Batch processing ' . $this->file . ', please wait...');
            }
        }

        $systems = Items::fromFile($file);
        $systemBatch = [];
        $infoBatch = [];
        $count = 0;

        foreach ($systems as $system) {
            $count++;

            $systemPayload = [
                'id64' => $system->id64,
                'name' => $system->name,
                'coords' => json_encode($system->coords),
                'updated_at' => System::getAPIUpdateTime($system)
            ];

            if (property_exists($system, 'mainStar') && $system->mainStar && $system->mainStar !== '') {
                $systemPayload['main_star'] = $system->mainStar;
            }

            $systemBatch[] = $systemPayload;

            if ($this->hasInfo) {
                $infoPayload = [
                    'system_id' => $system->id64,
                    'allegiance' => property_exists($system, 'allegiance') ? $system->allegiance : null,
                    'government' => property_exists($system, 'government') ? $system->government : null,
                    'economy' => property_exists($system, 'economy') ? $system->economy : null,
                    'population' => $system->population ?? 0,
                    'security' => $system->security
                ];

                if ($system->controllingFaction) {
                    $faction = $system->controllingFaction;
                    $infoPayload['faction'] = property_exists($faction, 'name') ? $faction->name : null;
                    $infoPayload['faction_state'] = property_exists($faction, 'allegiance') ? $faction->allegiance : null;
                }

                $infoBatch[] = $infoPayload;
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
            if ($this->hasInfo) {
                foreach ($infoBatch as $info) {
                    $system = System::where('id64', $info['system_id'])->first();
                    if ($system) {
                        $system->information()->updateOrCreate($info);
                    }
                }
            }
        });
    }
}
