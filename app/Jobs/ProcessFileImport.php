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

class ProcessFileImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public string $job;

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
     * Create a new job instance.
     * 
     * @param string $job
     * @param string $file
     * @param bool $hasInfo
     */
    public function __construct(string $job, string $file, bool $hasInfo = false)
    {
        $this->job = $job;
        $this->file = $file;
        $this->hasInfo = $hasInfo;
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
            Log::error('No file found at ' . $this->file);
            return;
        }

        Log::channel('import:system')->info('Starting import from ' . $this->file);

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
                'updated_at' => $this->getUpdateTime($system)
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
                Log::channel('import:system')->info('Processed batch of ' . count($systemBatch) . ' systems.');

                $systemBatch = [];
                $infoBatch = [];
            }
        }

        // Insert any remaining records
        if (!empty($systemBatch)) {
            $this->insertBatch($systemBatch, $infoBatch);
            Log::channel('import:system')->info('Processed final batch of ' . count($systemBatch) . ' systems.');
        }

        Log::channel('import:system')->info('Completed import of ' . $count . ' systems from ' . $this->file);
    }

    /**
     * Insert a batch of systems and their information.
     */
    private function insertBatch(array $systemBatch, array $infoBatch): void
    {
        DB::transaction(function () use ($systemBatch, $infoBatch) {
            // Insert or update systems
            $inserts = 0;
            $errors = 0;
            foreach ($systemBatch as $system) {
                if (! System::whereId64($system['id64'])->whereName($system['name'])->exists()) {
                    try {
                        $result = System::create($system);
                        if ($result) {
                            $inserts++;
                        } else {
                            Log::channel('import:system')->error('Failed to import ' . $system['name'] . ' system: create() returned false.');
                            $errors++;
                        }
                    } catch (Exception $e) {
                        Log::channel('import:system')->error('Failed to import ' . $system['name'] . ' system: ' . $e->getMessage());
                        $errors++;
                    }
                }
                // System::updateOrCreate(
                //     ['id64' => $system['id64'], 'name' => $system['name']],
                //     $system
                // );
            }

            Log::channel('import:system')->info('Inserted ' . $inserts . ' systems with ' . $errors . ' errors.');

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

    /**
     * Get update time according to various 3rd party formats.
     */
    private function getUpdateTime($system): mixed
    {
        // Spansh dumps
        if (property_exists($system, 'updateTime')
            && is_string($system->updateTime)
            && $system->updateTime
        ) {
            if (str_contains($system->updateTime, '+')) {
                return substr($system->updateTime, 0, strpos($system->updateTime, '+'));
            }

            return $system->updateTime;
        }

        // EDSM dumps
        if (property_exists($system, 'updateTime')
            && is_object($system->updateTime)
            && $system->updateTime->information
        ) {
            return $system->updateTime->information;
        }

        return now();
    }
}
