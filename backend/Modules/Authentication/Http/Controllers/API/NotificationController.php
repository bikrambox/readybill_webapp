<?php

namespace Modules\Authentication\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

use Modules\GroceryIndia\Entities\Shop;

use Modules\Authentication\Helpers\UserHelper;

class NotificationController extends Controller
{
    public function notifications()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            // Set locale based on user preference (e.g., stored in user profile)
            $locale = $user->lang ?? 'en'; // Default to 'en' if not set
            App::setLocale($locale);

            $notifications = DB::connection('central')->table('notifications')
                ->where('user_id', $user->user_id)->get();

            if ($user->isAdmin == 1) {
                $shop = $user->shop;
            } else {
                // $shop = Shop::find($user->staff->addedBy);
                $shop = DB::table('shops')->where('shop_id', $user->staff->addedBy)->first();

            }

            // EXPIRY DATE
            $shop_subscription = DB::table('shop_subscriptions')
                ->where('shop_id', $shop->shop_id)
                ->orderBy('created_at', 'desc')
                ->first();

            $subscription_expiry_date = null;
            $daysLeft = 0; // Default value


            if ($shop_subscription) {

                $subscription_expiry_date = Carbon::parse($shop_subscription->end_date);
                // Calculate days left until expiry
                $daysLeft = Carbon::today()->diffInDays($subscription_expiry_date, false);

                $subscription_expiry_date = Carbon::parse($shop_subscription->end_date)->format('d-m-Y H.i.s');
            }

            $isSubscriptionExpired = UserHelper::isSubscriptionExpired($shop_subscription, $subscription_expiry_date);

            // // Translate notification messages
            // $translatedNotifications = $notifications->map(function ($notification) use ($daysLeft) {
            //     $key = "notification.{$notification->message}";
            //     // Check if translation exists, otherwise use a fallback message
            //     $translatedMessage = trans()->has($key)
            //         ? __($key, ['daysLeft' => $notification->daysLeft ?? 0])
            //         : __('notification.subscription_expires', ['daysLeft' => $daysLeft ?? 0]);
            //     $notification->message = $translatedMessage;
            //     return $notification;
            // });


            // Translate notification messages
            $translatedNotifications = $notifications->map(function ($notification) use ($daysLeft) {
                // Add condition to filter specific days left values
                if (!in_array($daysLeft, [10, 7, 5, 2, 1,0])) {
                    return null; // Skip this notification
                }

                $key = "notification.{$notification->message}";
                // Check if translation exists, otherwise use a fallback message
                $translatedMessage = trans()->has($key)
                    ? __($key, ['daysLeft' => $notification->daysLeft ?? 0])
                    : __('notification.subscription_expires', ['daysLeft' => $daysLeft ?? 0]);
                $notification->message = $translatedMessage;
                return $notification;
            })->filter(); // Remove null values from the collection


            return response()->json([
                'status' => 'success',
                'notifications' => $translatedNotifications,
                'isSubscriptionExpired' => $isSubscriptionExpired,
                'locale' => App::getLocale(),
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    
}
