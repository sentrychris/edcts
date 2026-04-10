<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class JsonLargeFileSplitService
{
    /**
     * The log channel to use for logging.
     * 
     * @var string
     */
    private string $logChannel = 'default';

    /**
     * Set the log channel
     */
    public function setLogChannel(string $channel): self
    {
        $this->logChannel = $channel;
        return $this;
    }

    /**
     * Split a JSON file into parts for parallel processing.
     * 
     * @param string $filename
     * @param string $filepath
     * @param int $filesize
     * @param int $parts
     * @return void
     */
    public function split(string $filename, string $filepath, int $filesize, int $parts): void
    {
        Log::channel($this->logChannel)->info("Processing file {$filepath} to split into {$parts} parts.");
        Log::channel($this->logChannel)->info("Calculating parameters for equal split contents, please wait...");

        // Calculate the number of JSON objects
        $objectsInFile = 0;
        $handle = fopen($filepath, "r");
        if ($handle) {
            // Read each line and count JSON objects
            while (($line = fgets($handle)) !== false) {
                // Trim and skip empty lines and brackets
                $trimmedLine = trim($line);
                if ($trimmedLine === '' || $trimmedLine === '[' || $trimmedLine === ']') {
                    continue;
                }
                $objectsInFile++;
            }
            fclose($handle);
        }

        Log::channel($this->logChannel)->info("Total size: " . bytes_format($filesize));
        Log::channel($this->logChannel)->info("Total JSON objects: " . number_format($objectsInFile));

        // Determine the number of objects per file to create equal parts
        $objectsPerFile = ceil($objectsInFile / $parts);

        Log::channel($this->logChannel)->info("Splitting into:");
        Log::channel($this->logChannel)->info("Total parts: {$parts}");
        Log::channel($this->logChannel)->info("Total JSON objects per file: " . number_format($objectsPerFile));

        Log::channel($this->logChannel)->info("Splitting file for parallel processing, please wait...");

        $this->doSplit($filename, $filepath, $objectsPerFile);

        Log::channel($this->logChannel)->info("Successfully split {$filename} into {$parts} parts.");
    }

    /**
     * Split a JSON file into parts based on the number of objects per file.
     * 
     * @param string $filename
     * @param string $filepath
     * @param int $objectsPerFile
     * @return void
     */
    private function doSplit(string $filename, string $filepath, int $objectsPerFile): void
    {
        $file = fopen($filepath, 'r');
        $part = 0;
        $currentObjectCount = 0;
        $outputFile = null;
        $outputFilePath = "";

        // Remove the opening bracket from the first line
        $firstLine = fgets($file);
        if (trim($firstLine) !== '[') {
            rewind($file);
        }

        while (($line = fgets($file)) !== false) {
            // Trim and skip empty lines and brackets
            $trimmedLine = trim($line);
            if ($trimmedLine === '' || $trimmedLine === '[' || $trimmedLine === ']') {
                continue;
            }

            if ($currentObjectCount % $objectsPerFile == 0) {
                if ($outputFile) {
                    fseek($outputFile, -1, SEEK_CUR); // Move back to remove the last comma
                    // Close the JSON array in the current part
                    fwrite($outputFile, "\n]");
                    fclose($outputFile);
                    Log::channel($this->logChannel)
                        ->info("Part {$part} written to {$outputFilePath} (" . bytes_format(filesize($outputFilePath)) . ")");
                }

                $part++;
                $outputFilePrefix = pathinfo($filename, PATHINFO_FILENAME);
                $outputFilePath = storage_path("dumps/{$outputFilePrefix}_part_{$part}.json");
                $outputFile = fopen($outputFilePath, 'w');

                // Start the JSON array in the new part
                fwrite($outputFile, "[\n");
                $currentObjectCount = 0;
            } else {
                fwrite($outputFile, "\n");
            }

            // Write the JSON object to the current part
            fwrite($outputFile, $trimmedLine);
            $currentObjectCount++;
        }

        // Close the final part properly
        if ($outputFile) {
            fseek($outputFile, -1, SEEK_CUR); // Move back to remove the last comma
            fwrite($outputFile, "\n]");
            fclose($outputFile);
            Log::channel($this->logChannel)
                ->info("Part {$part} written to {$outputFilePath} (" . bytes_format(filesize($outputFilePath)) . ")");
        }

        fclose($file);
    }
}