<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;
use App\Models\System;

class ImportGalaxySystemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 21600;

    /**
     * @var int
     */
    public $tries = 5;

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
     */
    public function __construct(string $file, bool $hasInfo = false)
    {
        $this->file = $file;
        $this->hasInfo = $hasInfo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $file = storage_path('dumps/' . $this->file);

        if (!file_exists($file)) {
            Log::error('No file found at ' . $this->file);
            return;
        }

        $systems = Items::fromFile($file);
        foreach ($systems as $system) {
            // Initialize systemExists to false
            $systemExists = false;

            // Check if system already exists
            $record = System::whereId64($system->id64)
                ->where('name', $system->name)
                ->first();

            if (!$record) {
                // Construct payload for system import
                $payload = [
                    'id64' => $system->id64,
                    'name' => $system->name,
                    'coords' => json_encode($system->coords),
                    'updated_at' => $this->getUpdateTime($system)
                ];

                // Add system main star if exists
                if (property_exists($system, 'mainStar') && $system->mainStar && $system->mainStar !== '') {
                    $payload['main_star'] = $system->mainStar;
                }

                try {
                    // Create new system record
                    $record = new System($payload);
                    $record->save();
                    // Set systemExists to true afer import
                    $systemExists = true;
                } catch (Exception $e) {
                    Log::channel('system')->error($e->getMessage());
                }
            } else {
                // Already imported
                $systemExists = true;
                Log::channel('system')->info($system->name . ' already imported');
            }

            // If the system exists in the database and the import file has system information
            if ($this->hasInfo && $systemExists) {
                // Construct payload for system information import
                $payload = [
                    'allegiance' => property_exists($system, 'allegiance') ? $system->allegiance : null,
                    'government' => property_exists($system, 'government') ? $system->government : null,
                    'economy' => property_exists($system, 'economy') ? $system->economy : null,
                    'population' => $system->population ?? 0,
                    'security' => $system->security
                ];
    
                // Add system controlling faction if it is present
                if ($system->controllingFaction) {
                    $faction = $system->controllingFaction;
                    $payload['faction'] = property_exists($faction, 'name') ? $faction->name : null;
                    $payload['faction_state'] = property_exists($faction, 'allegiance') ? $faction->allegiance : null;
                }
    
                try {
                    // Update or create system information
                    $record->information()->updateOrCreate($payload);
                } catch (Exception $e) {
                    Log::channel('system')->error($e->getMessage());
                }
            }
        }
    }

    /**
     * Get update time according to various 3rd part formats
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
            return $system->updaeTime->information;
        }

        // Default
        return now();
    }
}
