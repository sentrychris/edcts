<?php

namespace App\Console\Commands;

use App\Models\System;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Items;

class ImportGalaxySystems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elite:import-galaxy-system
        {--f|--file= : The dump file (located from storage/dumps/)}
        {--i|--has-info : Provide system information if attached in dump file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import galaxy systems from dump files (large datasets)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = storage_path('dumps/' . $this->option('file'));

        if (!file_exists($file)){
            $this->error('No file found at ' .  $this->option('file'));
        }

        $systems = Items::fromFile($file);
        foreach ($systems as $system) {
            $systemExists = false;

            $updatedAt = $this->getUpdateTime($system);

            $record = System::whereId64($system->id64)
                ->where('name', $system->name)
                ->where('updated_at', $updatedAt)
                ->first();

            if (!$record) {
                $this->line('importing: <fg=green>' . $system->name . '</>');

                $payload = [
                    'id64' => $system->id64,
                    'name' => $system->name,
                    'coords' => json_encode($system->coords),
                    'updated_at' => $updatedAt
                ];

                if (property_exists($system, 'mainStar') && $system->mainStar && $system->mainStar !== '') {
                    $payload['main_star'] = $system->mainStar;
                }

                try {
                    $record = new System($payload);
                    $record->save();
                    $systemExists = true;
                } catch (Exception $e) {
                    Log::channel('system')->error($e->getMessage());
                }
            } else {
                $this->line('already imported: <fg=cyan>' . $system->name . '</>');
                $systemExists = true;
            }

            if ($this->option('has-info') && $systemExists) {
                $this->line('importing information for <fg=green>' . $system->name . '</>');

                $payload = [
                    'allegiance' => property_exists($system, 'allegiance') ? $system->allegiance : null,
                    'government' => property_exists($system, 'government') ? $system->government : null,
                    'economy' => property_exists($system, 'economy') ? $system->economy : null,
                    'population' => $system->population ?? 0,
                    'security' => $system->security
                ];
    
                if ($system->controllingFaction) {
                    $faction = $system->controllingFaction;
                    $payload['faction'] = property_exists($faction, 'name') ? $faction->name : null;
                    $payload['faction_state'] = property_exists($faction, 'allegiance') ? $faction->allegiance : null;
                }
    
                try {
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
    private function getUpdateTime($system)
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
