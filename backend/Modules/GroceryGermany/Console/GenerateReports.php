<?php

namespace Modules\GroceryGermany\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


use Modules\GroceryGermany\Http\Controllers\API\ReportController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Carbon\Carbon;

use Modules\GroceryGermany\Entities\ReportGeneration;
use Modules\Authentication\Entities\User;
use Modules\GroceryGermany\Entities\ShopSubscriptions;


class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grocerygermany:generate-reports {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate reports for GroceryGermany module';

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
        // set_time_limit(300); // Allow 5 minutes

        $currentEnv = env('APP_ENVIRONMENT', 'local');

        // Check for stuck reports and reset
        if (
            !$this->option('force') && ReportGeneration::where('status', 1)
                ->where('environment', $currentEnv)
                ->where('updated_at', '>=', now()->subMinutes(30))
                ->exists()
        ) {
            $this->info('A report is currently being processed. Skipping.');
            return;
        }
        ReportGeneration::where('status', 1)
            ->where('environment', $currentEnv)
            ->where('updated_at', '<', now()->subMinutes(30))
            ->update(['status' => 0]); // Reset to requested

        // Get the first requested report with locking
        $report = ReportGeneration::where('status', 0)
            ->where('environment', $currentEnv)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->first();

        if (!$report) {
            $this->info('No requested reports to process.');
            return;
        }

        // Check retry limit
        if (($report->retry_count ?? 0) >= 3) {
            $report->status = 3; // Mark as failed
            $report->save();
            $this->error('Report ID: ' . $report->id . ' exceeded retry limit.');
            return;
        }

        // Process report in a transaction
        try {
            DB::transaction(function () use ($report) {
                $report->status = 1; // Mark as processing
                $report->retry_count = ($report->retry_count ?? 0) + 1;
                $report->save();

                \Log::info('Starting report generation for ID: ' . $report->id, [
                    'retry_count' => $report->retry_count,
                ]);

                $controller = new ReportController();
                $result = $controller->generateReport($report->id);

                if (!$result) {
                    throw new \Exception('Report generation failed.');
                }

                $report->status = 2; // Mark as ready
                $report->save();

                // SEND PUSH NOTIFICATION TO APP

                // Get user's device token
                $deviceTokenList = DB::connection('central')->table('push_notification_tokens')
                    ->where('user_id', $report->user_id)
                    ->get();

                // Firebase setup
                $file = storage_path('app/google-services.json');
                if (!file_exists($file) || !is_readable($file)) {
                    Log::error('Firebase service account file issue');
                    return;
                }

                $firebase = (new Factory)->withServiceAccount($file);
                $messaging = $firebase->createMessaging();

                foreach ($deviceTokenList as $deviceToken) {

                    if ($deviceToken->device_token) {
                        try {
                            $firebaseMessage = CloudMessage::withTarget('token', $deviceToken->device_token)
                                ->withNotification(Notification::create(
                                    'Report Ready for Download',
                                    "The file is ready for download."
                                ))
                                ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

                            $messaging->send($firebaseMessage);
                            Log::info("Push notification sent to user {$report->user_id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send push notification to user {$report->user_id}: " . $e->getMessage());
                        }
                    } else {
                        Log::info("No device token found for user {$report->user_id}");
                    }

                }
                // SEND PUSH NOTIFICATION TO APP
            });
            $this->info('Report generated successfully for ID: ' . $report->id);
        } catch (\Exception $e) {
            Log::error('Failed to generate report ID: ' . $report->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Failed to generate report for ID: ' . $report->id . ' - ' . $e->getMessage());
            $report->status = ($report->retry_count >= 3) ? 3 : 0; // Failed or retry
            $report->save();
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
