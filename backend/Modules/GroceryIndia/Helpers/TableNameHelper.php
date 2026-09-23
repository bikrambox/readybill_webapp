<?php

namespace Modules\GroceryIndia\Helpers;

use Modules\GroceryIndia\Entities\Shop;
use Modules\Authentication\Entities\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableNameHelper
{

    public static function getTableNameOfLoggedInUser()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $table_name = '';
            if ($user->isAdmin == 1) {
                $business_name = strtolower(str_replace(' ', '_', $user->shop->business_name)); // Replace spaces with underscores
                $table_name = 'item_' . $business_name . '_' . $user->mobile;
            } else if ($user->isAdmin == 0) {
                $staff = $user->staff;
                $adminDetail = Shop::find($staff->addedBy);
                $business_name = strtolower(str_replace(' ', '_', $adminDetail->business_name)); // Replace spaces with underscores
                $table_name = 'item_' . $business_name . '_' . $adminDetail->user->mobile;
            }

            return $table_name;
        }

        return null; // Return null if the user is not authenticated
    }

    public static function getTableNameUsingShopId($shop_id)
    {
        $table_name = '';

        $shop = Shop::find($shop_id);

        if (isset($shop)) {
            $business_name = strtolower(str_replace(' ', '_', $shop->user->shop->business_name));
            // Replace spaces with underscores
            $table_name = 'item_' . $business_name . '_' . $shop->user->mobile;

        }

        return $table_name;
    }

    // public static function getTableName($userId)
    // {

    //     $user = User::find($userId);

    //     $table_name = 'item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
    //     return $table_name;

    // }

    public static function getTableName($userId)
    {
        try {

            $user = User::find($userId);

            if (!$user) {

                Log::error('User not found', [
                    'user_id' => $userId
                ]);

                return null;
            }

            if (!$user->shop) {

                Log::error('Shop not found for user', [
                    'user_id' => $userId
                ]);

                return null;
            }

            $table_name = 'item_' . strtolower(
                str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile
            );

            // return 'dataset_'.$table_name;
            return $table_name;

        } catch (\Exception $e) {

            Log::error('Error generating table name', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return null;
        }
    }

    public static function getDatasetTableNameOfLoggedInUser()
    {
        if (!Auth::guard('api')->check()) {
            return null;
        }

        $user = Auth::guard('api')->user();

        $business_name = '';
        $mobile = '';

        if ($user->isAdmin == 1) {

            if (!$user->shop) {
                return null;
            }

            $business_name = $user->shop->business_name;
            $mobile = $user->mobile;

        } else {

            if (!$user->staff) {
                return null;
            }

            $adminDetail = Shop::find($user->staff->addedBy);

            if (!$adminDetail || !$adminDetail->user) {
                return null;
            }

            $business_name = $adminDetail->business_name;
            $mobile = $adminDetail->user->mobile;
        }

        // Sanitize business name (important)
        $business_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $business_name));

        return 'dataset_item_' . $business_name . '_' . $mobile;
    }

}


?>