<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportGalaxySystemsJob;
use App\Traits\LargeJsonFile;

class ImportGalaxySystems extends Command
{
    use LargeJsonFile;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "elite:import-galaxy-systems
        {--f|--file= : The dump file (located from storage/dumps/)}
        {--i|--has-info : Provide system information if attached in dump file}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import galaxy systems from dump files.";

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Get the file
        $filename = $this->option("file");
        $filepath = storage_path("dumps/{$filename}");
        if (!file_exists($filepath)) {
            $this->error("File not found!");
            return;
        }

        $this->info("Importing galaxy systems from {$filename}\n");

        // Get the file size and set the threshold for type of processing
        $filesize = filesize($filepath);
        $threshold = 1073741824; // 1GB

        // If it's large, split it into parts
        if ($filesize > $threshold) {
            // $this->warn("{$filename} is larger than " . $this->formatBytes($threshold));
            // $this->line("The file will need to be split into parts for parallel processing.");
            
            $parts = 16;
            // $this->splitJsonFileIntoParts($filename, $filepath, $filesize, $parts);
            // $this->validateAllJsonSplitParts($filename, $parts);

            for ($part = 1; $part <= $parts; $part++) {
                $this->info("Dispatching part {$part} import job for processing...");
                // Create a job to process each part
                ImportGalaxySystemsJob::dispatch(
                    pathinfo($filename, PATHINFO_FILENAME) . "_part_{$part}.json",
                    $this->option("has-info")
                )->onQueue("high");
            }
        }

        // $this->info("Dispatching systems import job...");
        // ImportGalaxySystemsJob::dispatch(
        //     $this->option("file"),
        //     $this->option("has-info")
        // );
    }
}
