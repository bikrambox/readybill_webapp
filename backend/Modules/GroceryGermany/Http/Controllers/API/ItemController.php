<?php

namespace Modules\GroceryGermany\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

use Illuminate\Validation\Rule;

use Modules\Core\Helpers\ResponseHelper;
use Modules\GroceryGermany\Helpers\InventoryHelpher;
use Modules\GroceryGermany\Helpers\TableNameHelper;

use Modules\Authentication\Helpers\UserHelper;
use Modules\Core\Helpers\CountryHelpher;

use Modules\GroceryGermany\Entities\Shop;

class ItemController extends Controller
{
    public function addItem(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            // check user preference setting
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();


            $region = CountryHelpher::getCountryRegion($user->country_details);


            $response = InventoryHelpher::addInventory($request, $preferences, $table_name, $user);


            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'];


            // Prepare response data based on the response status
            $data = ($response['status'] == 200)
                ? ['data' => $response['data'] ?? []]
                : ['errors' => $response['errors'] ?? []];


            return ResponseHelper::responseFn($status, $response['status'], $message, $data);

        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }


    public function items(Request $request)
    {
        if (Auth::guard('api')->check()) {
            try {
                $draw = isset($request['draw']) ? intval($request['draw']) : 0;
                $start = isset($request['start']) ? intval($request['start']) : 0;
                $length = isset($request['length']) ? intval($request['length']) : 10;
                $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
                $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
                $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';
                $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'item_name';

                $user = Auth::guard('api')->user();


                $preferences = UserHelper::getUserPreference($user);
                $table_name = TableNameHelper::getTableNameOfLoggedInUser();

                // Build the base query
                $query = DB::table($table_name);

                // Apply search filter
                if ($searchValue !== "") {
                    if ($filter_option == 'item_name') {
                        $query->where('item_name', 'LIKE', "%$searchValue%");
                    } elseif ($filter_option == 'quantity') {
                        $query->where('quantity', 'LIKE', "%$searchValue%");
                    } elseif ($filter_option == 'mrp') {
                        if (is_numeric($searchValue)) {
                            $query->where('mrp', '=', $searchValue);
                        } else {
                            $query->where('mrp', '=', -1);
                        }
                    } elseif ($filter_option == 'sale_price') {
                        if (is_numeric($searchValue)) {
                            $query->where('sale_price', '=', $searchValue);
                        } else {
                            $query->where('sale_price', '=', -1);
                        }
                    } elseif ($filter_option == 'hsn') {
                        $query->where('hsn', 'LIKE', "%$searchValue%");
                    } elseif ($filter_option == 'gst') {
                        if (is_numeric($searchValue)) {
                            $query->where('rate1', '=', $searchValue);
                        } else {
                            $query->where('rate1', '=', -1);
                        }
                    } elseif ($filter_option == 'cess') {
                        if (is_numeric($searchValue)) {
                            $query->where('rate2', '=', $searchValue);
                        } else {
                            $query->where('rate2', '=', -1);
                        }
                    }
                }

                // Get filtered records count
                $recordsFiltered = (clone $query)->count();

                // Handle sorting
                $columns = ['item_name', 'quantity', 'sale_price'];
                $sortColumn = $columns[$sortColumnIndex] ?? 'item_name';
                $query->orderByRaw("CASE 
                WHEN quantity REGEXP '^[0-9]+\\.?[0-9]*$' 
                AND min_stock_alert REGEXP '^[0-9]+\\.?[0-9]*$' 
                AND CAST(quantity AS DECIMAL(10,2)) <= CAST(min_stock_alert AS DECIMAL(10,2)) THEN 0 
                ELSE 1 
                END, $sortColumn $sortDirection");

                // Paginate the results
                $query->offset($start)->limit($length);

                // Execute the query
                $items = $query->get();

                // Get total records count
                $totalRecords = DB::table($table_name)->count();

                // Check if the data is already cached
                $cacheKey = env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile;
                $cachedData = Cache::store('memcached')->get($cacheKey);

                if ($cachedData && $searchValue == "" && $filter_option == "") {
                    return $cachedData; // If cached data exists, return it directly
                }

                // Cache the response for future requests
                $response = response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $items,
                    'searchValue' => $searchValue,
                    'filter_option' => $filter_option,
                    'preferences' => $preferences,
                ], 200);

                Cache::store('memcached')->put($cacheKey, $response);

                // Return the response
                return $response;
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    // CACHE ADDED


    public function item($id)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();
            $region = CountryHelpher::getCountryRegion($user->country_details);
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $response = InventoryHelpher::item($id, $table_name);

            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'];

            $data = ($response['status'] == 200)
                ? [$response['data'] ?? []]
                : [$response['errors'] ?? []];

            return ResponseHelper::responseFn($status, $response['status'], $message, $data);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function allItems()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $response = InventoryHelpher::allItems($user, $preferences, $table_name);

            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'];


            // Prepare response data based on the response status
            $data = ($response['status'] == 200)
                ? ['data' => $response['data'] ?? []]
                : ['errors' => $response['errors'] ?? []];


            return ResponseHelper::responseFn($status, $response['status'], $message, $data);


        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function updateItem(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();
            // $table_name = 'item_' . $user->username;
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            // check user preference setting
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

            $region = CountryHelpher::getCountryRegion($user->country_details);


            $response = InventoryHelpher::updateItem($request, $preferences, $table_name, $user);


            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'];


            // Prepare response data based on the response status
            $data = ($response['status'] == 200)
                ? ['data' => $response['data'] ?? []]
                : [$response['errors'] ?? []];


            return ResponseHelper::responseFn($status, $response['status'], $message, $data);

        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }


    public function showProductSuggesstions(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $item = DB::table($table_name)
                ->select('id', 'item_name', 'short_unit', 'quantity')
                ->where('item_name', 'like', $request->item_name . '%')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $item,
                'count' => count($item),
                'item_name' => $request->item_name,
            ], 201);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    // check item stock quantity
    // public function checkStockQuantity(Request $request)
    // {
    //     if (Auth::guard('api')->check()) {

    //         $validate = Validator::make($request->all(), [
    //             'item_id' => 'required|numeric',
    //             'quantity' => 'required|numeric|gt:0',
    //             'relatedUnit' => 'required|not_in:null',
    //             // 'isRefund' => 'required|string|in:0,1,true,false',
    //             'isRefund' => 'nullable|string|in:0,1,true,false',
    //         ], [
    //             'item_id.required' => 'Please enter item name',
    //             'item_id.numeric' => 'Product Not Found',
    //         ]);

    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => __('validation.Validation Error'),
    //                 'data' => $validate->errors(),
    //             ], 403);
    //         }

    //         $user = Auth::guard('api')->user();
    //         // $table_name = 'item_' . $user->username;
    //         // $table_name = '';
    //         if ($user->isAdmin == 1) {
    //             // $table_name = 'item_' . $user->username;
    //             $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();
    //         } else if ($user->isAdmin == 0) {
    //             // $staff = $user->staff;
    //             // $adminDetail = User::find($staff->addedBy);
    //             $shop = Shop::find($user->staff->addedBy);

    //             $adminDetail = $shop->user;

    //             // dd($adminDetail);

    //             $preferences = DB::table('preferences')->where('user_id', $adminDetail->user_id)->first();
    //         }

    //         $table_name = TableNameHelper::getTableNameOfLoggedInUser();

    //         $item = DB::table($table_name)->select('quantity', 'item_name', 'sale_price', 'short_unit')->find($request->item_id);

    //         $itemOnCard = DB::table('items_on_carts')
    //             ->where('user_id', $user->user_id)
    //             ->where('item_id', $request->item_id)
    //             ->first();

    //         if ($item) {
    //             $amount = 0;

    //             // 0 - stock is not available.
    //             // 1 - stock is available.
    //             // 2 - item already added but add same item again but in that case inputed stock is exceed than available stock



    //             if ($request->isRefund == 0) {

    //                 if ($preferences->preference_quantity == 1) {

    //                     if (!is_numeric($item->quantity)) {
    //                         return response()->json([
    //                             'status' => 'success',
    //                             'stockStatus' => '2',
    //                             'data' => $item,
    //                         ], 200);
    //                     } else if ($itemOnCard) {

    //                         $stock = (float) $itemOnCard->quantity + (float) $request->quantity;
    //                         if ($stock > $item->quantity) {
    //                             return response()->json([
    //                                 'status' => 'success',
    //                                 'stockStatus' => '2',
    //                                 'availableStock' => $item->quantity,
    //                                 'qauantity_added_on_cart' => round((float) $itemOnCard->quantity, 2),
    //                                 'item_unit' => $item->short_unit

    //                             ], 200);
    //                         }
    //                     } else if (((float) $request->quantity <= (float) $item->quantity)) {

    //                         $amount = (float) $request->quantity * (float) $item->sale_price;
    //                         return response()->json([
    //                             'status' => 'success',
    //                             'stockStatus' => '1',
    //                             'data' => $item,
    //                             'amount' => round((float) $amount, 2),
    //                         ], 200);

    //                     } else {
    //                         return response()->json([
    //                             'status' => 'success',
    //                             'stockStatus' => '0',
    //                             'availableStock' => $item->quantity,
    //                             'item_unit' => $item->short_unit
    //                         ], 200);
    //                     }
    //                 } else if ($preferences->preference_quantity == 0) {

    //                     $amount = (float) $request->quantity * (float) $item->sale_price;
    //                     return response()->json([
    //                         'status' => 'success',
    //                         'stockStatus' => '1',
    //                         'data' => $item,
    //                         'amount' => round((float) $amount, 2),
    //                     ], 200);
    //                 }

    //             } else if ($request->isRefund == 1) {
    //                 $amount = (float) $request->quantity * (float) $item->sale_price;
    //                 return response()->json([
    //                     'status' => 'success',
    //                     'stockStatus' => '1',
    //                     'data' => $item,
    //                     'amount' => round((float) $amount * -1, 2),
    //                 ], 200);
    //             }

    //         } else {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => __('validation.No Product Found')
    //             ], 200);
    //         }

    //     }

    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => __('validation.Unauthorized')
    //     ], 401);
    // }

    public function checkStockQuantity(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $validate = Validator::make($request->all(), [
                'item_id' => 'required|numeric',
                'quantity' => 'required|numeric|gt:0',
                'relatedUnit' => 'required|not_in:null',
                'isRefund' => 'nullable|string|in:0,1,true,false',
            ], [
                'item_id.required' => 'Please enter item name',
                'item_id.numeric' => 'Product Not Found',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            $user = Auth::guard('api')->user();

            if ($user->isAdmin == 1) {
                $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();
            } else if ($user->isAdmin == 0) {
                $shop = Shop::find($user->staff->addedBy);
                $adminDetail = $shop->user;
                $preferences = DB::table('preferences')->where('user_id', $adminDetail->user_id)->first();
            }

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $item = DB::table($table_name)
                ->select('quantity', 'item_name', 'sale_price', 'short_unit')
                ->find($request->item_id);

            $itemOnCard = DB::table('items_on_carts')
                ->where('user_id', $user->user_id)
                ->where('item_id', $request->item_id)
                ->first();

            if ($item) {
                $amount = 0;

                // stockStatus:
                // 0 - stock not available
                // 1 - stock available
                // 2 - item already on cart but input quantity exceeds available stock

                if ($request->isRefund == 0 || $request->isRefund === null) {

                    if ($preferences->preference_quantity == 1) {

                        if (!is_numeric($item->quantity)) {
                            // Quantity is not tracked (e.g. unlimited / N/A)
                            return response()->json([
                                'status' => 'success',
                                'stockStatus' => '2',
                                'data' => $item,
                            ], 200);

                        } else if ($itemOnCard) {

                            $stock = (float) $itemOnCard->quantity + (float) $request->quantity;

                            if ($stock > (float) $item->quantity) {
                                // Cart qty + new qty exceeds available stock
                                return response()->json([
                                    'status' => 'success',
                                    'stockStatus' => '2',
                                    'availableStock' => $item->quantity,
                                    'qauantity_added_on_cart' => round((float) $itemOnCard->quantity, 2),
                                    'item_unit' => $item->short_unit,
                                ], 200);
                            }

                            // ✅ FIX: Cart qty + new qty is within stock — allow it
                            $amount = (float) $request->quantity * (float) $item->sale_price;
                            return response()->json([
                                'status' => 'success',
                                'stockStatus' => '1',
                                'data' => $item,
                                'amount' => round((float) $amount, 2),
                            ], 200);

                        } else if ((float) $request->quantity <= (float) $item->quantity) {

                            $amount = (float) $request->quantity * (float) $item->sale_price;
                            return response()->json([
                                'status' => 'success',
                                'stockStatus' => '1',
                                'data' => $item,
                                'amount' => round((float) $amount, 2),
                            ], 200);

                        } else {
                            // Requested quantity exceeds available stock
                            return response()->json([
                                'status' => 'success',
                                'stockStatus' => '0',
                                'availableStock' => $item->quantity,
                                'item_unit' => $item->short_unit,
                            ], 200);
                        }

                    } else if ($preferences->preference_quantity == 0) {
                        // Stock tracking disabled — always allow
                        $amount = (float) $request->quantity * (float) $item->sale_price;
                        return response()->json([
                            'status' => 'success',
                            'stockStatus' => '1',
                            'data' => $item,
                            'amount' => round((float) $amount, 2),
                        ], 200);
                    }

                } else if ($request->isRefund == 1) {
                    $amount = (float) $request->quantity * (float) $item->sale_price;
                    return response()->json([
                        'status' => 'success',
                        'stockStatus' => '1',
                        'data' => $item,
                        'amount' => round((float) $amount * -1, 2),
                    ], 200);
                }

                // ✅ FIX: Catch-all for any unhandled parameter combination inside $item block
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Invalid request parameters'),
                ], 422);

            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.No Product Found'),
                ], 200);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    // check item stock quantity


    public function getRelatedUnitList(Request $request)
    {

        if (Auth::guard('api')->check()) {

            $validate = Validator::make($request->all(), [
                'item_id' => 'required|numeric',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            $user = Auth::guard('api')->user();
            // // $table_name = 'item_' . $user->username;
            // $table_name = '';
            // if ($user->isAdmin == 1) {
            //     $table_name = 'item_' . $user->username;
            // } else if ($user->isAdmin == 0) {
            //     $adminDetail = User::find($user->addedBy);
            //     $table_name = 'item_' . $adminDetail->username;
            // }

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $item = DB::table($table_name)->find($request->item_id);
            if ($item) {
                $units = [];

                $itemUnit = strtolower($item->short_unit);
                if (($itemUnit == 'kg') || ($itemUnit == 'gm')) {
                    $units = ['KG', 'GM'];
                } else if (($itemUnit == 'ltr') || ($itemUnit == 'ml')) {
                    $units = ['KG', 'GM', 'LTR', 'ML'];
                }
                // else if( ($itemUnit == 'dzn') || ($itemUnit == 'pcs') ){
                //     $units = ['DZN','PCS'];
                // }
                else {
                    $units[0] = $item->short_unit;
                }

                return response()->json([
                    'status' => 'success',
                    'units' => $units,
                    'item_unit' => $item->short_unit
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.No Product Found')
                ], 200);
            }


        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function deleteItem($id)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            // Get the table name based on the logged-in user
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            // Check if the table name is valid
            if (!$table_name) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Table not found')
                ], 404);
            }

            // Find the record in the table
            $item = DB::table($table_name)->where('id', $id)->first();

            $cacheKey = env('CACHE_KEY_PREFIX') . 'all_items_' . Auth::guard('api')->user()->mobile;
            Cache::store('memcached')->forget($cacheKey);

            // Check if the item exists
            if (!$item) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Item not found')
                ], 404);
            }

            // Delete the record
            $deleted = DB::table($table_name)->where('id', $id)->delete();

            // Check if the deletion was successful
            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('validation.Item deleted successfully')
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Failed to delete the item')
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function multipleDeleteOption(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $table_name = TableNameHelper::getTableNameOfLoggedInUser();

        if (!$table_name) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Table not found')
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:' . $table_name . ',id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error'),
                'errors' => $validator->errors()
            ], 422);
        }

        $ids = $request->input('ids');

        // Fetch items to ensure they exist before deletion
        $items = DB::table($table_name)->whereIn('id', $ids)->get();
        if ($items->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.No matching items found')
            ], 404);
        }

        // Delete the items
        $deleted = DB::table($table_name)->whereIn('id', $ids)->delete();

        // Clear the cache after deletion
        $cacheKey = env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile;
        Cache::store('memcached')->forget($cacheKey);

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => __('validation.Items deleted successfully')
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Failed to delete items')
        ], 500);
    }


    public function getDataByBarcode($barcode)
    {

        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();
            // $region = CountryHelpher::getCountryRegion($user->country_details);

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $response = InventoryHelpher::getDataByBarcode($barcode, $table_name);

            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'];

            $data = ($response['status'] == 200)
                ? [$response['data'] ?? []]
                : [$response['errors'] ?? []];

            return ResponseHelper::responseFn($status, $response['status'], $message, $data);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);

    }


    // check barcode exsits or not
    public function checkBarcodeExistsOrNot($barcode)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $response = InventoryHelpher::checkBarcodeExistsOrNot($barcode, $table_name);

            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'] ?? '';

            $data = $response['status'] === 200
                ? $response['data']
                : $response['errors'];

            return ResponseHelper::responseFn(
                $status,
                $response['status'],
                $message,
                $data
            );
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

}
