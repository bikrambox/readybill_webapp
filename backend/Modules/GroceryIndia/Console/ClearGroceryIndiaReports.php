<?php

namespace Modules\GroceryIndia\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use Modules\GroceryIndia\Entities\ReportGeneration;
use Modules\GroceryIndia\Entities\Billing;
use Carbon\Carbon;

class ClearGroceryIndiaReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'groceryindia:clear-transaction-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear transaction reports older than 2 years from database and storage, and remove orphaned files';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // Step 1: Delete reports older than 730 days and their files
            $this->info('Starting cleanup of old transaction reports...');
            // $oldReports = ReportGeneration::where('created_at', '<', Carbon::now()->subDays(730))->get();

            // $oldReports = Billing::where('created_at', '<', Carbon::now()->subYears(2))->get();

            $retentionDays = env('TRANSACTION_RETENTION_DAYS', 7);
            $oldReports = Billing::where('created_at', '<', Carbon::now()->subDays($retentionDays))->get();

            $folderPath = storage_path('app/public/reports');

            $deletedRecords = 0;
            $deletedFiles = 0;

            foreach ($oldReports as $report) {
                $fileName = $report->file_path;
                $filePath = $folderPath . '/' . $fileName;

                // Delete the file if it exists
                if (File::exists($filePath)) {
                    if (File::delete($filePath)) {
                        $this->info("Deleted file: {$filePath}");
                        $deletedFiles++;
                    } else {
                        $this->warn("Failed to delete file: {$filePath}");
                        Log::warning("Failed to delete file: {$filePath}");
                    }
                } else {
                    $this->warn("File not found: {$filePath}");
                    Log::warning("File not found: {$filePath}");
                }

                // Delete the database record
                $report->delete();
                $deletedRecords++;
            }

            $this->info("Deleted {$deletedRecords} database records and {$deletedFiles} files.");

            // Step 2: Delete orphaned files in storage/public/reports
            $this->info('Checking for orphaned files in storage/public/reports...');
            $orphanedFilesDeleted = 0;

            // Get all files in the reports folder
            $files = File::files($folderPath);
            $dbFileNames = ReportGeneration::pluck('file_path')->toArray();

            foreach ($files as $file) {
                $fileName = $file->getFilename();

                // If the file is not in the database, delete it
                if (!in_array($fileName, $dbFileNames)) {
                    if (File::delete($file->getPathname())) {
                        $this->info("Deleted orphaned file: {$fileName}");
                        $orphanedFilesDeleted++;
                    } else {
                        $this->warn("Failed to delete orphaned file: {$fileName}");
                        Log::warning("Failed to delete orphaned file: {$fileName}");
                    }
                }
            }

            $this->info("Deleted {$orphanedFilesDeleted} orphaned files.");

            return Command::SUCCESS;

        } catch (\Exception $e) {

            report($e);
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Error during transaction reports cleanup: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
