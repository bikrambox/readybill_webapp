<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:logs';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Clear log files older than one week';

    /**
     * Execute the console command.
     *
     * @return int
     */

    // public function handle()
    // {
    //     try {
    //         // Delete log files
    //         $logPath = storage_path('logs');
    //         $files = File::glob($logPath . '/*.log');

    //         foreach ($files as $file) {
    //             $fileName = basename($file);
    //             File::delete($file);
    //             Log::info("Deleted log file: {$fileName}");
    //         }

    //         $this->info('All log files have been deleted successfully.');
    //         Log::info('Log file cleanup operation completed successfully');

    //     } catch (\Exception $e) {
    //         $this->error('An error occurred: ' . $e->getMessage());
    //         Log::error('Error during log file cleanup operation: ' . $e->getMessage());
    //     }
    // }


    // public function handle()
    // {
    //     try {
    //         // Delete log files older than one week
    //         $logPath = storage_path('logs');
    //         $files = File::glob($logPath . '/*.log');
    //         // $oneWeekAgo = now()->subWeek()->timestamp;

    //         $retentionDays = env('LOG_RETENTION_DAYS', 7);
    //         $retentionTimestamp = now()->subDays($retentionDays)->timestamp;

    //         // dd($files);

    //         foreach ($files as $file) {
    //             $this->info('File'.$file);
    //             if (File::lastModified($file) < $retentionTimestamp) {
    //                 $fileName = basename($file);
    //                 File::delete($file);
    //                 Log::info("Deleted log file: {$fileName}");
    //             }
    //         }

    //         $this->info('Log files older than one week have been deleted successfully.');
    //         Log::info('Log file cleanup operation completed successfully');

    //     } catch (\Exception $e) {
    //         $this->error('An error occurred: ' . $e->getMessage());
    //         Log::error('Error during log file cleanup operation: ' . $e->getMessage());
    //     }
    // }

    public function handle()
    {
        try {
            // Log current time and retention settings
            $retentionDays = env('LOG_RETENTION_DAYS', 7);
            $retentionTimestamp = now()->subDays($retentionDays)->timestamp;

            // $this->info("Current time (IST): " . now()->toDateTimeString() . " (Timestamp: " . now()->timestamp . ")");
            // $this->info("Retention Days: {$retentionDays}");
            // $this->info("Retention Time: " . date('Y-m-d H:i:s', $retentionTimestamp) . " (Timestamp: {$retentionTimestamp})");

            // Define directories to process
            $directories = [
                storage_path('logs') => 'main logs',
                storage_path('logs/custom') => 'custom logs',
            ];

            foreach ($directories as $path => $label) {
                $this->processDirectory($path, $label, $retentionTimestamp);
            }

            // $this->info('Log file cleanup operation completed successfully.');
            Log::info('Log file cleanup operation completed successfully');
        } catch (\Exception $e) {
            report($e);
            // $this->error("Error: {$e->getMessage()}");
            Log::error("Error during log file cleanup: {$e->getMessage()}");
        }
    }

    protected function processDirectory(string $path, string $label, int $retentionTimestamp)
    {
        if (!File::isDirectory($path)) {
            // $this->warn("Directory {$label} ({$path}) does not exist.");
            Log::warning("Directory {$label} ({$path}) does not exist.");
            return;
        }

        $files = File::files($path);

        if (empty($files)) {
            // $this->info("No files found in {$label} ({$path})");
            Log::info("No files found in {$label} ({$path})");
            return;
        }

        $this->info("Found " . count($files) . " files in {$label} ({$path})");

        foreach ($files as $file) {
            $fileName = $file->getBasename();

            if (!file_exists($file) || !is_readable($file)) {
                // $this->warn("File {$fileName} in {$label} is not accessible.");
                Log::warning("File {$fileName} in {$label} is not accessible.");
                continue;
            }

            $lastModified = File::lastModified($file);
            $isOlder = $lastModified < $retentionTimestamp;

            $this->info("File: {$fileName}, Last modified: " . date('Y-m-d H:i:s', $lastModified) . " (Timestamp: {$lastModified}), Older than retention? " . ($isOlder ? 'Yes' : 'No'));

            if ($isOlder) {
                if (File::delete($file)) {
                    // $this->info("Deleted file: {$fileName} from {$label}");
                    Log::info("Deleted log file: {$fileName} from {$label}");
                } else {
                    // $this->error("Failed to delete file: {$fileName} from {$label}");
                    Log::error("Failed to delete log file: {$fileName} from {$label}");
                }
            } else {
                // $this->info("File {$fileName} in {$label} is not old enough to delete.");
            }
        }
    }
}

