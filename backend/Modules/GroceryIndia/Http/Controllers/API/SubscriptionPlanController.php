<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\DB;
use App\Helpers\TableNameHelper;
use Illuminate\Support\Facades\Cache;
use Auth;
use Carbon\Carbon;

use Modules\GroceryIndia\Entities\Shop;

use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;


class SubscriptionPlanController extends Controller
{
    public function subscriptionPlans()
    {

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            // Check if the data is already cached
            $cacheKey = env('CACHE_KEY_PREFIX') . 'subscription_plans';
            $cachedData = Cache::store('memcached')->get($cacheKey);


            if ($user->isAdmin == 1) {

                $shop = DB::table('shops')->where('user_id', $user->user_id)->first();

                $shop_subscription = DB::table('shop_subscriptions')
                    ->where('shop_id', $shop->shop_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($shop_subscription) {
                    $subscription_expiry_date = $shop_subscription->end_date;
                    $subscription_expiry_date = Carbon::parse($subscription_expiry_date)->format('d-m-Y H.i.s');
                }
                // EXPIRY DATE


            } else if ($user->isAdmin == 0) {

                $shop = Shop::find($user->staff->addedBy);

                // EXPIRY DATE
                // $shop_subscription = $shop->subscriptions()->latest()->first();

                $shop_subscription = DB::table('shop_subscriptions')
                    ->where('shop_id', $shop->shop_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($shop_subscription) {
                    // $subscription_expiry_date = $shop_subscription->pivot->end_date;
                    $subscription_expiry_date = $shop_subscription->end_date;

                    $subscription_expiry_date = Carbon::parse($subscription_expiry_date)->format('d-m-Y H.i.s');
                }
                // EXPIRY DATE
            }


            $isSubscriptionExpired = UserHelper::isSubscriptionExpired($shop_subscription, $subscription_expiry_date);


            if ($cachedData) {
                // return $cachedData; // If cached data exists, return it directly
                return response()->json([
                    'status' => 'success',
                    // 'data' => $cachedData,
                    'data' => $cachedData->original['data'],
                    'isSubscriptionExpired' => $isSubscriptionExpired
                ], 200);
            }

            $items = DB::connection('central')->table('subscriptions')
                ->where('subscription_id', '!=', 1)
                ->where('active', 1)
                ->get();

            // $subscriptionData = SubscriptionHelper::subsriptionDataFormat($shop_subscription->subscription_data);

            Cache::store('memcached')->put($cacheKey, response()->json([
                'status' => 'success',
                'data' => $items,
            ], 200));



            return response()->json([
                'status' => 'success',
                'data' => $items,
                'isSubscriptionExpired' => $isSubscriptionExpired
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
}
