<?php

namespace Modules\GroceryIndia\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\GroceryIndia\Http\Controllers\API\ReportController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Carbon\Carbon;

use Modules\GroceryIndia\Entities\ReportGeneration;
use Modules\GroceryIndia\Entities\ShopSubscriptions;

class GenerateReports extends Command
{
    protected $signature = 'groceryindia:generate-reports {--force}';
    protected $description = 'Generate reports for GroceryIndia module';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $startTime = microtime(true);
        $currentEnv = env('APP_ENVIRONMENT', 'local');

        // $this->debugLog('====== GenerateReports Command Started ======');
        // $this->debugLog("Environment   : {$currentEnv}");
        // $this->debugLog("Force option  : " . ($this->option('force') ? 'YES' : 'NO'));
        // $this->debugLog("Started at    : " . now()->toDateTimeString());

        // ── Stuck report check ────────────────────────────────────────────
        // $this->debugLog('--- Checking for stuck (in-progress) reports ---');

        $stuckCount = ReportGeneration::where('status', 1)
            ->where('environment', $currentEnv)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->count();

        // $this->debugLog("Stuck reports (status=1, updated within 30 min): {$stuckCount}");

        if (!$this->option('force') && $stuckCount > 0) {
            $this->warn('A report is currently being processed. Skipping.');
            $this->debugLog('Exiting: active report detected and --force not set.');
            return;
        }

        // ── Reset stale stuck reports ─────────────────────────────────────
        $staleCount = ReportGeneration::where('status', 1)
            ->where('environment', $currentEnv)
            ->where('updated_at', '<', now()->subMinutes(30))
            ->count();

        // $this->debugLog("Stale stuck reports (status=1, not updated for >30 min): {$staleCount}");

        if ($staleCount > 0) {
            ReportGeneration::where('status', 1)
                ->where('environment', $currentEnv)
                ->where('updated_at', '<', now()->subMinutes(30))
                ->update(['status' => 0]);
            // $this->debugLog("Reset {$staleCount} stale report(s) back to status=0.");
        }

        // ── Fetch next pending report ─────────────────────────────────────
        // $this->debugLog('--- Fetching next pending report (status=0) ---');

        $report = ReportGeneration::where('status', 0)
            ->where('environment', $currentEnv)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->first();

        if (!$report) {
            $this->info('No requested reports to process.');
            $this->debugLog('Exiting: no pending reports found.');
            return;
        }

        // $this->debugLog("Report found  : ID={$report->id}");
        // $this->debugLog("  user_id     : {$report->user_id}");
        // $this->debugLog("  created_at  : {$report->created_at}");
        // $this->debugLog("  retry_count : " . ($report->retry_count ?? 0));
        // $this->debugLog("  environment : {$report->environment}");

        // ── Retry limit check ─────────────────────────────────────────────
        $retryCount = $report->retry_count ?? 0;

        if ($retryCount >= 3) {
            // $this->debugLog("Retry limit reached ({$retryCount}/3). Marking report as failed (status=3).");
            $report->status = 3;
            $report->save();
            $this->error("Report ID: {$report->id} exceeded retry limit. Marked as failed.");
            return;
        }

        // ── Process report ────────────────────────────────────────────────
        // $this->debugLog('--- Starting DB transaction for report processing ---');

