<?php

namespace Modules\GroceryIndia\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Modules\GroceryIndia\Entities\ReportGeneration;
use Modules\GroceryIndia\Http\Controllers\API\ReportController;

use Illuminate\Queue\Middleware\WithoutOverlapping;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $reportId)
    {
    }

    // public function handle(): void
    // {
    //     $report = ReportGeneration::findOrFail($this->reportId);

    //     if ($report->status !== 0) {
    //         return;
    //     }

    //     $report->status = 1;
    //     $report->retry_count = ($report->retry_count ?? 0) + 1;
    //     $report->save();

    //     try {
    //         $controller = new ReportController();
    //         $result = $controller->generateReport($report->id);

    //         if (!$result) {
    //             throw new \RuntimeException('Report generation failed.');
    //         }

    //         $report->status = 2;
    //         $report->save();

    //         $this->sendPushNotification($report);
    //     } catch (\Throwable $e) {
    //         Log::error('[GenerateReportJob] Failed', [
    //             'report_id' => $report->id,
    //             'error' => $e->getMessage(),
    //         ]);

    //         $report->status = ($report->retry_count >= 3) ? 3 : 0;
    //         $report->save();

    //         throw $e;
    //     }
    // }

    public function handle(): void
    {
        $report = ReportGeneration::findOrFail($this->reportId);

        if ($report->status !== 0) {
            return;
        }

        $report->status = 1;
        $report->retry_count = ($report->retry_count ?? 0) + 1;
        $report->save();

        try {
            $controller = new ReportController();
            $fileName = $controller->generateReport($report->id);

            if (!$fileName) {
                throw new \RuntimeException('Report generation failed.');
            }

            $report->status = 2;
            $report->file_path = $fileName;
            $report->save();

            $this->sendPushNotification($report);
        } catch (\Throwable $e) {
            Log::error('[GenerateReportJob] Failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $report->status = ($report->retry_count >= 3) ? 3 : 0;
            $report->save();

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        ReportGeneration::where('id', $this->reportId)->update(['status' => 3]);
    }

    private function sendPushNotification(ReportGeneration $report): void
    {
        $deviceTokenList = DB::connection('central')
            ->table('push_notification_tokens')
            ->where('user_id', $report->user_id)
            ->get();

        if ($deviceTokenList->isEmpty()) {
            return;
        }

        $file = storage_path('app/google-services.json');

        if (!file_exists($file) || !is_readable($file)) {
            return;
        }

        $messaging = (new Factory)->withServiceAccount($file)->createMessaging();

        foreach ($deviceTokenList as $deviceToken) {
            if (!$deviceToken->device_token) {
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
            } catch (\Throwable $e) {
                Log::error('[GenerateReportJob] Push failed', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("report:{$this->reportId}"))
                ->releaseAfter(60)
                ->expireAfter(600),
        ];
    }

}