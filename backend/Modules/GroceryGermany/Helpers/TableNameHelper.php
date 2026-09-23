<?php

namespace Modules\GroceryGermany\Helpers;

use Modules\GroceryGermany\Entities\Shop;
use Modules\Authentication\Entities\User;
use Auth;
use Illuminate\Support\Facades\DB;

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

    public static function getTableName($userId)
    {

        $user = User::find($userId);

        $table_name = 'item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
        return $table_name;

    }

}


?>