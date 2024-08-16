<?php

namespace App\Traits;

trait ParseLargeFile
{
    /**
     * 
     */
    public function splitJsonFilesIntoParts(string $filename, string $filepath, int $filesize, int $parts)
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

        $this->line("\n\nTotal size: " . $this->formatBytes($filesize));
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
     * 
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
                    // Close the JSON array in the current part
                    fwrite($outputFile, "\n]");
                    fclose($outputFile);
                    // Output the log message when a part is finished
                    $this->line("Part {$part} written to {$outputFilePath}");
                }
                $part++;
                $outputFilePrefix = pathinfo($filename, PATHINFO_FILENAME);
                $outputFilePath = storage_path("dumps/{$outputFilePrefix}_part_{$part}.json");
                $outputFile = fopen($outputFilePath, 'w');
                // Start the JSON array in the new part
                fwrite($outputFile, "[\n");
                $currentObjectCount = 0;  // Reset count for the new file
            } else {
                // Add a comma only if it's not the first object in the file or part
                fwrite($outputFile, "\n");
            }

            // Write the JSON object to the current part
            fwrite($outputFile, $trimmedLine);
            $currentObjectCount++;
        }

        // Close the final part properly
        if ($outputFile) {
            fwrite($outputFile, "\n]");
            fclose($outputFile);
            $this->line("Part {$part} written to {$outputFilePath}");
        }

        fclose($file);
    }

    /**
     * 
     */
    public function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 

        $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . $units[$pow]; 
    } 
}