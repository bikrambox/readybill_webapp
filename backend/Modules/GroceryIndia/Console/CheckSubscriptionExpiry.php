<?php

namespace Modules\GroceryIndia\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\ShopSubscriptions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class CheckSubscriptionExpiry extends Command
{
    private $module_type = 'grocery_india';

    protected $signature = 'groceryindia:check-shop-subscription {--debug : Show debug output regardless of APP_DEBUG}';

    protected $description = 'Check user subscription expiry and notify frontend';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $startTime = microtime(true);

        // $this->debugLog('====== CheckSubscriptionExpiry Command Started ======');
        // $this->debugLog('Started at    : ' . now()->toDateTimeString());
        // $this->debugLog('Module type   : ' . $this->module_type);

        try {
            // ── Fetch all active admin users ──────────────────────────────
            $this->debugLog('--- Fetching active admin users ---');

            $users = User::where('active', 1)
                ->where('isAdmin', 1)
                ->get();

            // $this->debugLog("Total active admin users found: {$users->count()}");

            // ── Filter grocery users ──────────────────────────────────────
            $groceryUsers = $users->where('module_type', $this->module_type)->pluck('user_id');

            // $this->debugLog("Users with module_type='{$this->module_type}': {$groceryUsers->count()}");
            // $this->debugLog('Grocery user IDs: [' . $groceryUsers->implode(', ') . ']');

            // ── Delete old notifications for grocery users ────────────────
            // $this->debugLog('--- Deleting existing notifications for grocery users ---');

            $deletedCount = DB::table('notifications')
                ->whereIn('user_id', $groceryUsers)
                ->delete();

            $this->debugLog("Deleted {$deletedCount} old notification(s).");

            // ── Loop through all users ────────────────────────────────────
            // $this->debugLog('--- Processing users ---');

            $notifications  = [];
            $skippedNoShop  = 0;
            $skippedNoSub   = 0;
            $skippedNoMatch = 0;

            foreach ($users->where('module_type', $this->module_type) as $user) {
                $shopId = $user->shop->shop_id ?? 0;

                // $this->debugLog("-- User ID={$user->user_id} | name={$user->name} | module_type={$user->module_type} | shop_id={$shopId}");

                if (!$shopId) {
                    $skippedNoShop++;
                    // $this->debugLog("   Skipped: no shop_id resolved.");
                    continue;
                }

                // ── Fetch latest subscription ─────────────────────────────
                $subscription = ShopSubscriptions::where('shop_id', $shopId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$subscription) {
                    $skippedNoSub++;
                    // $this->debugLog("   Skipped: no subscription found for shop_id={$shopId}.");
                    Log::info('[CheckSubscriptionExpiry] No subscription for shop', [
                        'user_id' => $user->user_id,
                        'shop_id' => $shopId,
                    ]);
                    continue;
                }

                // $this->debugLog("   Subscription ID={$subscription->id} | end_date={$subscription->end_date}");

                Log::info('[CheckSubscriptionExpiry] Subscription fetched', [
                    'user_id'         => $user->user_id,
                    'shop_id'         => $shopId,
                    'subscription_id' => $subscription->id,
                    'end_date'        => $subscription->end_date,
                ]);

                // ── Calculate days left ───────────────────────────────────
                $currentDate = Carbon::now();
                $expiryDate  = Carbon::parse($subscription->end_date)->endOfDay();
                $daysLeft    = $currentDate->diffInDays($expiryDate, false);

                // $this->debugLog("   Current date : {$currentDate->toDateTimeString()}");
                // $this->debugLog("   Expiry date  : {$expiryDate->toDateTimeString()} (end of day)");
                // $this->debugLog("   Days left    : {$daysLeft}");

                // ── Check notification thresholds ─────────────────────────
                $thresholds = [10, 7, 5, 2, 1, 0];

                if (!in_array($daysLeft, $thresholds)) {
                    $skippedNoMatch++;
                    // $this->debugLog("   Skipped: {$daysLeft} day(s) not in threshold [" . implode(', ', $thresholds) . "].");
                    continue;
                }

                // $this->debugLog("   Threshold matched: {$daysLeft} day(s) left — preparing notification.");

                // ── Build message ─────────────────────────────────────────
                $message = $daysLeft === 0
                    ? __('notification.Your subscription expires today')
                    : __('notification.subscription_expires', ['daysLeft' => $daysLeft]);

                // $this->debugLog("   Notification message: \"{$message}\"");

                // ── Duplicate check ───────────────────────────────────────
                $existingNotification = DB::table('notifications')
                    ->where('user_id', $user->user_id)
                    ->where('message', $message)
                    ->first();

                if ($existingNotification) {
                    // $this->debugLog("   Skipped: duplicate notification already exists (id={$existingNotification->id}).");
                    Log::info('[CheckSubscriptionExpiry] Duplicate notification skipped', [
                        'user_id'  => $user->user_id,
                        'days_left' => $daysLeft,
                        'existing_notification_id' => $existingNotification->id,
                    ]);
                    continue;
                }

                // ── Queue notification ────────────────────────────────────
                $notifications[] = [
                    'user_id'    => $user->user_id,
                    'message'    => $message,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // $this->debugLog("   Notification queued for insert.");
            }

            // ── Summary before insert ─────────────────────────────────────
            // $this->debugLog('--- Loop summary ---');
            // $this->debugLog("Skipped (no shop)         : {$skippedNoShop}");
            // $this->debugLog("Skipped (no subscription) : {$skippedNoSub}");
            // $this->debugLog("Skipped (no threshold hit): {$skippedNoMatch}");
            // $this->debugLog("Notifications to insert   : " . count($notifications));

            // ── Bulk insert ───────────────────────────────────────────────
            if (!empty($notifications)) {
                // $this->debugLog('--- Inserting notifications ---');

                DB::table('notifications')->insert($notifications);

                // $this->debugLog("Inserted " . count($notifications) . " notification(s) successfully.");

                Log::info('[CheckSubscriptionExpiry] Notifications inserted', [
                    'count' => count($notifications),
                ]);
            } else {
                // $this->debugLog('No notifications to insert.');
                Log::info('[CheckSubscriptionExpiry] No notifications to insert.');
            }

            $totalDuration = round(microtime(true) - $startTime, 3);
            // $this->debugLog("====== Command completed in {$totalDuration}s ======");
            $this->info("CheckSubscriptionExpiry finished. Inserted: " . count($notifications) . " notification(s).");

        } catch (Exception $e) {
            $totalDuration = round(microtime(true) - $startTime, 3);

            // $this->debugLog("EXCEPTION caught after {$totalDuration}s:");
            // $this->debugLog("  Message : " . $e->getMessage());
            // $this->debugLog("  File    : " . $e->getFile() . ':' . $e->getLine());

            $this->error('CheckSubscriptionExpiry failed: ' . $e->getMessage());

            // Log::error('[CheckSubscriptionExpiry] Exception occurred', [
            //     'error' => $e->getMessage(),
            //     'file'  => $e->getFile() . ':' . $e->getLine(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
        }
    }

    /**
     * Output a debug line to console and always write to Laravel log.
     */
    private function debugLog(string $message): void
    {
        if (config('app.debug') || $this->option('debug')) {
            $this->line('<fg=gray>[DEBUG] ' . $message . '</>');
        }

        // Log::debug('[CheckSubscriptionExpiry] ' . $message);
    }

    protected function getArguments(): array
    {
        return [];
    }

    protected function getOptions(): array
    {
        return [
            ['debug', null, InputOption::VALUE_NONE, 'Show debug output regardless of APP_DEBUG'],
        ];
    }
}