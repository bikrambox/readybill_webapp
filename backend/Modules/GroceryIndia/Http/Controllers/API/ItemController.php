<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Validator;
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
use Modules\GroceryIndia\Rules\ValidBarcode;
use Modules\Core\Rules\NoScriptTag;

use Modules\Core\Helpers\ResponseHelper;
use Modules\GroceryIndia\Helpers\InventoryHelpher;
use Modules\GroceryIndia\Helpers\TableNameHelper;


use Modules\Authentication\Helpers\UserHelper;
use Modules\Core\Helpers\CountryHelpher;


use Modules\GroceryIndia\Entities\Shop;
use Modules\GroceryIndia\Entities\ItemsOnCart;

use Exception;



class ItemController extends Controller
{
    public function addItem(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            // $table_name = 'item_'.$user->username;
            // $table_name = 'item_' . $user->admin->username;

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

                // dd($start,$length);

                // Build the base query
                // $query = DB::table($table_name);

                $query = DB::table($table_name)
                    ->leftJoin('categories', 'categories.id', '=', $table_name . '.category_id')
                    ->select(
                        $table_name . '.*',
                        DB::raw("COALESCE(categories.name, 'NA') as category_name"),
                        DB::raw("COALESCE($table_name.sku, 'NA') as sku")
                    );

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
                    } elseif ($filter_option == 'sku') {
                        $query->where('sku', 'LIKE', "%$searchValue%");
                    } elseif ($filter_option == 'category') {
                        $query->where('categories.name', 'LIKE', "%$searchValue%");
                    }


                }

                // Get filtered records count
                $recordsFiltered = (clone $query)->count();

                // Column mapping
                if (isset($request['columns']) && isset($request['columns'][$sortColumnIndex]['data'])) {
                    $sortColumn = $request['columns'][$sortColumnIndex]['data'];
                } else {
                    $columns = [
                        1 => 'item_name',     // Column 1
                        2 => 'quantity',      // Column 2
                        3 => 'mrp',          // Column 3
                        4 => 'sale_price',   // Column 4
                        5 => 'short_unit',   // Column 5
                    ];
                    $sortColumn = $columns[$sortColumnIndex] ?? 'item_name';
                }

                // Define numeric columns
                $numericColumns = ['quantity', 'mrp', 'sale_price'];

                // Apply proper sorting - treating 0 as a valid number
                if (in_array($sortColumn, $numericColumns)) {
                    // Sort numeric columns: NULL/empty last, but 0 is treated as valid number
                    $query->orderByRaw("
                    CASE 
                        WHEN $sortColumn IS NULL OR $sortColumn = '' THEN 1
                        ELSE 0
                    END,
                    CAST(COALESCE(NULLIF($sortColumn, ''), '0') AS DECIMAL(10,2)) $sortDirection
                ");
                } else {
                    // Text sorting with NULL/empty handling
                    $query->orderByRaw("
                    CASE 
                        WHEN $sortColumn IS NULL OR $sortColumn = '' THEN 1
                        ELSE 0
                    END,
                    $sortColumn $sortDirection
                ");
                }

                // Paginate
                $query->offset($start)->limit($length);

                // Execute
                $items = $query->get();

                // Get total records count
                $totalRecords = DB::table($table_name)->count();

                // Cache handling
                $cacheKey = env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile;
                $cachedData = Cache::store('memcached')->get($cacheKey);

                if ($cachedData && $searchValue == "" && $filter_option == "" && $sortColumnIndex == 0) {
                    return $cachedData;
                }

                $response = response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $items,
                    'searchValue' => $searchValue,
                    'filter_option' => $filter_option,
                    'preferences' => $preferences,
                ], 200);

                if ($searchValue == "" && $filter_option == "" && $sortColumnIndex == 0) {
                    Cache::store('memcached')->put($cacheKey, $response);
                }

                return $response;
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process'),
                    // 'message' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }


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

    public function editBasedOnCell(Request $request)
    {

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            // $table_name = 'item_' . $user->username;
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            switch ($request->columnIndex) {
                case 0:
                    $validate = Validator::make($request->all(), [
                        'id' => 'required',
                        'newValue' => 'required',
                    ]);
                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    } else if (!$validate->fails()) {
                        DB::table($table_name)->where('id', $request->id)->update(['item_name' => $request->newValue]);
                    }
                    break;

                case 1:

                    $validate = Validator::make($request->all(), [
                        'id' => 'required',
                        'newValue' => 'nullable|numeric|gt:0',
                    ]);
                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    } else if (!$validate->fails()) {
                        DB::table($table_name)->where('id', $request->id)->update(['quantity' => $request->newValue]);
                    }
                    break;

                case 6:
                    $validate = Validator::make($request->all(), [
                        'id' => 'required',
                        'newValue' => 'nullable|required_with:tax1|gte:0|between:0,100',
                    ]);
                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    } else if (!$validate->fails()) {
                        $checkTax = DB::table($table_name)->find($request->id);

                        if ($checkTax->tax1 != 'NA') {
                            DB::table($table_name)->where('id', $request->id)->update(['rate1' => $request->newValue]);
                        } else {
                            DB::table($table_name)->where('id', $request->id)->update([
                                'tax1' => 'GST',
                                'rate1' => $request->newValue
                            ]);
                        }
                    }
                    break;


                case 7:
                    $validate = Validator::make($request->all(), [
                        'id' => 'required',
                        'newValue' => 'nullable|required_with:tax2|gte:0|between:0,100',
                    ]);
                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    } else if (!$validate->fails()) {
                        $checkTax = DB::table($table_name)->find($request->id);

                        if ($checkTax->tax2 != 'NA') {
                            DB::table($table_name)->where('id', $request->id)->update(['rate2' => $request->newValue]);
                        } else {
                            DB::table($table_name)->where('id', $request->id)->update([
                                'tax2' => 'CESS',
                                'rate2' => $request->newValue
                            ]);
                        }
                    }
                    break;

                default:
                    break;
            }

            $item = DB::table($table_name)->find($request->id);

            // Delete data from cache
            Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

            // Delete data from cache
            Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);

            $response = [
                'status' => 'success',
                'message' => __('validation.New Item Successfully Added'),
                'data' => $item
            ];
            return response()->json($response, 200);

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

    public function getDataByBarcode(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 401,
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $validate = Validator::make(
                $request->all(),
                [
                    'barcode' => [
                        'required',
                        new NoScriptTag,
                        new ValidBarcode($table_name, null, $user->module_type),
                    ],
                ],
                []
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => __('validation.Validation Error'),
                    'errors' => $validate->errors(),
                ], 422);
            }

            $response = InventoryHelpher::getDataByBarcode($request->barcode, $table_name);

            $status = $response['status'] === 200 ? 1 : 0;
            $message = $response['message'];

            $data = ($response['status'] == 200)
                ? [$response['data'] ?? []]
                : [$response['errors'] ?? []];

            return ResponseHelper::responseFn($status, $response['status'], $message, $data);
        } catch (Exception $e) {
            report($e);

            return response()->json([
                'status' => 500,
                'message' => __('inventory.error_adding_item'),
                'errors' => __('validation.unable_to_process'),
            ], 500);
        }
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

    public function validateItemOrSku(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $tableName = TableNameHelper::getTableNameOfLoggedInUser();

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|regex:/[a-zA-Z]/|unique:' . $tableName . ',item_name',
            'sku' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $validator->sometimes('category_id', 'required', function ($input) {
            return !empty($input->sku);
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 403,
                'message' => __('validation.Validation Error'),
                'errors' => $validator->errors(),
            ], 403);
        }

        $response = [
            'item_name' => $request->item_name,
            'item_name_unique' => true,
        ];

        if (!empty($request->sku)) {
            $category = DB::table('categories')->find($request->category_id);

            if (!$category) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Category not found',
                ], 404);
            }


            $generatedSku = InventoryHelpher::generateSkuFromSequence(
                $request->item_name,
                $category->name,
            );

            $skuExists = DB::table($tableName)
                ->where('sku', $request->sku)
                ->exists();

            $response['category_id'] = $request->category_id;
            $response['category_name'] = $category->name;
            $response['entered_sku'] = $request->sku;
            $response['generated_sku'] = $generatedSku;
            $response['entered_sku_exists'] = $skuExists;
            $response['generated_sku_matches_input'] = $request->sku === $generatedSku;
        }

        return response()->json([
            'status' => 200,
            'message' => !empty($request->sku)
                ? 'Item name checked, SKU checked, and SKU generated successfully'
                : 'Item name is unique',
            'data' => $response
        ], 200);
    }

}