<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportGalaxySystemsJob;

class ImportGalaxySystems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elite:import-galaxy-systems
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
    public function handle(): void
    {
        $this->info('Dispatching systems import job...');
        ImportGalaxySystemsJob::dispatch(
            $this->option('file'),
            $this->option('has-info')
        );
    }
}
