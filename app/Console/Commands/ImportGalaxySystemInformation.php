<?php

namespace App\Console\Commands;

use App\Libraries\EliteAPIManager;
use App\Models\System;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Items;
use stdClass;

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
    protected $signature = 'elite:import-galaxy-system-information
    {--f|--from= : The service to import the data from (edsm or inara)}
    {--s|--system= : The system}';
    
    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Import galaxy system information from a 3rd party service';
    
    /**
    * Execute the console command.
    */
    public function handle()
    {
        if (!$this->option('system')) {
            $this->output->error('You must specify a system');
            return false;
        }
        
        $system = System::whereName($this->option('system'))->first();
        if (!$system) {
            $this->output->error('Could not find specified system.');
            return false;
        }
        
        if (!(in_array($this->option('from'), ['edsm', 'inara']))) {
            $this->output->error('-f|--from must be edsm or inara');
            return false;
        }
        
        $response =$this->api->setConfig(config('elite.'.$this->option('from')))
        ->setCategory('systems')
        ->get('system', [
            'systemName' => $this->option('system'),
            'showInformation' => true
        ]);

        if ($response->information) {
            $data = [];
            $this->convert($response->information, $data);
            $system->information()->create($data);
        }
    }
    
    private function convert($obj, &$arr)
    {
        if (!is_object($obj) && !is_array($obj)) {
            $arr = $obj;
            return $arr;
        }
        
        foreach ($obj as $key => $value){
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));

            if (!empty($value)) {
                $arr[$key] = array();
                $this->convert($value, $arr[$key]);
            } else {
                $arr[$key] = $value;
            }
        }
        
        return $arr;
    }
}
