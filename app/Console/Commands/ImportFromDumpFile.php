<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessFileImport;
use App\Traits\LargeJsonFile;

class ImportFromDumpFile extends Command
{
    use LargeJsonFile;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "edcts:import-from-dump
        {--channel= : The log channel for the dispatch job.};
        {--file= : The dump file, located at `/storage/dumps`.}
        {--has-info : Provide extra information if object is attached in the dump file.};
        {--queue=high : The queue to dispatch the job to.}
        {--validate : Validate the JSON file before processing.}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import records from `/storage/dumps` dump files.";

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

        $this->info("Importing records from {$filename}\n");

        // Get the file size and set the threshold for type of processing
        $filesize = filesize($filepath);
        $threshold = 1073741824; // 1GB

        // If it's large, split it into parts
        if ($filesize > $threshold) {
            $this->warn("{$filename} is larger than " . bytes_format($threshold));
            $this->line("The file will need to be split into parts for parallel processing.");
            
            $parts = 16;
            $this->setLargeJsonFileLogChannel($this->option("channel"));
            $this->splitLargeJsonFileIntoParts($filename, $filepath, $filesize, $parts);
            $this->info("Successfully split {$filename} into {$parts} parts.");

            for ($part = 1; $part <= $parts; $part++) {
                $this->info("Dispatching part {$part} import job for processing...");
                // Create a job to process each part
                ProcessFileImport::dispatch(
                    $this->option("channel"),
                    pathinfo($filename, PATHINFO_FILENAME) . "_part_{$part}.json",
                    $this->option("has-info"),
                    true,
                    $this->option("validate")
                )->onQueue($this->option('queue'));
            }

            $this->warn("Please ensure you have enough queue workers to process the parts in parallel.");
        } else {
            $this->line("{$filename} is smaller than " . bytes_format($threshold));
            $this->info("Dispatching import job for processing...");
            ProcessFileImport::dispatch(
                $this->option("channel"),
                $this->option("file"),
                $this->option("has-info"),
                false,
                $this->option("validate")
            )->onQueue($this->option('queue'));
        }
    }
}
