<?php

namespace Modules\Authentication\Helpers;

use Illuminate\Support\Facades\DB;

use Exception;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Authentication\Helpers\UserHelper;

class SubscriptionHelper
{

    /**
     * Check if route is permanently restricted (blocked) for free plan
     */
    public static function isRestrictedRoute($route, $path)
    {
        $restrictedRoutes = config('subscription.restricted_routes', []);

        // Normalize the path
        $normalizedPath = trim($path, '/');

        foreach ($restrictedRoutes as $restrictedRoute) {
            $normalizedRestricted = trim($restrictedRoute, '/');

            if ($route === $normalizedRestricted || $normalizedPath === $normalizedRestricted) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get report data access years based on subscription
     */
    public static function getReportDataYears($shopId, $module_type)
    {
        try {
            $shopSubscription = DB::connection($module_type)
                ->table('shop_subscriptions')
                ->where('shop_id', $shopId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$shopSubscription) {
                return config('subscription.free_plan_report_data_years', 1);
            }

            $subscriptionData = self::subsriptionDataFormat($shopSubscription->subscription_data);

            // $isFree = $shopSubscription->subscription_id == config('subscription.free_subscription_id', 1);
            $isFree = $subscriptionData['subscription_id'] == config('subscription.free_subscription_id', 1);

            if ($isFree) {
                return config('subscription.free_plan_report_data_years', 1);
            } else {
                return config('subscription.premium_plan_report_data_years', 3);
            }

        } catch (Exception $e) {
            \Log::error('Get Report Data Years Error', [
                'error' => $e->getMessage(),
            ]);
            return config('subscription.free_plan_report_data_years', 1);
        }
    }

    /**
     * Assign subscription to a shop
     */
    public static function assignSubscription($shop_id, $subscription_id, $module_type)
    {
        $shop = DB::connection($module_type)->table('shops')->where('shop_id', $shop_id)->first();

        if (!$shop) {
            return [
                'success' => false,
                'message' => __('validation.Shop not found')
            ];
        }

        // Get subscription details from central connection
        $subscription = DB::connection('central')
            ->table('subscriptions')
            ->where('subscription_id', $subscription_id)
            ->first();

        if (!$subscription) {
            return [
                'success' => false,
                'message' => __('validation.Subscription plan not found')
            ];
        }

        $startDate = $shop->created_at;

        // Check if it's a free plan
        $isFree = $subscription_id == config('subscription.free_subscription_id', 1);

        if ($isFree) {
            // // Free plan: Set end_date to 1 year (logic will skip expiry check)
            // $endDate = Carbon::parse($startDate)->addYear();
            $endDate = Carbon::parse($startDate)->addYears(env('SUBSCRIPTION_FREE_PLAN_EXPIRY_DAYS'));

            $paymentStatus = 'free';
        } else {
            // Paid plan: calculate end date from months field
            $endDate = Carbon::parse($startDate)->addMonths($subscription->months);
            $paymentStatus = 'pending';
        }

        $data = [
            'shop_id' => $shop_id,
            // 'subscription_id' => $subscription_id,
            'subscription_data' => json_encode((array) $subscription), // ← full snapshot as JSON
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_status' => $paymentStatus,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $result = DB::connection($module_type)->table('shop_subscriptions')->insert($data);

        return [
            'success' => $result,
            'message' => $result ? __('validation.Subscription assigned successfully') : __('validation.Failed to assign subscription')
        ];
    }

    /**
     * Check if subscription has expired
     */
    public static function isSubscriptionExpired($subscription, $endDate)
    {
        $currentDate = Carbon::now();
        $endOfDay = Carbon::parse($endDate)->endOfDay();

        return $currentDate->greaterThan($endOfDay) ? 1 : 0;
    }

    /**
     * Check shop subscription validity
     */
    public static function checkShopSubscription($shopId, $module_type)
    {
        try {
            $subscription = DB::connection($module_type)->table('shop_subscriptions')
                ->where('shop_id', $shopId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$subscription) {
                return [
                    'status' => 0,
                    'message' => __('validation.No subscription found for the shop'),
                    'code' => 403,
                ];
            }

            $subscriptionData = self::subsriptionDataFormat($subscription->subscription_data);


            // Check if it's a free plan - free plans never expire at subscription level
            // $isFree = $subscription->subscription_id == config('subscription.free_subscription_id', 1);
            $isFree = $subscriptionData['subscription_id'] == config('subscription.free_subscription_id', 1);


            if (!$isFree) {
                // Only check expiry for paid plans
                if (self::isSubscriptionExpired($subscription, $subscription->end_date) == 1) {
                    return [
                        'status' => 0,
                        'message' => __('validation.The shop subscription has expired'),
                        'code' => 200,
                    ];
                }
            }

            // Check payment status
            if (!in_array($subscription->payment_status, ['free', 'paid'])) {
                return [
                    'status' => 0,
                    'message' => __('validation.The subscription payment status is not valid'),
                    'code' => 403,
                ];
            }

            return [
                'status' => 1,
                'message' => __('valid.Valid subscription'),
                'code' => 200,
            ];

        } catch (Exception $e) {
            report($e);
            \Log::error('Shop Subscription Check Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 0,
                'message' => __('validation.Internal Server Error'),
                'code' => 500,
            ];
        }
    }

    /**
     * Check feature access based on subscription plan
     */
    public static function checkFeatureAccess($shopId, $module_type, $route, $path)
    {
        try {
            // Get shop subscription from module connection
            $shopSubscription = DB::connection($module_type)
                ->table('shop_subscriptions')
                ->where('shop_id', $shopId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$shopSubscription) {
                return [
                    'status' => 0,
                    'message' => __('validation.No subscription found'),
                    'code' => 403,
                ];
            }

            $subscriptionData = self::subsriptionDataFormat($shopSubscription->subscription_data);

            // // Get subscription details from central connection
            // $subscription = DB::connection('central')
            //     ->table('subscriptions')
            //     ->where('subscription_id', $subscriptionData['subscription_id'])
            //     ->first();

            if (!$subscriptionData) {
                return [
                    'status' => 0,
                    'message' => __('validation.Subscription plan not found'),
                    'code' => 403,
                ];
            }

            $isFree = $subscriptionData['subscription_id'] == config('subscription.free_subscription_id', 1);

            // If not a free plan, allow all access
            if (!$isFree) {
                return [
                    'status' => 1,
                    'message' => __('valid.Feature access allowed'),
                    'code' => 200,
                ];
            }

            // Check free plan access configuration
            $freePlanAccess = config('subscription.free_plan_access', 'restricted');

            // If free plan has full access, allow all
            if ($freePlanAccess === 'full') {
                return [
                    'status' => 1,
                    'message' => __('valid.Feature access allowed'),
                    'code' => 200,
                ];
            }

            // Check if route is permanently restricted
            $isPermanentlyRestricted = self::isRestrictedRoute($route, $path);

            if ($isPermanentlyRestricted) {
                // These routes are ALWAYS blocked for free users
                return [
                    'status' => 0,
                    'message' => __('validation.This feature requires a premium subscription. Please upgrade your plan.'),
                    'code' => 403,
                ];
            }

            // All other routes are accessible
            return [
                'status' => 1,
                'message' => __('valid.Feature access allowed'),
                'code' => 200,
            ];

        } catch (Exception $e) {
            report($e);
            \Log::error('Feature Access Check Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 0,
                'message' => __('validation.Internal Server Error'),
                'code' => 500,
            ];
        }
    }

    public static function getShopSubscription($shopId, $module_type)
    {

        try {

            $subscription = DB::connection($module_type)->table('shop_subscriptions')
                ->where('shop_id', $shopId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$subscription) {
                return [
                    'status' => 0,
                    'message' => __('validation.No subscription found for the shop'),
                    'code' => 403,
                ];
            }

            $subscription_expiry_date = Carbon::parse($subscription->end_date)->format('d-m-Y H.i.s');

            $isSubscriptionExpired = UserHelper::isSubscriptionExpired($subscription, $subscription_expiry_date);


            $subscriptionData = self::subsriptionDataFormat($subscription->subscription_data);


            // Check if it's a free plan - free plans never expire at subscription level
            // $isFree = $subscription->subscription_id == config('subscription.free_subscription_id', 1);
            $isFree = $subscriptionData['subscription_id'] == config('subscription.free_subscription_id', 1);


            $reportDataYearAccess = self::getReportDataYears($shopId, $module_type);

            return [
                'status' => 1,
                'message' => __('validation.Valid subscription'),
                'code' => 200,
                // $subscription,
                'shop_subscription_id' => $subscription->id,
                'shop_id' => $subscription->shop_id,
                'subscription_id' => $subscriptionData['subscription_id'],
                'end_date' => $subscription->end_date,
                'payment_status' => $subscription->payment_status,
                'payment_mode' => $subscription->payment_mode,
                'payment_reference' => $subscription->payment_reference,
                'isSubscriptionExpired' => $isSubscriptionExpired,
                'isFreeSubscriptionPlan' => ($isFree == true) ? 1 : 0,
                'reportDataYearAccess' => $reportDataYearAccess,
                'current_plan_data' => $subscriptionData,
            ];

        } catch (Exception $e) {
            report($e);
            \Log::error('Shop Subscription Check Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 0,
                'message' => __('validation.Internal Server Error'),
                'code' => 500,
            ];
        }

    }

    public static function subsriptionDataFormat($data)
    {
        // If it's a JSON string, decode it
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        // Convert object to array if needed
        if (is_object($data)) {
            $data = (array) $data;
        }

        // Safety check
        if (!is_array($data)) {
            return [];
        }

        return [
            'subscription_id' => $data['subscription_id'] ?? null,
            'plan_name' => $data['plan_name'] ?? '',
            'price' => (float) ($data['price'] ?? 0),
            'duration' => (int) ($data['months'] ?? 0),
            'is_free' => ($data['price'] ?? 0) == 0,
            'is_active' => (bool) ($data['active'] ?? false),
            'best_value' => (bool) ($data['is_best_value'] ?? false),
            'shop_type' => $data['shop_type'] ?? '',
            'heading' => $data['heading'] ?? '',
            'subheading' => $data['subheading'] ?? '',
            'description' => $data['description'] ?? '',
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }

}