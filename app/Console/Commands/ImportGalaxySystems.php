<?php

namespace App\Console\Commands;

use App\Models\System;
use Illuminate\Console\Command;
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
            $this->output->error('No file found at ' .  $this->option('file'));
        }

        $systems = Items::fromFile($file);
        foreach ($systems as $system) {
            $this->output->writeln('importing system: ' . $system->name);
            $record = System::whereName($system->name)->first();

            if (!$record) {
                $record = new System([
                    'id64' => $system->id64,
                    'name' => $system->name,
                    'coords' => json_encode($system->coords),
                    'updated_at' => $this->getUpdateTime($system)
                ]);
    
                $record->save();
            }

            if ($this->option('has-info')) {
                $this->output->writeln('importing information for ' . $system->name);
                $information = [
                    'allegiance' => property_exists($system, 'allegiance') ? $system->allegiance : null,
                    'government' => property_exists($system, 'government') ? $system->government : null,
                    'economy' => property_exists($system, 'economy') ? $system->economy : null,
                    'population' => $system->population ?? 0,
                    'security' => $system->security
                ];
    
                if ($system->controllingFaction) {
                    $faction = $system->controllingFaction;
                    $information['faction'] = property_exists($faction, 'name') ? $faction->name : null;
                    $information['faction_state'] = property_exists($faction, 'allegiance') ? $faction->allegiance : null;
                }
    
                $record->information()->updateOrCreate($information);
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
