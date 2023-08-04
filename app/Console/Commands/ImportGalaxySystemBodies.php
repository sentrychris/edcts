<?php

namespace App\Console\Commands;

use App\Libraries\EliteAPIManager;
use App\Models\System;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportGalaxySystemBodies extends Command
{
    
    /**
    * @var EliteAPIManager
    */
    private EliteAPIManager $api;
    
    /**
    * Constructor
    */
    public function __construct(EliteAPIManager $api)
    {
        parent::__construct();
        $this->api = $api;
    }
    
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'elite:import-galaxy-system-bodies
    {--f|--from= : The service to import the data from (edsm or inara)}
    {--s|--system= : The system}';
    
    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Import galaxy system bodies from 3rd party services';
    
    /**
    * Execute the console command.
    */
    public function handle()
    {
        if (!$this->option('system')) {
            $this->error('You must specify a system');
            return false;
        }
        
        $system = System::whereName($this->option('system'))->first();
        if (!$system) {
            $this->error('Could not find specified system.');
            return false;
        }
        
        if (!(in_array($this->option('from'), ['edsm', 'inara']))) {
            $this->error('-f|--from must be edsm or inara');
            return false;
        }
        
        $response = $this->api->setConfig(config('elite.'.$this->option('from')))
            ->setCategory('system')
            ->get('bodies', [
                'systemName' => $this->option('system')
            ]);

        $bodies = $response->bodies;

        foreach($bodies as $body) {
            try {
                $this->line('import bodies for system <fg=cyan>' . $system->name . '</>');

                $system->bodies()->updateOrCreate([
                    'id64' => $body->id64,
                    'name' => $body->name,
                    'discovered_by' => $body->discovery->commander,
                    'discovered_at' => $body->discovery->date,
                    'type' => $body->type,
                    'sub_type' => $body->subType
                ]);
            } catch (Exception $e) {
                Log::channel('system')->error->getMessage($e->getMessage());
            }
        }
    }
}
