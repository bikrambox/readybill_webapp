<?php

namespace Modules\GroceryGermany\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\Authentication\Entities\User;
use Modules\GroceryGermany\Entities\ShopSubscriptions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class CheckSubscriptionExpiry extends Command
{
    private $module_type = "grocery_germany";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grocerygermany:check-shop-subscription';
  


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user subscription expiry and notify frontend';

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
            // Log::info('CheckSubscriptionExpiry command started at ' . now());

            // Fetch users with active subscriptions
            $users = User::where('active', 1)
                ->where('isAdmin', 1)
                ->get();
            $notifications = [];

            // Delete all previous notifications for users with active subscriptions
            // // Log::info('Deleting all previous notifications.');
            // \DB::table('notifications')
            // // ->where('module_type', $this->module_type)
            // ->whereIn('user_id', $users->pluck('user_id'))
            // ->delete();

            // Get only users whose module_type = grocery_germany
            $groceryUsers = $users->where('module_type', $this->module_type)->pluck('user_id');

            // Delete notifications only for these users
            DB::table('notifications')
                ->whereIn('user_id', $groceryUsers)
                ->delete();

            // Loop through users to prepare new notifications
            foreach ($users->where('module_type', $this->module_type) as $user) {

                // ✅ SET THE LOCALE BASED ON USER'S PREFERENCE
                $userLocale = $user->lang ?? 'en'; // Adjust based on your user locale field
                App::setLocale($userLocale);

                $shopId = $user->shop->shop_id ?? 0;

                // Check if subscription exists
                $subscription = ShopSubscriptions::where('shop_id', $shopId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($subscription) {

                    // $expiryDate = Carbon::parse($subscription->end_date);
                    // $daysLeft = Carbon::now()->diffInHours($expiryDate, false);

                    $currentDate = Carbon::now();
                    $expiryDate = Carbon::parse($subscription->end_date)->endOfDay(); // Set to 11:59 PM of the expiry day


                    $daysLeft = $currentDate->diffInDays($expiryDate, false);

                    // echo $user->name . ' -> ' . $daysLeft.'<br>';

                    // Check for 7, 2, 1 days left and prepare notifications if not already created
                    if (in_array($daysLeft, [10, 7, 5, 2, 1]) || $daysLeft === 0) {

                        // Check if a notification already exists for this user with the same message
                        // $message = $daysLeft === 0
                        //     ? "Your subscription expires today."
                        //     : "Your subscription expires in $daysLeft day(s).";

                        $message = $daysLeft === 0
                            ? __('notification.Your subscription expires today')
                            : __('notification.subscription_expires', ['daysLeft' => $daysLeft]);

                        // Check if a notification already exists for this user with the same message
                        $existingNotification = \DB::table('notifications')
                            ->where('user_id', $user->user_id)
                            ->where('message', $message)
                            ->first();

                        if (!$existingNotification) {
                            // Log::info('Preparing notification for user ' . $user->id . ' with ' . $daysLeft . ' days left.');
                            $notifications[] = [
                                'user_id' => $user->user_id,
                                'message' => $message,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            // Log::info('Notification already exists for user ' . $user->id . ' with ' . $daysLeft . ' days left.');
                        }
                    }
                } else {
                    // Log::info('No active subscription found for shop_id: ' . $shopId);
                }
            }

            // Insert notifications into the database
            if (!empty($notifications)) {
                \DB::table('notifications')->insert($notifications);
                // Log::info(count($notifications) . ' notifications inserted.');
            } else {
                Log::info('No notifications to insert.');
            }

            Log::info('CheckSubscriptionExpiry command finished at ' . now());
        } catch (Exception $e) {
            // Log the exception if something goes wrong
            // Log::error('Error occurred in CheckSubscriptionExpiry command: ' . $e->getMessage());
            // Log::error('Stack trace: ' . $e->getTraceAsString());
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
