<?php

namespace Modules\GroceryGermany\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use Modules\Authentication\Helpers\UserHelper;
use Modules\GroceryGermany\Helpers\ExcelHelpher;
use Modules\GroceryGermany\Helpers\TableNameHelper;
use Modules\GroceryGermany\Helpers\TagsHelper;
use Modules\GroceryGermany\Helpers\ItemHelpher;


class DatasetController extends Controller
{
    public function previewDataSet(Request $request)
    {

        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 0,
                'message' => __('validation.Unauthorized'),
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            $cacheKey = "uploaded_excel_preview_{$user->user_id}";

            // Generate temp table name
            $dataset_table_name = 'dataset_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);

            // Get preferences for validation
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();


            $filePath = app()->environment('production')
                ? 'modules/grocerygermany/dataset/grocery.xlsx'
                : module_path('GroceryGermany', 'Resources/assets/dataset/grocery.xlsx');


            // Check if file exists
            if (!file_exists($filePath)) {
                return response()->json([
                    'status' => 0,
                    'message' => __('dataset_validation.Dataset file not found'),
                ], 404);
            }

            // Parse Excel data
            $data = ExcelHelpher::parseExcel($filePath, 'dataset');


            $country_details = json_decode($user->country_details);

            // Ensure dataset table exists
            UserHelper::generateDatasetTableForAdmin($dataset_table_name, $user->module_type, $country_details->region);

            if (DB::table($dataset_table_name)->count() == 0) {
                $insertData = [];

                // dd($data);

                foreach ($data as $item) {


                    $item_name = ItemHelpher::checkSpecialCharacterFromString($item['item_name']);

                    // Check if item already exists
                    $exists = DB::table($dataset_table_name)->where('item_name', $item_name)->exists();
                    if ($exists) {
                        continue; // Skip inserting if item exists
                    }

                    // Convert numeric fields to float
                    $quantity = isset($item['quantity']) ? (float) trim($item['quantity']) : 0.0;
                    $min_stock_alert = isset($item['min_stock_alert']) ? (float) trim($item['min_stock_alert']) : 0.0;
                    $mrp = isset($item['mrp']) ? (float) trim($item['mrp']) : 0.0;
                    
                    $sale_price = isset($item['sale_price']) ? (float) trim($item['sale_price']) : 0.0;
                   
                    $rate1 = isset($item['vat']) ? (float) trim($item['vat']) : 0.0;

                    $insertData[] = [
                        'item_name' => trim($item_name),
                        'quantity' => $quantity,
                        'min_stock_alert' => $min_stock_alert,
                        'mrp' => $mrp,
                        'sale_price' => $sale_price,
                        'full_unit' => ItemHelpher::getFullUnit(trim(strtoupper($item['unit']))),
                        'short_unit' => trim(strtoupper($item['unit'])),
                        'tax1' => trim('VAT'),
                        'rate1' => $rate1,
                        'tags' => TagsHelper::createOrUpdateTag(trim($item_name)),
                    ];
                }

                DB::connection($user->module_type)->table($dataset_table_name)->insert($insertData);
            }

            // Fetch all data from temp table with explicit column selection
            $allData = DB::connection($user->module_type)->table($dataset_table_name)
                ->select([
                    'id',
                    'item_name',
                    'quantity',
                    'min_stock_alert',
                    'mrp',
                    'sale_price',
                    'short_unit',
                    'rate1',
                ])
                ->get();

            // Collect item names for validation
            $itemNames = DB::connection($user->module_type)->table($dataset_table_name)
                ->pluck('item_name')
                ->map(fn($name) => strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($name ?? 'New Item'))))
                ->toArray();

            // Process data and check for errors
            $data = [];
            $errors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $errorMap = [];

            foreach ($allData as $key => $row) {
                
                $rowArray = [
                    'item_name' => $row->item_name,
                    'quantity' => $row->quantity,
                    'min_stock_alert' => $row->min_stock_alert,
                    'mrp' => $row->mrp,
                    'sale_price' => $row->sale_price,
                    'unit' => $row->short_unit,
                    'vat' => $row->rate1,
                ];

                // Validate each row
                $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, '', $itemNames, $user);

                // Update flag based on validation
                $flag = !empty($validationResult['errors']) ? 1 : 0;

                if (!empty($validationResult['errors'])) {
                    $errorMap[$key] = $validationResult['errors'];
                }

                $data[] = [
                    'original_index' => $key,
                    'item_name' => $row->item_name,
                    'quantity' => $row->quantity,
                    'min_stock_alert' => $row->min_stock_alert,
                    'mrp' => $row->mrp,
                    'sale_price' => $row->sale_price,
                    'unit' => $row->short_unit,
                    'vat' => $row->rate1,
                    'flag' => $flag,
                    'id' => $row->id,
                ];
            }

            // Apply DataTables pagination and filtering
            $draw = $request->input('draw');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value');

            // Get total records before filtering
            $totalRecords = count($data);

            // Apply search filter if provided
            $filteredData = $data;
            if (!empty($searchValue)) {
                $filteredData = array_filter($data, function ($row) use ($searchValue) {
                    return stripos($row['item_name'], $searchValue) !== false ||
                        stripos((string) $row['quantity'], $searchValue) !== false ||
                        stripos((string) $row['min_stock_alert'], $searchValue) !== false ||
                        stripos((string) $row['mrp'], $searchValue) !== false ||
                        stripos((string) $row['sale_price'], $searchValue) !== false ||
                        stripos($row['unit'], $searchValue) !== false ||
                        stripos((string) $row['vat'], $searchValue) !== false ;
                });
                $filteredData = array_values($filteredData);
            }

            // Get filtered count
            $filteredRecords = count($filteredData);

            // Apply sorting
            $columns = [
                'key',
                'item_name',
                'quantity',
                'min_stock_alert',
                'mrp',
                'sale_price',
                'unit',
                'vat'
            ];
            // $orderColumnIndex = $request->input('order.0.column', 1);
            // $orderDirection = $request->input('order.0.dir', 'asc');

            $orderColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 1;
            $orderDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';

            if (isset($columns[$orderColumnIndex])) {
                usort($filteredData, function ($a, $b) use ($columns, $orderColumnIndex, $orderDirection) {
                    $column = $columns[$orderColumnIndex];
                    $valueA = $a[$column];
                    $valueB = $b[$column];
                    if ($orderDirection === 'asc') {
                        return $valueA <=> $valueB;
                    }
                    return $valueB <=> $valueA;
                });
            }
            
            // Always prioritize rows with errors (flag = 1) first
            usort($filteredData, function ($a, $b) {
                return $b['flag'] <=> $a['flag'];
            });

            // Apply pagination
            $paginatedData = array_slice($filteredData, $start, $length);

            // Update error coordinates based on paginated data
            $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];

            foreach ($paginatedData as $newIndex => $row) {
                $originalIndex = $row['original_index'];

                if (isset($errorMap[$originalIndex])) {
                    foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
                        $newErrors['coordinates'][] = "items.$newIndex.$field";
                        $newErrors['grid_coordinates'][] = sprintf("%02d,%02d", $newIndex, array_search($field, array_keys($row), true));

                        if (!in_array($errorMessage, $newErrors['messages'])) {
                            $newErrors['messages'][] = $errorMessage;
                        }
                    }
                }
            }

            $status = empty($errorMap) ? 1 : 0;
            $message = empty($errorMap) ? __('validation.Data fetched successfully') : __('validation.Some rows contain errors');

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => array_values($paginatedData),
                'status' => $status,
                'errors' => $newErrors,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 0,
                'message' => __('validation.An error occurred while fetching data'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addMultipleItems(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $table_name = TableNameHelper::getTableNameOfLoggedInUser();
        $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

        $isErrorExsist = 0;
        $errorMap = [];
        $response = [];

        // Initial validation
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:0,1,2' // 1-append, 2-replace
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 2,
                'message' => __('validation.Validation Error'),
                'errors' => $validator->errors()
            ], 403);
        }

        // Generate temp table name
        $dataset_table_name = 'dataset_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);

        // Fetch all data from temp table
        $tempData = DB::connection($user->module_type)->table($dataset_table_name)->get();

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.No items found in temporary table'),
                'isErrorExsist' => $isErrorExsist,
            ], 404);
        }


        // Collect item names for validation
        $itemNames = DB::connection($user->module_type)->table($dataset_table_name)
            ->pluck('item_name')
            ->map(fn($name) => strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($name ?? 'New Item'))))
            ->toArray();

        // Process items from temp table
        foreach ($tempData as $key => $row) {
            $rowArray = [
                'item_name' => ItemHelpher::checkSpecialCharacterFromString(strtoupper($row->item_name ?? '')),
                'quantity' => $row->quantity ?? '',
                'min_stock_alert' => $row->min_stock_alert ?? '',
                'mrp' => $row->mrp ?? '',
                'sale_price' => $row->sale_price ?? '',
                'unit' => $row->short_unit ?? '',
                'vat' => $row->rate1 ?? '',
            ];

            // Validate each row
            $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, $request->action, $itemNames, $user);

            if (!empty($validationResult['errors'])) {
                $errorMap[$key] = $validationResult['errors'];
                $isErrorExsist = 1;
            }

            $response[] = [
                'original_index' => $key,
                'item_name' => $rowArray['item_name'],
                'quantity' => $rowArray['quantity'],
                'min_stock_alert' => $rowArray['min_stock_alert'],
                'mrp' => $rowArray['mrp'],
                'sale_price' => $rowArray['sale_price'],
                'unit' => $rowArray['unit'],
                'vat' => $rowArray['vat'],
                'flag' => $validationResult['flag'],
            ];
        }

        // Sort response (errors first) for consistency with preview
        usort($response, fn($a, $b) => $b['flag'] <=> $a['flag']);

        // Build detailed error response
        $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
        foreach ($response as $newIndex => $row) {
            $originalIndex = $row['original_index'];
            if (isset($errorMap[$originalIndex])) {
                foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
                    $newErrors['coordinates'][] = "items.$newIndex.$field";
                    $newErrors['grid_coordinates'][] = sprintf("%02d,%02d", $newIndex, array_search($field, array_keys($row), true));
                    if (!in_array($errorMessage, $newErrors['messages'])) {
                        $newErrors['messages'][] = $errorMessage;
                    }
                }
            }
        }

        // Start transaction only for database operations
        if ($isErrorExsist == 0) {
            try {
                DB::beginTransaction();

                if ($request->action == 2) {
                    DB::table($table_name)->delete();
                }

                $insertData = [];

                foreach ($tempData as $row) {
                    $item_name = ItemHelpher::checkSpecialCharacterFromString(strtoupper($row->item_name ?? ''));
                    $insertData[] = [
                        'item_name' => $item_name,
                        'quantity' => !empty($row->quantity) ? $row->quantity : '–',
                        'min_stock_alert' => !empty($row->min_stock_alert) ? $row->min_stock_alert : '–',
                        'mrp' => $row->mrp ?? '–',
                        'sale_price' => $row->sale_price,
                        'full_unit' => ItemHelpher::getFullUnit(strtoupper($row->short_unit ?? '')),
                        'short_unit' => strtoupper($row->short_unit ?? ''),
                        'tax1' => 'VAT',
                        'rate1' => $row->rate1 ?? 0,
                        'tags' => TagsHelper::createOrUpdateTag($item_name),
                    ];
                }

                // dd($insertData);

                DB::table($table_name)->insert($insertData);
                DB::commit();


                Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);
                Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

                return response()->json([
                    'status' => 'success',
                    'message' => count($insertData) . ' ' . __('validation.Items Successfully Added'),
                    'isErrorExsist' => $isErrorExsist,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Transaction failed: ' . $e->getMessage());
                report($e);
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process'),
                    'isErrorExsist' => $isErrorExsist,
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error! No database changes were made'),
                // 'errors' => $newErrors,
                // 'data' => $response,
                'isErrorExsist' => $isErrorExsist,
            ], 403);
        }
    }

    public function updateCellData(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $username = strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
        $table_name = 'dataset_item_' . $username;

        // User preferences
        $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

        // Mapping column indexes to database fields
        $columnMap = [
            1 => 'item_name',
            2 => 'quantity',
            3 => 'min_stock_alert',
            4 => 'mrp',
            5 => 'sale_price',
            6 => 'short_unit',
            7 => 'vat',
        ];

        // Validate that the requested cell_index is valid
        if (($request->id != 0) && empty($request->cell_index) && !array_key_exists($request->cell_index, $columnMap)) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Invalid cell index')
            ], 400);
        }

        if ($request->id == 0) {
            $column_name = $columnMap;
        } else {
            $column_name = $columnMap[$request->cell_index];
        }

        if ($request->id == 0) {
            $rules = [

                'id' => ['required', 'numeric', 'exclude_if:id,0'],
                'row_index' => 'required|numeric',

                'item_name' => 'required|unique:' . $table_name . ',item_name,' . $request->id,
                'quantity' => $preferences->preference_quantity == 1 ? 'required|numeric|gte:0' : 'nullable|numeric|gte:0',
                'min_stock_alert' => 'nullable|numeric|gte:0',
                'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                'sale_price' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'max:100000',
                    function ($attribute, $value, $fail) use ($request, $preferences) {
                        $mrp = $preferences->preference_mrp == 1 ? floatval($request->input('mrp')) : floatval($value);
                        if (floatval($value) > $mrp) {
                            $fail(__('dataset_validation.Sale price cannot be greater than MRP.'));
                        }
                    },
                ],
                'short_unit' => ['required', Rule::in(array_values(config('german_units.units')))],
                'vat' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'value'))],
            ];
        } else {
            $rules = [

                'id' => ['required', 'numeric', 'exclude_if:id,0'],
                'row_index' => 'required|numeric',
                'cell_index' => 'required|numeric',
                $column_name => match ($column_name) {
                    'item_name' => 'required|unique:' . $table_name . ',item_name,' . $request->id,
                    'quantity' => $preferences->preference_quantity == 1 ? 'required|numeric|gte:0' : 'nullable|numeric|gte:0',
                    'min_stock_alert' => 'nullable|numeric|gte:0',
                    'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                    'sale_price' => [
                        'required',
                        'numeric',
                        'gt:0',
                        'max:100000',
                        function ($attribute, $value, $fail) use ($request, $preferences) {
                                $mrp = $preferences->preference_mrp == 1 ? floatval($request->input('mrp')) : floatval($value);
                                if (floatval($value) > $mrp) {
                                    $fail(__('dataset_validation.Sale price cannot be greater than MRP.'));
                                }
                            },
                    ],
                    'short_unit' => ['required', Rule::in(array_values(config('german_units.units')))],
                    'vat' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'value'))],
                    default => '',
                }
            ];
        }

        // Perform validation
        $validate = Validator::make($request->all(), $rules);


        // dd($validate->errors());

        if ($validate->fails()) {


            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
                'row_index' => $request->row_index,
                'cell_index' => $request->cell_index ?? 0,

                'errors' => [
                    'coordinates' => array_keys($validate->errors()->toArray()),
                    // 'grid_coordinates' => array_map(fn($key) => str_pad($request->row_index, 2, '0', STR_PAD_LEFT) . ',' . str_pad($request->cell_index, 2, '0', STR_PAD_LEFT), array_keys($validate->errors()->toArray())),
                    'grid_coordinates' => array_map(fn($key) => str_pad($request->row_index, 2, '0', STR_PAD_LEFT) . ',' . str_pad(array_search($key, $columnMap), 2, '0', STR_PAD_LEFT), array_keys($validate->errors()->toArray())),

                    'messages' => array_values($validate->errors()->all()),
                ]

            ], 403);
        }

        if ($request->id == 0) {


            $updateData = array_merge([
                'item_name' => ItemHelpher::checkSpecialCharacterFromString($request->input('item_name')),
                'tags' => TagsHelper::createOrUpdateTag($request->input('item_name')),
                'quantity' => $request->input('quantity') ?? 0,
                'min_stock_alert' => $request->input('min_stock_alert') ?? 0,
                'mrp' => $request->input('mrp') ?? "-",
                'sale_price' => $request->input('sale_price'),
                'full_unit' => ItemHelpher::getFullUnit(strtoupper($request->input('short_unit'))),
                'short_unit' => $request->input('short_unit'),
                'tax1' => 'VAT',
                'rate1' => $request->input('vat') ?? 0,
            ]);

            // If ID is 0, create a new record
            $insertData = array_merge($updateData, [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $newId = DB::table($table_name)->insertGetId($insertData);

            return response()->json([
                'status' => 'success',
                'message' => 'New Item Successfully Added',
                'data' => ['id' => $newId]
            ], 200);
        } else {

            // Prepare the data for update
            if ($column_name == 'item_name') {
                $updateData = array_merge([
                    'item_name' => ItemHelpher::checkSpecialCharacterFromString($request->input($column_name)),
                    'tags' => TagsHelper::createOrUpdateTag($request->input($column_name)),
                ]);
            } else if ($column_name == 'short_unit') {
                $updateData = array_merge([
                    'full_unit' => ItemHelpher::getFullUnit(strtoupper($request->input($column_name))),
                    'short_unit' => $request->input($column_name),
                ]);
            } else if ($column_name == 'vat') {
                $tax1 = 'tax1';
                $updateData = array_merge([
                    $tax1 => 'VAT',
                    'rate1' => $request->input($column_name),
                ]);
            } 
            else {
                $updateData = array_merge([
                    $column_name => $request->input($column_name),
                ]);
            }


            // If ID exists, update the record
            $updated = DB::table($table_name)->where('id', $request->id)->update($updateData);

            return response()->json([
                'status' => $updated ? 'success' : 'failed',
                'message' => $updated ? __('validation.Item successfully updated') : __('validation.No changes made'),
                'data' => $updateData
            ], 200);
        }
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
        $username = strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
        $table_name = 'dataset_item_' . $username;


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

    public function resetDataset()
    {

        if (Auth::guard('api')->check()) {
            try {
                $user = Auth::guard('api')->user();
                // $file = "storage/dataset/grocery.xlsx";
                // $file = public_path('modules/grocerygemany/dataset/grocery.xlsx');

                $file = app()->environment('production')
                    ? 'modules/grocerygermany/dataset/grocery.xlsx'
                    : module_path('GroceryGermany', 'Resources/assets/dataset/grocery.xlsx');

                // Check if file exists
                if (!file_exists($file)) {
                    return response()->json([
                        'status' => '0',
                        'message' => __('validation.File not found'),
                    ], 404);
                }

                // Call the parseExcel method
                $data = ExcelHelpher::parseExcel($file);

                $username = strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
                $dataset_table_name = 'dataset_item_' . $username;

                DB::connection($user->module_type)->statement("DROP TABLE IF EXISTS {$dataset_table_name}");


                $country_details = json_decode($user->country_details);

                UserHelper::generateDatasetTableForAdmin($dataset_table_name, $user->module_type, $country_details->region);

                $insertData = [];

                foreach ($data as $item) {
                    $item_name = ItemHelpher::checkSpecialCharacterFromString($item['item_name']);

                    // Check if item already exists
                    $exists = DB::table($dataset_table_name)->where('item_name', $item_name)->exists();
                    if ($exists) {
                        continue; // Skip inserting if item exists
                    }

                    $insertData[] = [
                        'item_name' => $item_name,
                        'quantity' => $item['quantity'] ?? 0,
                        'min_stock_alert' => $item['min_stock_alert'] ?? 0,
                        'mrp' => $item['mrp'] ?? '-',
                        'sale_price' => $item['sale_price'],
                        'full_unit' => ItemHelpher::getFullUnit(strtoupper($item['unit'])),
                        'short_unit' => strtoupper($item['unit']),
                        'tax1' => 'VAT',
                        'rate1' => $item['vat'] ?? 0,
                        'tags' => TagsHelper::createOrUpdateTag($item_name),
                    ];
                }


                DB::table($dataset_table_name)->truncate();
                DB::table($dataset_table_name)->insert($insertData);


                $dataset = DB::table($dataset_table_name)->get()->map(function ($item) {
                    $item = (array) $item;
                    $item['unit'] = $item['short_unit'];
                    unset($item['short_unit']);
                    return $item;
                });


                return response()->json([
                    'status' => '1',
                    'message' => __('validation.Dataset Preview'),
                    'data' => $dataset,
                ], 200);

            } catch (\Exception $e) {
                report($e);
                return response()->json([
                    'status' => '0',
                    'message' => __('validation.unable_to_process'),
                ], 403);
            }
        }
    }

}
