<?php

namespace App\Traits;

use Exception;
use JsonMachine\Items;

trait LargeJsonFile
{
    /**
     * Split a large JSON file into parts for parallel processing.
     * 
     * @param string $filename
     * @param string $filepath
     * @param int $filesize
     * @param int $parts
     * @return void
     */
    public function splitJsonFileIntoParts(string $filename, string $filepath, int $filesize, int $parts): void
    {
        $this->line("Calculating parameters for equal split, please wait...\n");

        // Calculate the number of JSON objects
        $objectsInFile = 0;
        $handle = fopen($filepath, "r");
        if ($handle) {
            // Initialize the progress bar based on file size
            $bar = $this->output->createProgressBar($filesize);
            $bar->setFormat('%bar% %percent:3s%%');

            // Read each line and count JSON objects
            while (($line = fgets($handle)) !== false) {
                // Trim and skip empty lines and brackets
                $trimmedLine = trim($line);
                if ($trimmedLine === '' || $trimmedLine === '[' || $trimmedLine === ']') {
                    continue;
                }
                $objectsInFile++;
                $bar->advance(strlen($line));
            }
            fclose($handle);
            $bar->finish();
        }

        $this->line("\n\nTotal size: " . bytes_format($filesize));
        $this->line("Total JSON objects: " . number_format($objectsInFile));

        // Determine the number of objects per file to create equal parts
        $objectsPerFile = ceil($objectsInFile / $parts);

        $this->line("\nSplitting into:");
        $this->line("Total parts: {$parts}");
        $this->line("Total JSON objects per file: " . number_format($objectsPerFile));

        $this->info("\nSplitting file for parallel processing, please wait...");

        $this->splitJsonFile($filename, $filepath, $objectsPerFile);
    }

    /**
     * Split a JSON file into parts based on the number of objects per file.
     * 
     * @param string $filename
     * @param string $filepath
     * @param int $objectsPerFile
     * @return void
     */
    public function splitJsonFile(string $filename, string $filepath, int $objectsPerFile): void
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
                    $this->line("Part {$part} written to {$outputFilePath}");
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
            $this->line("Part {$part} written to {$outputFilePath}");
        }

        fclose($file);
    }

    /**
     * Validate split parts of a JSON file.
     * 
     * @param string $filename
     * @param int $parts
     * @return void
     */
    public function validateAllJsonSplitParts(string $filename, int $parts): void
    {
        for ($part = 1; $part <= $parts; $part++) {
            $outputFilePrefix = pathinfo($filename, PATHINFO_FILENAME);
            $outputFilePath = storage_path("dumps/{$outputFilePrefix}_part_{$part}.json");

            // Validate each part file
            if (!$this->validateJsonFile($outputFilePath)) {
                $this->error("Validation failed for part {$part}.");
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

            $this->line("Validation successful for file: {$filepath}");
            return true;
        } catch (Exception $e) {
            $this->error("Validation failed for file: {$filepath}");
            $this->error("Error: " . $e->getMessage());
            return false;
        }
    }
}