<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\ParseLargeFile;
use App\Jobs\ImportGalaxySystemsJob;

class ImportGalaxySystems extends Command
{
    use ParseLargeFile;

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
        $threshold = 524288000; // 500MB

        // If it's large, split it into parts
        if ($filesize > $threshold) {
            $this->warn("{$filename} is larger than " . $this->formatBytes($threshold));
            $this->line("The file will need to be split into parts for parallel processing.");
            $this->splitJsonFilesIntoParts($filename, $filepath, $filesize, 3000);
        }

        $this->info("Dispatching systems import job...");
        // ImportGalaxySystemsJob::dispatch(
        //     $this->option("file"),
        //     $this->option("has-info")
        // );
    }
}