        try {
            DB::transaction(function () use ($report) {

                $report->status = 1;
                $report->retry_count = ($report->retry_count ?? 0) + 1;
                $report->save();

                // $this->debugLog("Report ID={$report->id} marked as processing (status=1), retry_count={$report->retry_count}.");

                Log::info('[GenerateReports] Starting report generation', [
                    'report_id'   => $report->id,
                    'user_id'     => $report->user_id,
                    'retry_count' => $report->retry_count,
                    'environment' => $report->environment,
                ]);

                // ── Call report controller ────────────────────────────────
                // $this->debugLog("Calling ReportController::generateReport({$report->id}) ...");

                $controllerStart = microtime(true);
                $controller = new ReportController();
                $result = $controller->generateReport($report->id);
                $controllerDuration = round(microtime(true) - $controllerStart, 3);

                // $this->debugLog("ReportController::generateReport() returned: " . ($result ? 'truthy' : 'falsy') . " (took {$controllerDuration}s)");

                if (!$result) {
                    throw new \Exception('ReportController::generateReport() returned falsy.');
                }

                $report->status = 2;
                $report->save();
                // $this->debugLog("Report ID={$report->id} marked as ready (status=2).");

                Log::info('[GenerateReports] Report generated successfully', [
                    'report_id' => $report->id,
                    'duration'  => $controllerDuration,
                ]);

                // ── Push notification ─────────────────────────────────────
                // $this->debugLog('--- Sending push notifications ---');

                $deviceTokenList = DB::connection('central')
                    ->table('push_notification_tokens')
                    ->where('user_id', $report->user_id)
                    ->get();

                // $this->debugLog("Device tokens found for user_id={$report->user_id}: " . $deviceTokenList->count());

                if ($deviceTokenList->isEmpty()) {
                    // $this->debugLog("No device tokens for user_id={$report->user_id}. Skipping push.");
                    Log::info('[GenerateReports] No device tokens found', ['user_id' => $report->user_id]);
                    return;
                }

                // ── Firebase init ─────────────────────────────────────────
                $file = storage_path('app/google-services.json');
                // $this->debugLog("Firebase credentials file: {$file}");

                if (!file_exists($file)) {
                    $this->error("Firebase credentials file NOT FOUND: {$file}");
                    Log::error('[GenerateReports] Firebase credentials file not found', ['path' => $file]);
                    return;
                }

                if (!is_readable($file)) {
                    $this->error("Firebase credentials file NOT READABLE: {$file}");
                    Log::error('[GenerateReports] Firebase credentials file not readable', ['path' => $file]);
                    return;
                }

                // $this->debugLog("Firebase credentials file OK.");

                $firebase  = (new Factory)->withServiceAccount($file);
                $messaging = $firebase->createMessaging();

                // $this->debugLog("Firebase messaging instance created.");

                // ── Send to each device ───────────────────────────────────
                $successCount = 0;
                $failCount    = 0;

                foreach ($deviceTokenList as $deviceToken) {
                    $tokenPreview = $deviceToken->device_token
                        ? substr($deviceToken->device_token, 0, 20) . '...'
                        : 'NULL';

                    // $this->debugLog("Processing token: {$tokenPreview}");

                    if (!$deviceToken->device_token) {
                        // $this->debugLog("  Skipped: token is empty.");
                        Log::info('[GenerateReports] Empty device token', ['user_id' => $report->user_id]);
                        continue;
                    }

                    try {
                        $firebaseMessage = CloudMessage::withTarget('token', $deviceToken->device_token)
                            ->withNotification(Notification::create(
                                'Report Ready for Download',
                                'The file is ready for download.'
                            ))
                            ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

                        $messaging->send($firebaseMessage);
                        $successCount++;

                        // $this->debugLog("  Push sent successfully.");
                        Log::info('[GenerateReports] Push notification sent', [
                            'user_id'      => $report->user_id,
                            'token_prefix' => $tokenPreview,
                        ]);
                    } catch (\Exception $e) {
                        $failCount++;
                        // $this->debugLog("  Push FAILED: " . $e->getMessage());
                        Log::error('[GenerateReports] Push notification failed', [
                            'user_id'      => $report->user_id,
                            'token_prefix' => $tokenPreview,
                            'error'        => $e->getMessage(),
                        ]);
                    }
                }

                // $this->debugLog("Push summary: {$successCount} sent, {$failCount} failed.");
            });

            $totalDuration = round(microtime(true) - $startTime, 3);
            $this->info("Report ID={$report->id} generated successfully.");
            // $this->debugLog("====== Command completed in {$totalDuration}s ======");

        } catch (\Exception $e) {
            $totalDuration = round(microtime(true) - $startTime, 3);

            // $this->debugLog("EXCEPTION caught after {$totalDuration}s:");
            // $this->debugLog("  Message : " . $e->getMessage());
            // $this->debugLog("  File    : " . $e->getFile() . ':' . $e->getLine());

            Log::error('[GenerateReports] Report generation failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
                'file'      => $e->getFile() . ':' . $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);

            $this->error("Failed to generate report ID={$report->id} — " . $e->getMessage());

            $newStatus = ($report->retry_count >= 3) ? 3 : 0;
            $report->status = $newStatus;
            $report->save();

            // $this->debugLog("Report ID={$report->id} status set to {$newStatus} (" . ($newStatus === 3 ? 'failed' : 'retry') . ").");
        }
    }

    /**
     * Output a debug line to the console (only when APP_DEBUG=true or --force).
     */
    private function debugLog(string $message): void
    {
        if (config('app.debug') || $this->option('force')) {
            $this->line('<fg=gray>[DEBUG] ' . $message . '</>');
        }

        // Log::debug('[GenerateReports] ' . $message);
    }

    protected function getArguments(): array
    {
        return [];
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force processing even if a report is in progress'],
        ];
    }
}