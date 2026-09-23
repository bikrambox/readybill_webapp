<?php

namespace Modules\GroceryIndia\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\ShopSubscriptions;

class TriggerPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trigger:push-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Push Notification';

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
            // Log::info('TriggerPushNotification command started at ' . now());

            // Firebase setup
            $file = storage_path('app/google-services.json');
            if (!file_exists($file) || !is_readable($file)) {
                Log::error('Firebase service account file issue');
                return;
            }

            $firebase = (new Factory)->withServiceAccount($file);
            $messaging = $firebase->createMessaging();

            // Fetch users with active subscriptions
            $users = User::where('active', 1)
                ->where('isAdmin', 1)
                ->with('shop')
                ->get();

            foreach ($users as $user) {
                $shopId = $user->shop?->shop_id;

                if (!$shopId) {
                    Log::info("No shop found for user {$user->id}");
                    continue;
                }

                $subscription = ShopSubscriptions::where('shop_id', $shopId)
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($subscription) {
                    $currentDate = Carbon::now();
                    $expiryDate = Carbon::parse($subscription->end_date)->endOfDay();

                    // Check if the subscription has expired by comparing dates directly
                    $isExpired = $currentDate->greaterThan($expiryDate);
                    $daysLeft = $currentDate->diffInDays($expiryDate, false);

                    // Log the values for debugging
                    Log::info("User {$user->id} - Current date: {$currentDate}, Expiry date: {$expiryDate}, Days left: {$daysLeft}, Is expired: " . ($isExpired ? 'yes' : 'no'));

                    // Determine the message based on expiration status
                    if ($isExpired) {
                        $message = __('notification.Your subscription has expired. Renew now to regain full access');
                    } elseif ($daysLeft === 0) {
                        $message = __('notification.Your subscription will be expired today. Please renew it');
                    } elseif (in_array($daysLeft, [10, 7, 5, 2, 1])) {
                        // $message = "Your subscription expires in $daysLeft day(s).";
                        $message = __('notification.subscription_expires', ['daysLeft' => $daysLeft]);
                    } else {
                        Log::info("User {$user->id} - No notification needed (daysLeft: {$daysLeft})");
                        continue;
                    }

                    // Log the selected message
                    Log::info("User {$user->id} - Sending message: {$message}");

                    // // Get user's device token
                    // $deviceToken = DB::table('push_notification_tokens')
                    //     ->where('user_id', $user->user_id)
                    //     ->value('device_token');

                    // Get user's device token
                    $deviceTokenList = DB::table('push_notification_tokens')
                        ->where('user_id', $user->user_id)
                        ->get();

                    foreach($deviceTokenList as $deviceToken){

                        if ($deviceToken->device_token) {
                            try {
                                $firebaseMessage = CloudMessage::withTarget('token', $deviceToken->device_token)
                                    ->withNotification(Notification::create(
                                        'Subscription Reminder',
                                        $message
                                    ))
                                    ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
    
                                $messaging->send($firebaseMessage);
                                Log::info("Push notification sent to user {$user->user_id} for {$daysLeft} days left");
                            } catch (\Exception $e) {
                                Log::error("Failed to send push notification to user {$user->user_id}: " . $e->getMessage());
                            }
                        } else {
                            Log::info("No device token found for user {$user->user_id}");
                        }

                    }
                }
            }

            Log::info('TriggerPushNotification command finished at ' . now());
        } catch (Exception $e) {
            Log::error('Error in TriggerPushNotification: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
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
