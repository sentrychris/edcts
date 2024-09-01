<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\JsonFileParsing;
use App\Jobs\ProcessSystemsDumpFileImport;

class ImportDumpFile extends Command
{
    use JsonFileParsing;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "edcts:import:dumpfile
        {--type= : The type of dump file to import.}
        {--channel= : The log channel for the dispatch job.};
        {--file= : The dump file, located at `/storage/dumps`.}
        {--queue=default : The queue to dispatch the job to.}
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

        $this->line("Configuring import job for {$filename}");

        // Get the file size and set the threshold for type of processing
        $filesize = filesize($filepath);
        $threshold = 1073741824; // 1GB

        // If it's large, split it into parts
        if ($filesize > $threshold) {
            $this->warn("{$filename} is larger than " . bytes_format($threshold));
            $this->line("The file will need to be split into parts for parallel processing.");

            $this->setJsonFileLogChannel($this->option('channel'));
            
            $parts = 32;
            $this->splitJsonFileIntoParts($filename, $filepath, $filesize, $parts);
            $this->info("Successfully split {$filename} into {$parts} parts.");

            for ($part = 1; $part <= $parts; $part++) {
                $this->info("Dispatching part {$part} import job for processing...");

                $filename = pathinfo($this->option('file'), PATHINFO_FILENAME) . "_part_{$part}.json";
                $this->dispatchJob($filename);
            }

            $this->warn("Please ensure you have enough queue workers for parallel processing.");
        } else {
            $this->line("{$filename} is smaller than split threshold (" . bytes_format($threshold) . ")");
            $this->dispatchJob($filename);
        }
    }

    /**
     * Dispatch a job to process the file.
     * 
     * @param string $filename
     * @return void
     */
    private function dispatchJob (string $filename)
    {
        if (in_array($this->option("type"), ['sys', 'system', 'systems'])) {
            ProcessSystemsDumpFileImport::dispatch(
                $this->option("channel"),
                $filename,
                $this->option("validate"),
            )->onQueue($this->option('queue'));

            $this->info("Import job has been dispatched.");
        } else {
            $this->error('Type does not match a valid dumpfile processing job type.');
        }

        // More types can be added here...
    }
}
