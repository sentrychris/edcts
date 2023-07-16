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
    protected $signature = 'elite:import-galaxy-systems
        {--f|--file= : The dump file (located from storage/dumps/)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import galaxy systems from dump file';

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
            $this->output->info('Importing system: ' . $system->name);
            if ((System::whereName($system->name)->exists())) {
                continue;
            }

            $record = new System([
                'id64' => $system->id64,
                'name' => $system->name,
                'coords' => json_encode($system->coords),
                'updated_at' => substr($system->updateTime, 0, strpos($system->updateTime, '+'))
            ]);

            if (property_exists($system, 'mainStar')) {
                $record->main_star = $system->mainStar;
            }

            $record->save();
        }
    }
}
