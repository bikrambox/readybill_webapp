<?php

namespace Modules\GroceryIndia\Helpers;

use Illuminate\Support\Facades\DB;

class ItemHelpher
{

    public static function getFullUnit($short_unit)
    {
        $unitList = config('india_units.units');

        $key = array_search($short_unit, $unitList);

        if ($key !== false) {
            return $key;
        } else {
            return "NA";
        }
    }


    public static function checkSpecialCharacterFromString($item_name)
    {

        preg_match_all('/[^a-zA-Z0-9\s\-\+]/', $item_name, $special_chars);
        $special_chars = array_unique($special_chars[0]);

        $cleaned_name = preg_replace('/[^a-zA-Z0-9\s\-\+]/', ' ', $item_name);

        foreach ($special_chars as $char) {
            $cleaned_name = str_replace($char, ' ', $cleaned_name);
        }

        // Replace multiple whitespaces with a single whitespace
        $updated_name = preg_replace('/\s+/', ' ', $cleaned_name);

        // Trim whitespace from both ends
        $updated_name = trim($updated_name);

        return $updated_name;
    }


    public static function deleteItemFromCart($cart_id)
    {


        // Find the record in the table
        $item = DB::table('items_on_carts')->where('id', $cart_id)->first();

        // Check if the item exists
        if (!$item) {
            return [
                'status' => 'failed',
                'message' => __('validation.Item not found'),
                'code' => 404,
            ];
        }

        // Delete the record
        $deleted = DB::table('items_on_carts')->where('id', $cart_id)->delete();

        // Check if the deletion was successful
        if ($deleted) {
            return [
                'status' => 'success',
                'message' => __('validation.Item deleted from cart successfully'),
                'code' => 200,
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => __('validation.Failed to delete the item from cart'),
                'code' => 500,
            ];

        }

    }

    public static function deleteItemFromCartUsingItemID($item_id, $user_id, $location)
    {


        // Find the record in the table
        $item = DB::table('items_on_carts')->where('item_id', $item_id)
            ->where('user_id', $user_id)
            ->where('location', $location)
            ->first();

        // Check if the item exists
        if (!$item) {
            return [
                'status' => 'failed',
                'message' => __('validation.Item not found'),
                'code' => 404,
            ];
        }

        // Delete the record
        $deleted = DB::table('items_on_carts')->where('item_id', $item_id)
            ->where('user_id', $user_id)
            ->where('location', $location)
            ->delete();

        // Check if the deletion was successful
        if ($deleted) {
            return [
                'status' => 'success',
                'message' => __('validation.Item deleted from cart successfully'),
                'code' => 200,
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => __('validation.Failed to delete the item from cart'),
                'code' => 500,
            ];

        }

    }

}

?>