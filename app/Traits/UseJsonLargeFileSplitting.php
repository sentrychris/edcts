<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;

trait UseJsonLargeFileSplitting
{
    /**
     * The log channel to use for logging.
     * 
     * @var string
     */
    private string $logChannel = 'default';

    /**
     * Set the log channel to use for logging.
     * 
     * @param string $channel
     * @return void
     */
    public function setJsonFileLogChannel(string $channel): void
    {
        $this->logChannel = $channel;
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
    public function splitJsonFileIntoParts(string $filename, string $filepath, int $filesize, int $parts): void
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

    /**
     * Validate all split parts of a JSON file.
     * 
     * @param string $filename
     * @param int $parts
     * @return void
     */
    public function validateSplitFiles(string $filename, int $parts): void
    {
        for ($part = 1; $part <= $parts; $part++) {
            $outputFilePrefix = pathinfo($filename, PATHINFO_FILENAME);
            $outputFilePath = storage_path("dumps/{$outputFilePrefix}_part_{$part}.json");

            // Validate each part file
            if (!$this->validateJsonFile($outputFilePath)) {
                Log::channel($this->logChannel)->error("Validation failed for part {$part}.");
            }
        }
    }

    /**
     * Validate a JSON file.
     * 
     * @param string $filepath
     * @return bool
     */
    public function validateJsonFile(string $filepath): bool
    {
        try {
            // Use JsonMachine to iterate over the JSON items
            $jsonStream = Items::fromFile($filepath);

            // Iterate through each item to validate the JSON structure
            foreach ($jsonStream as $key => $value) {
                if ($key === null || $value === null) {
                    throw new Exception("Invalid JSON structure");
                }
            }

            Log::channel($this->logChannel)->info("Validation successful for file: {$filepath}");
            return true;
        } catch (Exception $e) {
            Log::channel($this->logChannel)->error("Validation failed for file: {$filepath}");
            Log::channel($this->logChannel)->error("Error: " . $e->getMessage());
            return false;
        }
    }
}