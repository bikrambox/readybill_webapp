<?php

namespace Modules\GroceryGermany\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Validator;
use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Modules\GroceryGermany\Entities\ItemsOnCart;

use Modules\GroceryGermany\Helpers\TableNameHelper;
use Modules\GroceryGermany\Helpers\ItemHelpher;

class ItemsOnCartController extends Controller
{
    public function addItemOnCart(Request $request)
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $validate = Validator::make(
                $request->all(),
                [
                    'item_id' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($table_name) {
                            // Check if the item exists in the given table
                            $isItemExists = DB::table($table_name)->find($value);

                            // If the item doesn't exist, fail validation
                            if (!$isItemExists) {
                                $fail(__('validation.Item Not Found'));
                            }
                        }
                    ],
                    'item_name' => 'required|max:200',
                    'sale_price' => [
                        'required',
                        'numeric',
                        'gt:0',
                        'max:100000',
                    ],
                    'quantity' => 'required|numeric|gte:0',
                    'item_unit' => ['required', Rule::in(array_values(config('german_units.units')))],
                    'location' => 'required|in:sell,refund',
                    'isRefund' => 'required|in:0,1',
                ],
                [
                    'item_id.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.item_id')]),
                    'item_id.numeric' => __('item_on_cart_validation.numeric', ['attribute' => __('item_on_cart_validation.attributes.item_id')]),
                    'item_name.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.item_name')]),
                    'item_name.max' => __('item_on_cart_validation.max', ['attribute' => __('item_on_cart_validation.attributes.item_name'), 'max' => 200]),
                    'sale_price.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.sale_price')]),
                    'sale_price.numeric' => __('item_on_cart_validation.numeric', ['attribute' => __('item_on_cart_validation.attributes.sale_price')]),
                    'sale_price.gt' => __('item_on_cart_validation.gt', ['attribute' => __('item_on_cart_validation.attributes.sale_price'), 'gt' => 0]),
                    'sale_price.max' => __('item_on_cart_validation.max_numeric', ['attribute' => __('item_on_cart_validation.attributes.sale_price'), 'max_numeric' => 100000]),
                    'quantity.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.quantity')]),
                    'quantity.numeric' => __('item_on_cart_validation.numeric', ['attribute' => __('item_on_cart_validation.attributes.quantity')]),
                    'quantity.gte' => __('item_on_cart_validation.gte', ['attribute' => __('item_on_cart_validation.attributes.quantity'), 'gte' => 0]),
                    'item_unit.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.item_unit')]),
                    'item_unit.in' => __('item_on_cart_validation.invalid', ['attribute' => __('item_on_cart_validation.attributes.item_unit')]),
                    'location.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.location')]),
                    'location.in' => __('item_on_cart_validation.invalid', ['attribute' => __('item_on_cart_validation.attributes.location')]),
                    'isRefund.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.isRefund')]),
                    'isRefund.in' => __('item_on_cart_validation.invalid', ['attribute' => __('item_on_cart_validation.attributes.isRefund')]),
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            $itemExsists = ItemsOnCart::where('user_id', $user->user_id)
                ->where('isRefund', $request->isRefund)
                ->where('item_id', $request->item_id)->first();

            if ($itemExsists) {
                $itemExsists->quantity = (float) $itemExsists->quantity + (float) $request->quantity;
                $itemExsists->save();
            } else {
                $itemOnCart = new ItemsOnCart();
                $itemOnCart->user_id = $user->user_id;
                $itemOnCart->item_id = $request->item_id;
                $itemOnCart->item_name = $request->item_name;
                $itemOnCart->sale_price = $request->sale_price;
                $itemOnCart->quantity = $request->quantity;
                $itemOnCart->item_unit = $request->item_unit;
                $itemOnCart->location = $request->location;
                $itemOnCart->isRefund = $request->isRefund;
                $itemOnCart->save();
            }

            $response = [
                'status' => 'success',
                'message' => __('validation.New Item Successfully Added on Cart'),
                'data' => $itemOnCart ?? $itemExsists
            ];
            return response()->json($response, 200);

        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    // public function getItemsByUserId($location)
    // {

    //     if (Auth::guard('api')->check()) {
    //         $user = Auth::guard('api')->user();

    //         // $itemsOnCart = $user->cartItems;

    //         $itemsOnCart = ItemsOnCart::where('location', $location)
    //             ->where('user_id', $user->user_id)
    //             ->get();

    //         $response = [
    //             'status' => 'success',
    //             'message' => __('validation.Item List'),
    //             'data' => $itemsOnCart
    //         ];
    //         return response()->json($response, 200);

    //     }

    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => __('validation.Unauthorized')
    //     ], 401);

    // }


    public function getItemsByUserId($location)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $itemsOnCart = ItemsOnCart::where('location', $location)
                ->where('user_id', $user->user_id)
                ->get();

            // get all item_ids from cart
            $itemIds = $itemsOnCart->pluck('item_id')->toArray();

            // fetch valid item ids from dynamic table
            $validItemIds = DB::table($table_name)
                ->whereIn('id', $itemIds)
                ->pluck('id')
                ->toArray();

            // filter cart items
            $itemList = $itemsOnCart->whereIn('item_id', $validItemIds)->values();

            return response()->json([
                'status' => 'success',
                'message' => __('validation.Item List'),
                'data' => $itemList
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }



    public function deleteItemFromCart($id)
    {
        if (Auth::guard('api')->check()) {


            $response = ItemHelpher::deleteItemFromCart($id);

            return response()->json([
                'status' => $response['status'],
                'message' => $response['message']
            ], $response['code']);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function updateItemOnCart(Request $request)
    {

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $validate = Validator::make(
                $request->all(),
                [
                    'cart_id' => 'required|exists:items_on_carts,id',

                    'item_id' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($table_name) {
                            // Check if the item exists in the given table
                            $isItemExists = DB::table($table_name)->find($value);

                            // If the item doesn't exist, fail validation
                            if (!$isItemExists) {
                                $fail(__('validation.Item Not Found'));
                            }
                        }
                    ],
                    'item_name' => 'required|max:200',
                    'sale_price' => [
                        'required',
                        'numeric',
                        'gt:0',
                        'max:100000',
                    ],
                    'quantity' => 'required|numeric|gte:0',
                    'location' => 'required|in:sell,refund',
                    'isRefund' => 'required|in:0,1',
                ],
                [
                    'cart_id.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.cart_id')]),
                    'cart_id.exists' => __('item_on_cart_validation.exists', ['attribute' => __('item_on_cart_validation.attributes.cart_id')]),
                    'item_id.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.item_id')]),
                    'item_id.numeric' => __('item_on_cart_validation.numeric', ['attribute' => __('item_on_cart_validation.attributes.item_id')]),
                    'item_name.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.item_name')]),
                    'item_name.max' => __('item_on_cart_validation.max', ['attribute' => __('item_on_cart_validation.attributes.item_name'), 'max' => 200]),
                    'sale_price.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.sale_price')]),
                    'sale_price.numeric' => __('item_on_cart_validation.numeric', ['attribute' => __('item_on_cart_validation.attributes.sale_price')]),
                    'sale_price.gt' => __('item_on_cart_validation.gt', ['attribute' => __('item_on_cart_validation.attributes.sale_price'), 'gt' => 0]),
                    'sale_price.max' => __('item_on_cart_validation.max_numeric', ['attribute' => __('item_on_cart_validation.attributes.sale_price'), 'max_numeric' => 100000]),
                    'quantity.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.quantity')]),
                    'quantity.numeric' => __('item_on_cart_validation.numeric', ['attribute' => __('item_on_cart_validation.attributes.quantity')]),
                    'quantity.gte' => __('item_on_cart_validation.gte', ['attribute' => __('item_on_cart_validation.attributes.quantity'), 'gte' => 0]),
                    'location.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.location')]),
                    'location.in' => __('item_on_cart_validation.invalid', ['attribute' => __('item_on_cart_validation.attributes.location')]),
                    'isRefund.required' => __('item_on_cart_validation.required', ['attribute' => __('item_on_cart_validation.attributes.isRefund')]),
                    'isRefund.in' => __('item_on_cart_validation.invalid', ['attribute' => __('item_on_cart_validation.attributes.isRefund')]),
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            $itemOnCart = ItemsOnCart::find($request->cart_id);
            $itemOnCart->user_id = $user->user_id;
            $itemOnCart->item_id = $request->item_id;
            $itemOnCart->item_name = $request->item_name;
            $itemOnCart->sale_price = $request->sale_price;
            $itemOnCart->quantity = $request->quantity;
            $itemOnCart->location = $request->location;
            $itemOnCart->isRefund = $request->isRefund;
            $itemOnCart->save();


            $response = [
                'status' => 'success',
                'message' => __('validation.Item Updated Successfully'),
                'data' => $itemOnCart
            ];
            return response()->json($response, 200);

        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function deleteAllItemOnCart($location)
    {
        try {
            // Validate input
            $validated = validator(['location' => $location], [
                'location' => 'required|in:sell,refund',
            ])->validate();

            // Check authentication
            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Unauthorized')
                ], 401);
            }

            // Optionally, verify user ownership of the cart
            $user = Auth::guard('api')->user();
            $affectedRows = DB::table('items_on_carts')
                ->where('location', $location)
                // ->where('user_id', $user->id) // Uncomment if cart is user-specific
                ->delete();

            // Deletion is successful if no exception occurs
            return response()->json([
                'status' => 'success',
                'message' => __('validation.All items deleted successfully'),
                'affected_rows' => $affectedRows
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Invalid location provided'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Failed to delete items'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
