<?php

namespace Modules\GroceryIndia\Helpers;

use Illuminate\Support\Facades\DB;

use Modules\GroceryIndia\Entities\Notification;
use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\ShopSubscriptions;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Validator;

use Modules\GroceryIndia\Helpers\TableNameHelper;

class NotificationHelpher
{
    const SUBSCRIPTION_ALERT_DAYS = [10, 7, 5, 2, 1];

    /**
     * Check and generate subscription expiry notifications for a user.
     */
    public static function checkSubscriptionExpiry(User $user): void
    {
        if (!$user) {
            return;
        }

        $shopId = $user->shop->shop_id ?? 0;

        if (!$shopId) {
            return;
        }

        $subscription = ShopSubscriptions::where('shop_id', $shopId)
            ->orderBy('created_at', 'desc')
            ->select('end_date')
            ->first();

        if (!$subscription || !$subscription->end_date) {
            return;
        }

        $currentDate = Carbon::now();
        $expiryDate = Carbon::parse($subscription->end_date)->endOfDay();

        $daysLeft = (int) $currentDate->diffInDays($expiryDate, false);

        if (!in_array($daysLeft, self::SUBSCRIPTION_ALERT_DAYS)) {
            return;
        }

        $alreadyExists = Notification::where('user_id', $user->user_id)
            ->where('type', 'subscription_expiry')
            ->whereDate('created_at', Carbon::today())
            ->whereJsonContains('data->days_left', $daysLeft)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        Notification::create([
            'user_id' => $user->user_id,
            'type' => 'subscription_expiry',
            'title' => 'Subscription Expiring Soon',
            'message' => "Your subscription will expire in {$daysLeft} day(s). Please renew to avoid interruption.",
            'data' => [
                'days_left' => $daysLeft,
                'expiry_date' => $subscription->end_date,
            ],
        ]);
    }

    /**
     * Check and generate stock alert notifications for a user's products.
     * Skips entirely if stock preference is disabled.
     */
    public static function checkStockAlerts(User $user): void
    {
        // Gate: skip if stock preference is disabled
        if (!$user->preference?->preference_quantity) {
            return;
        }

        $table_name = TableNameHelper::getTableName($user->user_id);

        $alertProducts = DB::connection($user->module_type)->table($table_name)
            ->where(function ($query) {
                $query->where('quantity', '<=', 0)
                    ->orWhereRaw('quantity <= min_stock_alert');
            })
            ->select('id', 'item_name', 'quantity', 'min_stock_alert')
            ->get();

        foreach ($alertProducts as $product) {
            $alreadyExists = Notification::where('user_id', $user->user_id)
                ->where('type', 'stock_alert')
                ->whereDate('created_at', Carbon::today())
                ->whereJsonContains('data->product_id', $product->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $isZero = $product->quantity <= 0;
            $reason = $isZero ? 'Out of Stock' : 'Reached Minimum Stock Level';

            Notification::create([
                'user_id' => $user->user_id,
                'type' => 'stock_alert',
                'title' => "Stock Alert: {$product->item_name}",
                'message' => "{$product->item_name} is {$reason}. Current stock: " . number_format($product->quantity, 2) . ".",
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->item_name,
                    'stock' => number_format($product->quantity, 2),
                    'minimum_stock' => number_format($product->min_stock_alert, 2),
                    'reason' => $reason,
                ],
            ]);
        }
    }

}