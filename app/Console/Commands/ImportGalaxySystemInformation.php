<?php

namespace App\Console\Commands;

use App\Libraries\EliteAPIManager;
use App\Models\System;
use Illuminate\Console\Command;

class ImportGalaxySystemInformation extends Command
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
    protected $signature = 'elite:import-galaxy-system-info
    {--f|--from= : The service to import the data from (edsm or inara)}
    {--s|--system= : The system}';
    
    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Import galaxy system information from 3rd party services';
    
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
            ->setCategory('systems')
            ->get('system', [
                'systemName' => $this->option('system'),
                'showInformation' => true
            ]);

        if ($response->information) {
            $data = [];
            $this->api->convertResponse($response->information, $data);
            $system->information()->create($data);
        }
    }
}
