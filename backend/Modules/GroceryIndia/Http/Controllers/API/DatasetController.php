<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

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
use Modules\GroceryIndia\Helpers\ExcelHelpher;
use Modules\GroceryIndia\Helpers\TableNameHelper;
use Modules\GroceryIndia\Helpers\TagsHelper;
use Modules\GroceryIndia\Helpers\ItemHelpher;
use Modules\GroceryIndia\Helpers\InventoryHelpher;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


use Modules\GroceryIndia\Rules\HsnCode;
use Modules\GroceryIndia\Rules\ValidBarcode;
use Modules\Core\Rules\NoSpecialCharacter;
use Modules\Core\Rules\NoScriptTag;

class DatasetController extends Controller
{

    // private const COLUMN_MAP = [
    //     1 => 'sku',
    //     2 => 'barcode',
    //     3 => 'item_name',
    //     4 => 'category_id',
    //     5 => 'short_unit',
    //     6 => 'hsn',
    //     7 => 'quantity',
    //     8 => 'min_stock_alert',
    //     9 => 'purchase_price',
    //     10 => 'mrp',
    //     11 => 'sale_price',
    //     12 => 'gst',
    //     13 => 'cess',
    // ];
    
    // public function previewDataSet(Request $request)
    // {
    //     if (!Auth::guard('api')->check()) {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => __('validation.Unauthorized'),
    //         ], 401);
    //     }

    //     try {
    //         $user = Auth::guard('api')->user();
    //         $cacheKey = "uploaded_excel_preview_{$user->user_id}";

    //         $dataset_table_name = 'dataset_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);

    //         $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

    //         $filePath = app()->environment('production')
    //             ? 'modules/groceryindia/dataset/grocery.xlsx'
    //             : module_path('GroceryIndia', 'Resources/assets/dataset/grocery.xlsx');

    //         if (!file_exists($filePath)) {
    //             return response()->json([
    //                 'status' => 0,
    //                 'message' => __('dataset_validation.Dataset file not found'),
    //             ], 404);
    //         }

    //         $data = ExcelHelpher::parseExcel($filePath, 'dataset');

    //         $country_details = json_decode($user->country_details);

    //         UserHelper::generateDatasetTableForAdmin($dataset_table_name, $user->module_type, $country_details->region);

    //         if (DB::table($dataset_table_name)->count() == 0) {
    //             $insertData = [];

    //             foreach ($data as $item) {
    //                 $item_name = ItemHelpher::checkSpecialCharacterFromString($item['item_name']);

    //                 $generatedSku = $preferences->preference_sku
    //                     ? InventoryHelpher::generateSkuFromSequence($item_name)
    //                     : null;


    //                 $exists = DB::table($dataset_table_name)->where('item_name', $item_name)->exists();
    //                 if ($exists) {
    //                     continue;
    //                 }

    //                 $quantity = isset($item['quantity']) ? (float) trim($item['quantity']) : 0.0;
    //                 $min_stock_alert = isset($item['min_stock_alert']) ? (float) trim($item['min_stock_alert']) : 0.0;
    //                 $purchase_price = isset($item['purchase_price']) ? (float) trim($item['purchase_price']) : 0.0;
    //                 $mrp = isset($item['mrp']) ? (float) trim($item['mrp']) : 0.0;
    //                 $sale_price = isset($item['sale_price']) ? (float) trim($item['sale_price']) : 0.0;
    //                 $rate1 = isset($item['gst']) ? (float) trim($item['gst']) : 0.0;
    //                 $rate2 = isset($item['cess']) ? (float) trim($item['cess']) : 0.0;

    //                 $insertData[] = [
    //                     'sku' => $generatedSku,
    //                     'category_id' => $item['category_id'] ?? 1,
    //                     'item_name' => trim($item_name),
    //                     'quantity' => $quantity,
    //                     'min_stock_alert' => $min_stock_alert,
    //                     'purchase_price' => $purchase_price,
    //                     'mrp' => $mrp,
    //                     'sale_price' => $sale_price,
    //                     'full_unit' => ItemHelpher::getFullUnit(trim(strtoupper($item['unit']))),
    //                     'short_unit' => trim(strtoupper($item['unit'])),
    //                     'hsn' => trim(preg_match('/^0+$/', trim($item['hsn']))) ? 0 : trim($item['hsn'] ?? '-'),
    //                     'tax1' => trim('GST'),
    //                     'tax2' => trim('CESS'),
    //                     'rate1' => $rate1,
    //                     'rate2' => $rate2,
    //                     'tags' => TagsHelper::createOrUpdateTag(trim($item_name)),
    //                 ];
    //             }

    //             DB::connection($user->module_type)->table($dataset_table_name)->insert($insertData);
    //         }

    //         $allData = DB::connection($user->module_type)->table($dataset_table_name)
    //             ->select([
    //                 'id',
    //                 'sku',
    //                 'category_id',
    //                 'item_name',
    //                 'quantity',
    //                 'min_stock_alert',
    //                 'purchase_price',
    //                 'mrp',
    //                 'sale_price',
    //                 'short_unit',
    //                 'hsn',
    //                 'rate1',
    //                 'rate2',
    //                 'barcode'
    //             ])
    //             ->get();

    //         $itemNames = DB::connection($user->module_type)->table($dataset_table_name)
    //             ->pluck('item_name')
    //             ->map(fn($name) => strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($name ?? 'New Item'))))
    //             ->toArray();

    //         $data = [];
    //         $errors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
    //         $errorMap = [];

    //         foreach ($allData as $key => $row) {
    //             $rowArray = [
    //                 'sku' => $row->sku,
    //                 'category_id' => $row->category_id,
    //                 'item_name' => $row->item_name,
    //                 'quantity' => $row->quantity,
    //                 'min_stock_alert' => $row->min_stock_alert,
    //                 'purchase_price' => $row->purchase_price,
    //                 'mrp' => $row->mrp,
    //                 'sale_price' => $row->sale_price,
    //                 'unit' => $row->short_unit,
    //                 'hsn' => $row->hsn,
    //                 'gst' => $row->rate1,
    //                 'cess' => $row->rate2,
    //                 'barcode' => $row->barcode,
    //             ];

    //             $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, '', $itemNames, $user);

    //             $flag = !empty($validationResult['errors']) ? 1 : 0;

    //             if (!empty($validationResult['errors'])) {
    //                 $errorMap[$key] = $validationResult['errors'];
    //             }

    //             $data[] = [
    //                 'original_index' => $key,
    //                 'sku' => $row->sku,
    //                 'category_id' => $row->category_id,
    //                 'item_name' => $row->item_name,
    //                 'quantity' => $row->quantity,
    //                 'min_stock_alert' => $row->min_stock_alert,
    //                 'purchase_price' => $row->purchase_price,
    //                 'mrp' => $row->mrp,
    //                 'sale_price' => $row->sale_price,
    //                 'short_unit' => $row->short_unit,
    //                 'hsn' => $row->hsn,
    //                 'gst' => $row->rate1,
    //                 'cess' => $row->rate2,
    //                 'barcode' => $row->barcode,
    //                 'flag' => $flag,
    //                 'id' => $row->id,
    //             ];
    //         }

    //         $draw = isset($request['draw']) ? max(0, intval($request['draw'])) : 0;
    //         $start = isset($request['start']) ? max(0, intval($request['start'])) : 0;
    //         $length = isset($request['length']) ? max(1, intval($request['length'])) : 10;
    //         $sortColumnIndex = isset($request['order'][0]['column']) ? max(0, intval($request['order'][0]['column'])) : 0;
    //         $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
    //         $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';

    //         $totalRecords = count($data);

    //         $filteredData = $data;
    //         if (!empty($searchValue)) {
    //             $filteredData = array_filter($data, function ($row) use ($searchValue) {
    //                 return stripos($row['item_name'], $searchValue) !== false ||
    //                     stripos((string) $row['sku'], $searchValue) !== false ||
    //                     stripos((string) $row['category_id'], $searchValue) !== false ||
    //                     stripos((string) $row['quantity'], $searchValue) !== false ||
    //                     stripos((string) $row['min_stock_alert'], $searchValue) !== false ||
    //                     stripos((string) $row['purchase_price'], $searchValue) !== false ||
    //                     stripos((string) $row['mrp'], $searchValue) !== false ||
    //                     stripos((string) $row['sale_price'], $searchValue) !== false ||
    //                     stripos($row['short_unit'], $searchValue) !== false ||
    //                     stripos($row['hsn'], $searchValue) !== false ||
    //                     stripos((string) $row['gst'], $searchValue) !== false ||
    //                     stripos((string) $row['cess'], $searchValue) !== false;
    //             });
    //             $filteredData = array_values($filteredData);
    //         }

    //         $filteredRecords = count($filteredData);

    //         $columns = [
    //             'key',
    //             'sku',
    //             'barcode',
    //             'item_name',
    //             'category_id',
    //             'short_unit',
    //             'hsn',
    //             'quantity',
    //             'min_stock_alert',
    //             'purchase_price',
    //             'mrp',
    //             'sale_price',
    //             'gst',
    //             'cess'
    //         ];

    //         // ✅ Use sortColumnIndex / sortDirection from new block
    //         if (isset($columns[$sortColumnIndex])) {
    //             usort($filteredData, function ($a, $b) use ($columns, $sortColumnIndex, $sortDirection) {
    //                 $column = $columns[$sortColumnIndex];
    //                 $valueA = $a[$column];
    //                 $valueB = $b[$column];
    //                 return $sortDirection === 'asc' ? $valueA <=> $valueB : $valueB <=> $valueA;
    //             });
    //         }

    //         usort($filteredData, function ($a, $b) {
    //             return $b['flag'] <=> $a['flag'];
    //         });

    //         $paginatedData = array_slice($filteredData, $start, $length);

    //         $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];

    //         foreach ($paginatedData as $newIndex => $row) {
    //             $originalIndex = $row['original_index'];

    //             if (isset($errorMap[$originalIndex])) {
    //                 foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
    //                     $newErrors['coordinates'][] = "items.$newIndex.$field";
    //                     $newErrors['grid_coordinates'][] = sprintf("%02d,%02d", $newIndex, array_search($field, array_keys($row), true));

    //                     if (!in_array($errorMessage, $newErrors['messages'])) {
    //                         $newErrors['messages'][] = $errorMessage;
    //                     }
    //                 }
    //             }
    //         }

    //         $status = empty($errorMap) ? 1 : 0;
    //         $message = empty($errorMap)
    //             ? __('validation.Data fetched successfully')
    //             : __('validation.Some rows contain errors');

    //         return response()->json([
    //             'draw' => intval($draw),
    //             'recordsTotal' => $totalRecords,
    //             'recordsFiltered' => $filteredRecords,
    //             'data' => array_values($paginatedData),
    //             'status' => $status,
    //             'errors' => $newErrors,
    //             'message' => $message,
    //         ]);

    //     } catch (\Exception $e) {
    //         report($e);
    //         return response()->json([
    //             'status' => 0,
    //             'message' => __('validation.An error occurred while fetching data'),
    //             'error' => __('validation.unable_to_process'),
    //         ], 500);
    //     }
    // }


    private const COLUMN_MAP = [
        1 => 'sku',
        2 => 'barcode',
        3 => 'item_name',
        4 => 'category_id',
        5 => 'short_unit',
        6 => 'hsn',
        7 => 'quantity',
        8 => 'min_stock_alert',
        9 => 'purchase_price',
        10 => 'mrp',
        11 => 'sale_price',
        12 => 'rate1',
        13 => 'rate2',
    ];

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

            $dataset_table_name = 'dataset_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);

            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

            $filePath = app()->environment('production')
                ? 'modules/groceryindia/dataset/grocery.xlsx'
                : module_path('GroceryIndia', 'Resources/assets/dataset/grocery.xlsx');

            if (!file_exists($filePath)) {
                return response()->json([
                    'status' => 0,
                    'message' => __('dataset_validation.Dataset file not found'),
                ], 404);
            }

            $parsedExcelData = ExcelHelpher::parseExcel($filePath, 'dataset');

            $country_details = json_decode($user->country_details);

            UserHelper::generateDatasetTableForAdmin($dataset_table_name, $user->module_type, $country_details->region);

            $connection = DB::connection($user->module_type);

            if ($connection->table($dataset_table_name)->count() == 0) {
                $insertData = [];
                $seenItemNames = [];

                foreach ($parsedExcelData as $item) {
                    $item_name = ItemHelpher::checkSpecialCharacterFromString($item['item_name'] ?? '');

                    if (empty(trim($item_name))) {
                        continue;
                    }

                    $normalizedItemName = strtoupper(trim($item_name));

                    if (isset($seenItemNames[$normalizedItemName])) {
                        continue;
                    }

                    $seenItemNames[$normalizedItemName] = true;

                    $generatedSku = $preferences->preference_sku
                        ? InventoryHelpher::generateSkuFromSequence($item_name)
                        : null;

                    $quantity = isset($item['quantity']) ? (float) trim((string) $item['quantity']) : 0.0;
                    $min_stock_alert = isset($item['min_stock_alert']) ? (float) trim((string) $item['min_stock_alert']) : 0.0;
                    $purchase_price = isset($item['purchase_price']) ? (float) trim((string) $item['purchase_price']) : 0.0;
                    $mrp = isset($item['mrp']) ? (float) trim((string) $item['mrp']) : 0.0;
                    $sale_price = isset($item['sale_price']) ? (float) trim((string) $item['sale_price']) : 0.0;
                    $rate1 = isset($item['gst']) ? (float) trim((string) $item['gst']) : 0.0;
                    $rate2 = isset($item['cess']) ? (float) trim((string) $item['cess']) : 0.0;
                    $unit = trim(strtoupper((string) ($item['unit'] ?? '')));
                    $hsnRaw = trim((string) ($item['hsn'] ?? '-'));
                    $hsn = preg_match('/^0+$/', $hsnRaw) ? 0 : $hsnRaw;

                    $insertData[] = [
                        'sku' => $generatedSku,
                        'barcode' => $item['barcode'] ?? null,
                        'category_id' => $item['category_id'] ?? 1,
                        'item_name' => trim($item_name),
                        'quantity' => $quantity,
                        'min_stock_alert' => $min_stock_alert,
                        'purchase_price' => $purchase_price,
                        'mrp' => $mrp,
                        'sale_price' => $sale_price,
                        'full_unit' => ItemHelpher::getFullUnit($unit),
                        'short_unit' => $unit,
                        'hsn' => $hsn,
                        'tax1' => 'GST',
                        'tax2' => 'CESS',
                        'rate1' => $rate1,
                        'rate2' => $rate2,
                        'tags' => TagsHelper::createOrUpdateTag(trim($item_name)),
                    ];
                }

                foreach (array_chunk($insertData, 500) as $chunk) {
                    $connection->table($dataset_table_name)->insert($chunk);
                }
            }

            $draw = max(0, (int) $request->input('draw', 0));
            $start = max(0, (int) $request->input('start', 0));
            $length = max(1, (int) $request->input('length', 10));

            $order = $request->input('order', []);
            $search = $request->input('search', []);

            $sortColumnIndex = 0;
            $sortDirection = 'asc';
            $searchValue = '';

            if (is_array($order) && isset($order[0]) && is_array($order[0])) {
                $sortColumnIndex = max(0, (int) ($order[0]['column'] ?? 0));
                $sortDirection = strtolower((string) ($order[0]['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            }

            if (is_array($search)) {
                $searchValue = trim((string) ($search['value'] ?? ''));
            } elseif (is_string($search)) {
                $searchValue = trim($search);
            }

            $totalRecords = $connection->table($dataset_table_name)->count();

            $baseQuery = $connection->table($dataset_table_name)
                ->select([
                    'id',
                    'sku',
                    'barcode',
                    'category_id',
                    'item_name',
                    'quantity',
                    'min_stock_alert',
                    'purchase_price',
                    'mrp',
                    'sale_price',
                    'short_unit',
                    'hsn',
                    'rate1',
                    'rate2',
                ]);

            if ($searchValue !== '') {
                $like = '%' . $searchValue . '%';

                $baseQuery->where(function ($query) use ($like) {
                    $query->where('item_name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('barcode', 'like', $like)
                        ->orWhere('category_id', 'like', $like)
                        ->orWhere('quantity', 'like', $like)
                        ->orWhere('min_stock_alert', 'like', $like)
                        ->orWhere('purchase_price', 'like', $like)
                        ->orWhere('mrp', 'like', $like)
                        ->orWhere('sale_price', 'like', $like)
                        ->orWhere('short_unit', 'like', $like)
                        ->orWhere('hsn', 'like', $like)
                        ->orWhere('rate1', 'like', $like)
                        ->orWhere('rate2', 'like', $like);
                });
            }

            $filteredRecords = (clone $baseQuery)->count();

            $allowedColumns = [
                0 => 'id',
                1 => 'sku',
                2 => 'barcode',
                3 => 'item_name',
                4 => 'category_id',
                5 => 'short_unit',
                6 => 'hsn',
                7 => 'quantity',
                8 => 'min_stock_alert',
                9 => 'purchase_price',
                10 => 'mrp',
                11 => 'sale_price',
                12 => 'rate1',
                13 => 'rate2',
            ];

            $sortColumn = $allowedColumns[$sortColumnIndex] ?? 'id';

            $allData = (clone $baseQuery)
                ->orderBy($sortColumn, $sortDirection)
                ->offset($start)
                ->limit($length)
                ->get();

            $itemNames = $connection->table($dataset_table_name)
                ->pluck('item_name')
                ->map(fn($name) => strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($name ?? 'New Item'))))
                ->toArray();

            $data = [];
            $errorMap = [];

            foreach ($allData as $key => $row) {
                $rowArray = [
                    'sku' => $row->sku,
                    'category_id' => $row->category_id,
                    'item_name' => $row->item_name,
                    'quantity' => $row->quantity,
                    'min_stock_alert' => $row->min_stock_alert,
                    'purchase_price' => $row->purchase_price,
                    'mrp' => $row->mrp,
                    'sale_price' => $row->sale_price,
                    'unit' => $row->short_unit,
                    'hsn' => $row->hsn,
                    'gst' => $row->rate1,
                    'cess' => $row->rate2,
                    'barcode' => $row->barcode,
                ];

                $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, '', $itemNames, $user);
                $validationErrors = $validationResult['errors'] ?? [];

                if (is_string($validationErrors) && $validationErrors !== '') {
                    $validationErrors = ['item_name' => $validationErrors];
                }

                if (!is_array($validationErrors)) {
                    $validationErrors = [];
                }

                $flag = !empty($validationErrors) ? 1 : 0;

                if (!empty($validationErrors)) {
                    $errorMap[$key] = $validationErrors;
                }

                $data[] = [
                    'original_index' => $start + $key,
                    'sku' => $row->sku,
                    'category_id' => $row->category_id,
                    'item_name' => $row->item_name,
                    'quantity' => $row->quantity,
                    'min_stock_alert' => $row->min_stock_alert,
                    'purchase_price' => $row->purchase_price,
                    'mrp' => $row->mrp,
                    'sale_price' => $row->sale_price,
                    'short_unit' => $row->short_unit,
                    'hsn' => $row->hsn,
                    'gst' => $row->rate1,
                    'cess' => $row->rate2,
                    'barcode' => $row->barcode,
                    'flag' => $flag,
                    'id' => $row->id,
                ];
            }

            usort($data, function ($a, $b) {
                return $b['flag'] <=> $a['flag'];
            });

            $paginatedData = array_values($data);

            $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];

            $fieldColumnIndexMap = [
                'sku' => 1,
                'barcode' => 2,
                'item_name' => 3,
                'category_id' => 4,
                'short_unit' => 5,
                'unit' => 5,
                'hsn' => 6,
                'quantity' => 7,
                'min_stock_alert' => 8,
                'purchase_price' => 9,
                'mrp' => 10,
                'sale_price' => 11,
                'gst' => 12,
                'cess' => 13,
            ];

            foreach ($paginatedData as $newIndex => $row) {
                $originalIndex = $row['original_index'] - $start;

                if (isset($errorMap[$originalIndex]) && is_array($errorMap[$originalIndex])) {
                    foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
                        $newErrors['coordinates'][] = "items.$newIndex.$field";
                        $newErrors['grid_coordinates'][] = sprintf(
                            "%02d,%02d",
                            $newIndex,
                            $fieldColumnIndexMap[$field] ?? 0
                        );

                        if (!in_array($errorMessage, $newErrors['messages'], true)) {
                            $newErrors['messages'][] = $errorMessage;
                        }
                    }
                }
            }

            $status = empty($errorMap) ? 1 : 0;
            $message = empty($errorMap)
                ? __('validation.Data fetched successfully')
                : __('validation.Some rows contain errors');

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $paginatedData,
                'status' => $status,
                'errors' => $newErrors,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 0,
                'message' => __('validation.An error occurred while fetching data'),
                'error' => __('validation.unable_to_process'),
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
        $preferences = DB::table('preferences')
            ->where('user_id', $user->user_id)
            ->first();

        $conn = DB::connection($user->module_type);

        $isErrorExsist = 0;
        $errorMap = [];
        $response = [];

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 2,
                'message' => __('validation.Validation Error'),
                'errors' => $validator->errors()
            ], 403);
        }

        $dataset_table_name = 'dataset_item_' . strtolower(
            str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile
        );

        try {
            $tempData = $conn->table($dataset_table_name)->get();
        } catch (\Throwable $e) {
            \Log::error('Temp dataset fetch failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'connection' => $user->module_type,
                'dataset_table_name' => $dataset_table_name,
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Database operation failed') . ': ' . $e->getMessage(),
                'isErrorExsist' => $isErrorExsist,
            ], 500);
        }

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.No items found in temporary table'),
                'isErrorExsist' => $isErrorExsist,
            ], 404);
        }

        $itemNames = $conn->table($dataset_table_name)
            ->pluck('item_name')
            ->map(fn($name) => strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($name ?? 'New Item'))))
            ->toArray();

        foreach ($tempData as $key => $row) {
            $rowArray = [
                'sku' => $row->sku ?? '',
                'category_id' => $row->category_id ?? '',
                'item_name' => ItemHelpher::checkSpecialCharacterFromString(strtoupper($row->item_name ?? '')),
                'quantity' => $row->quantity ?? '',
                'min_stock_alert' => $row->min_stock_alert ?? '',
                'mrp' => $row->mrp ?? '',
                'sale_price' => $row->sale_price ?? '',
                'purchase_price' => $row->purchase_price ?? '',
                'unit' => $row->short_unit ?? '',
                'hsn' => $row->hsn ?? '',
                'gst' => $row->rate1 ?? '',
                'cess' => $row->rate2 ?? '',
            ];

            $validationResult = ExcelHelpher::validateRow(
                $rowArray,
                $preferences,
                $request->action,
                $itemNames,
                $user
            );

            if (!empty($validationResult['errors'])) {
                $errorMap[$key] = $validationResult['errors'];
                $isErrorExsist = 1;
            }

            $response[] = [
                'original_index' => $key,
                'sku' => $rowArray['sku'],
                'category_id' => $rowArray['category_id'],
                'item_name' => $rowArray['item_name'],
                'quantity' => $rowArray['quantity'],
                'min_stock_alert' => $rowArray['min_stock_alert'],
                'purchase_price' => $rowArray['purchase_price'],
                'mrp' => $rowArray['mrp'],
                'sale_price' => $rowArray['sale_price'],
                'unit' => $rowArray['unit'],
                'hsn' => $rowArray['hsn'],
                'gst' => $rowArray['gst'],
                'cess' => $rowArray['cess'],
                'flag' => $validationResult['flag'],
            ];
        }

        usort($response, fn($a, $b) => $b['flag'] <=> $a['flag']);

        $newErrors = [
            'coordinates' => [],
            'grid_coordinates' => [],
            'messages' => []
        ];

        foreach ($response as $newIndex => $row) {
            $originalIndex = $row['original_index'];

            if (isset($errorMap[$originalIndex])) {
                foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
                    $newErrors['coordinates'][] = "items.$newIndex.$field";
                    $newErrors['grid_coordinates'][] = sprintf(
                        "%02d,%02d",
                        $newIndex,
                        array_search($field, array_keys($row), true)
                    );

                    if (!in_array($errorMessage, $newErrors['messages'])) {
                        $newErrors['messages'][] = $errorMessage;
                    }
                }
            }
        }

        if ($isErrorExsist == 1) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error! No database changes were made'),
                // 'errors' => $newErrors,
                // 'data' => $response,
                'isErrorExsist' => $isErrorExsist,
            ], 403);
        }

        try {
            $result = $conn->transaction(function () use ($conn, $request, $table_name, $tempData, $user) {
                if ((int) $request->action === 2) {
                    $conn->table($table_name)->delete();
                }

                $insertData = [];

                // $lastSequence = (int) $conn->table($table_name)->max('sku_sequence');
                // $lastSequence = $lastSequence > 0 ? $lastSequence : 0;

                foreach ($tempData as $row) {
                    $item_name = ItemHelpher::checkSpecialCharacterFromString(
                        strtoupper($row->item_name ?? '')
                    );

                    // $shortUnit = strtoupper($row->short_unit ?? '');
                    // $lastSequence++;

                    // $sku = InventoryHelpher::generateSkuFromSequence(
                    //     $item_name,
                    //     $shortUnit,
                    //     $lastSequence
                    // );

                    $insertData[] = [
                        // 'sku_sequence' => $lastSequence,
                        'sku' => !empty($row->sku) ? $row->sku : NULL,
                        'category_id' => !empty($row->category_id) ? $row->category_id : NULL,

                        'item_name' => $item_name,
                        'quantity' => !empty($row->quantity) ? $row->quantity : '–',
                        'min_stock_alert' => !empty($row->min_stock_alert) ? $row->min_stock_alert : '–',
                        'purchase_price' => $row->purchase_price ?? '–',
                        'mrp' => $row->mrp ?? '–',
                        'sale_price' => $row->sale_price ?? 0,
                        'full_unit' => ItemHelpher::getFullUnit(strtoupper($row->short_unit ?? '')),
                        'short_unit' => strtoupper($row->short_unit ?? ''),
                        'hsn' => !empty($row->hsn) ? $row->hsn : '–',
                        'tax1' => 'GST',
                        'rate1' => $row->rate1 ?? 0,
                        'tax2' => 'CESS',
                        'rate2' => $row->rate2 ?? 0,
                        'tags' => TagsHelper::createOrUpdateTag($item_name),
                    ];
                }

                if (!empty($insertData)) {
                    $conn->table($table_name)->insert($insertData);
                }

                // $items = $conn->table($table_name)
                //     ->orderBy('id', 'desc')
                //     ->take(count($insertData))
                //     ->get()
                //     ->reverse()
                //     ->values();

                // foreach ($items as $item) {
                //     $sku = InventoryHelpher::generateSku(
                //         $item->item_name,
                //         $item->short_unit,
                //         $table_name,
                //         $user->module_type
                //     );

                //     $conn->table($table_name)
                //         ->where('id', $item->id)
                //         ->update([
                //             'sku' => $sku
                //         ]);
                // }

                return count($insertData);
            });

            Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);
            Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

            return response()->json([
                'status' => 'success',
                'message' => $result . ' ' . __('validation.Items Successfully Added'),
                'isErrorExsist' => $isErrorExsist,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('addMultipleItems failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'connection' => $user->module_type,
                'table_name' => $table_name,
                'dataset_table_name' => $dataset_table_name,
                'action' => $request->action,
                'user_id' => $user->user_id ?? null,
                'mobile' => $user->mobile ?? null,
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Database operation failed') . ': ' . $e->getMessage(),
                'isErrorExsist' => $isErrorExsist,
            ], 500);
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
        // $columnMap = [
        //     1 => 'sku',
        //     2 => 'category_id',
        //     3 => 'item_name',
        //     4 => 'quantity',
        //     5 => 'min_stock_alert',
        //     6 => 'purchase_price',
        //     7 => 'mrp',
        //     8 => 'sale_price',
        //     9 => 'short_unit',
        //     10 => 'hsn',
        //     11 => 'gst',
        //     12 => 'cess',
        // ];
        $columnMap = self::COLUMN_MAP;

        // Validate that the requested cell_index is valid
        if (($request->id != 0) && empty($request->cell_index) && !array_key_exists($request->cell_index, $columnMap)) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Invalid cell index')
            ], 400);
        }

        // if ($request->id == 0) {
        //     $column_name = $columnMap;
        // } else {
        //     $column_name = $columnMap[$request->cell_index];
        // }

        $column_name = $request->id == 0 ? $columnMap : $columnMap[$request->cell_index];


        if ($request->id == 0) {
            $rules = [

                'id' => ['required', 'numeric', 'exclude_if:id,0'],
                'row_index' => 'required|numeric',

                'sku' => $preferences->preference_sku == 1 ? 'required|max:50|string|unique:' . $table_name . ',item_name,' . $request->id : 'nullable|max:50|string|unique:' . $table_name . ',item_name,' . $request->id,
                'category_id' => $preferences->preference_category == 1 ? 'requrired|integer|exists:categories,id' : 'nullable|integer|exists:categories,id',

                'item_name' => 'required|unique:' . $table_name . ',item_name,' . $request->id,
                'quantity' => $preferences->preference_quantity == 1 ? 'required|integer|gte:0|max:100000' : 'nullable|integer|gte:0|max:100000',
                'min_stock_alert' => 'nullable|integer|gte:0|max:100000',
                'purchase_price' => $preferences->preference_purchase_price == 1 ? 'required|numeric|gte:0|max:100000' : 'nullable|numeric|gte:0|max:100000',
                'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gte:0|max:100000' : 'nullable|numeric|gte:0|max:100000',
                'sale_price' => [
                    'required',
                    'numeric',
                    'gte:0',
                    'max:100000',
                    function ($attribute, $value, $fail) use ($request, $preferences) {
                        $mrp = $preferences->preference_mrp == 1 ? floatval($request->input('mrp')) : floatval($value);
                        if (floatval($value) > $mrp) {
                            $fail(__('dataset_validation.Sale price cannot be greater than MRP.'));
                        }
                    },
                ],
                'short_unit' => ['required', Rule::in(array_values(config('india_units.units')))],
                'gst' => 'required|numeric|gte:0|between:0,100',
                'cess' => 'nullable|numeric|gte:0|between:0,100',
                'hsn' => ['nullable', new HsnCode($preferences->preference_hsn == 1)],
                'barcode' => [
                    'nullable',
                    new NoScriptTag,
                    new ValidBarcode($table_name, null, $user->module_type),
                ],
            ];
        } else {
            $rules = [

                'id' => ['required', 'numeric', 'exclude_if:id,0'],
                'row_index' => 'required|numeric',
                'cell_index' => 'required|numeric',
                $column_name => match ($column_name) {

                    'sku' => $preferences->preference_sku == 1 ? 'required|max:50|string|unique:' . $table_name . ',item_name,' . $request->id : 'nullable|max:50|string|unique:' . $table_name . ',item_name,' . $request->id,
                    'category_id' => $preferences->preference_category == 1 ? 'requrired|integer|exists:categories,id' : 'nullable|integer|exists:categories,id',

                    'item_name' => 'required|unique:' . $table_name . ',item_name,' . $request->id,
                    'quantity' => $preferences->preference_quantity == 1 ? 'required|integer|gte:0|max:100000' : 'nullable|integer|gte:0|max:100000',
                    'min_stock_alert' => 'nullable|integer|gte:0|max:100000',
                    'purchase_price' => $preferences->preference_purchase_price == 1 ? 'required|numeric|gte:0|max:100000' : 'nullable|numeric|gte:0|max:100000',
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
                    'short_unit' => ['required', Rule::in(array_values(config('india_units.units')))],
                    'gst' => 'required|numeric|gte:0|between:0,100',
                    'cess' => 'nullable|numeric|gte:0|between:0,100',
                    'hsn' => ['nullable', new HsnCode($preferences->preference_hsn == 1)],
                    'barcode' => [
                        'nullable',
                        new NoScriptTag,
                        new ValidBarcode($table_name, null, $user->module_type),
                    ],
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
                'sku' => $request->input('sku') ?? NULL,
                'category_id' => $request->input('category_id') ?? NULL,

                'item_name' => ItemHelpher::checkSpecialCharacterFromString($request->input('item_name')),
                'tags' => TagsHelper::createOrUpdateTag($request->input('item_name')),
                'quantity' => $request->input('quantity') ?? 0,
                'min_stock_alert' => $request->input('min_stock_alert') ?? 0,
                'purchase_price' => $request->input('purchase_price') ?? "-",
                'mrp' => $request->input('mrp') ?? "-",
                'sale_price' => $request->input('sale_price'),
                'full_unit' => ItemHelpher::getFullUnit(strtoupper($request->input('short_unit'))),
                'short_unit' => $request->input('short_unit'),
                'hsn' => $request->input('hsn') ?? '-',
                'tax1' => 'GST',
                'rate1' => $request->input('gst') ?? 0,
                'tax2' => 'CESS',
                'rate2' => $request->input('cess') ?? 0,
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
            if ($column_name == 'sku') {
                $updateData = array_merge([
                    'sku' => !empty($request->sku) ? $request->sku : NULL
                ]);
            }
            else if ($column_name == 'category_id') {
                $updateData = array_merge([
                    'category_id' => !empty($request->category_id) ? $request->category_id : NULL
                ]);
            }
            else if ($column_name == 'item_name') {
                $updateData = array_merge([
                    'item_name' => ItemHelpher::checkSpecialCharacterFromString($request->input($column_name)),
                    'tags' => TagsHelper::createOrUpdateTag($request->input($column_name)),
                ]);
            } else if ($column_name == 'quantity') {
                $updateData = array_merge([
                    // 'quantity' => $request->input('quantity', 0),
                    'quantity' => !empty($request->quantity) ? $request->quantity : 0
                ]);
            } else if ($column_name == 'min_stock_alert') {
                $updateData = array_merge([
                    'min_stock_alert' => !empty($request->min_stock_alert) ? $request->min_stock_alert : 0
                ]);
            } else if ($column_name == 'short_unit') {
                $updateData = array_merge([
                    'full_unit' => ItemHelpher::getFullUnit(strtoupper($request->input($column_name))),
                    'short_unit' => $request->input($column_name),
                ]);
            } else if ($column_name == 'gst') {
                $tax1 = 'tax1';
                $updateData = array_merge([
                    $tax1 => 'GST',
                    'rate1' => $request->input($column_name),
                ]);
            } else if ($column_name == 'cess') {
                $tax2 = 'tax2';
                $updateData = array_merge([
                    $tax2 => 'CESS',
                    'rate2' => $request->input($column_name),
                ]);
            } else {
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

                $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();


                // $file = "storage/dataset/grocery.xlsx";
                // $file = public_path('modules/groceryindia/dataset/grocery.xlsx');

                $file = app()->environment('production')
                    ? 'modules/groceryindia/dataset/grocery.xlsx'
                    : module_path('GroceryIndia', 'Resources/assets/dataset/grocery.xlsx');


                // Check if file exists
                if (!file_exists($file)) {
                    return response()->json([
                        'status' => '0',
                        'message' => __('validation.File not found'),
                    ], 404);
                }

                // Call the parseExcel method
                $data = ExcelHelpher::parseExcel($file,'dataset');

                $username = strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
                $dataset_table_name = 'dataset_item_' . $username;

                DB::connection($user->module_type)->statement("DROP TABLE IF EXISTS {$dataset_table_name}");

                $country_details = json_decode($user->country_details);

                UserHelper::generateDatasetTableForAdmin($dataset_table_name, $user->module_type, $country_details->region);

                $insertData = [];

                foreach ($data as $item) {
                    $item_name = ItemHelpher::checkSpecialCharacterFromString($item['item_name']);
                    
                    $generatedSku = $preferences->preference_sku
                        ? InventoryHelpher::generateSkuFromSequence($item_name)
                        : null;

                    // Check if item already exists
                    $exists = DB::table($dataset_table_name)->where('item_name', $item_name)->exists();
                    if ($exists) {
                        continue; // Skip inserting if item exists
                    }

                    $insertData[] = [
                        'sku' => $generatedSku,
                        'category_id' => 1,
                        'item_name' => $item_name,
                        'quantity' => $item['quantity'] ?? 0,
                        'min_stock_alert' => $item['min_stock_alert'] ?? 0,
                        'purchase_price' => $item['purchase_price'] ?? '0',
                        'mrp' => $item['mrp'] ?? '0',
                        'sale_price' => $item['sale_price'],
                        'full_unit' => ItemHelpher::getFullUnit(strtoupper($item['unit'])),
                        'short_unit' => strtoupper($item['unit']),
                        'hsn' => (preg_match('/^0+$/', $item['hsn'])) ? 0 : $item['hsn'] ?? '-',
                        'tax1' => 'GST',
                        'rate1' => $item['gst'] ?? 0,
                        'tax2' => 'CESS',
                        'rate2' => $item['cess'] ?? 0,
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
                    'message' => __('validation.datase_successfully_reset'),
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

    public function downloadDataset(){
        if (!Auth::guard('api')->check()) {
            return response()->json(['status' => 'failed', 'message' => __('validation.Unauthorized')], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getDatasetTableNameOfLoggedInUser();

            // check user preference setting
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

            $data = DB::table($table_name)->get()->toArray();

            if (count($data) <= 0) {
                throw new \Exception('No Product Found');
            }

            // // Explicit column map: field => [label, column letter, merge_end (optional)]
            // $columnMap = [
            //     'item_name' => ['label' => 'ITEM NAME', 'col' => 'A', 'merge_end' => 'D'],
            //     'quantity' => ['label' => 'QUANTITY', 'col' => 'E'],
            //     'min_stock_alert' => ['label' => 'MINIMUM STOCK ALERT', 'col' => 'F', 'merge_end' => 'G'],
            //     'purchase_price' => ['label' => 'Purchase Price', 'col' => 'H'],
            //     'mrp' => ['label' => 'MRP', 'col' => 'I'],
            //     'sale_price' => ['label' => 'SALE PRICE', 'col' => 'J'],
            //     'short_unit' => ['label' => 'UNIT', 'col' => 'K'],
            //     'hsn' => ['label' => 'HSN', 'col' => 'L'],
            //     'barcode' => ['label' => 'BARCODE', 'col' => 'M'],
            //     'rate1' => ['label' => 'GST', 'col' => 'N'],
            //     'rate2' => ['label' => 'CESS', 'col' => 'O'],
            // ];


            // Explicit column map
            $columnMap = [
                'item_name' => ['label' => 'ITEM NAME', 'col' => 'A', 'merge_end' => 'D'],
                'sku' => ['label' => 'SKU', 'col' => 'E', 'merge_end' => 'F'],
                'category_id' => ['label' => 'CATEGORY', 'col' => 'G', 'merge_end' => 'H'],
                'quantity' => ['label' => 'QUANTITY', 'col' => 'I'],
                'min_stock_alert' => ['label' => 'MINIMUM STOCK ALERT', 'col' => 'J', 'merge_end' => 'K'],
            ];

            // // Add Purchase Price only if enabled in preferences
            // if ($preferences && $preferences->preference_purchase_price == 1) {
            //     $columnMap['purchase_price'] = ['label' => 'PURCHASE PRICE', 'col' => 'H'];
            // }

            $columnMap += [
                'mrp' => ['label' => 'MRP', 'col' => 'L'],
                'sale_price' => ['label' => 'SALE PRICE', 'col' => 'M'],
                'purchase_price' => ['label' => 'PURCHASE PRICE', 'col' => 'N'],
                'short_unit' => ['label' => 'UNIT', 'col' => 'O'],
                'hsn' => ['label' => 'HSN', 'col' => 'P'],
                'barcode' => ['label' => 'BARCODE', 'col' => 'Q'],
                'rate1' => ['label' => 'GST', 'col' => 'R'],
                'rate2' => ['label' => 'CESS', 'col' => 'S'],
            ];

            // Fields to skip entirely
            $skipFields = ['id', 'full_unit', 'tax1', 'tax2', 'tags', 'created_at', 'updated_at'];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // --- Write headers ---
            foreach ($columnMap as $field => $config) {
                $col = $config['col'];
                $cell = $col . '1';

                if (isset($config['merge_end'])) {
                    $sheet->mergeCells($col . '1:' . $config['merge_end'] . '1');
                }

                $sheet->setCellValue($cell, $config['label']);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setWidth(18);
            }

            // --- Write data rows ---
            $startRow = 2;
            foreach ($data as $row) {
                $row = (array) $row;

                foreach ($columnMap as $field => $config) {
                    $col = $config['col'];
                    $cell = $col . $startRow;
                    $value = $row[$field] ?? '';

                    // Normalize empty/dash values
                    $cleanValue = ($value !== '' && $value !== '–') ? $value : '';

                    if (isset($config['merge_end'])) {
                        $sheet->mergeCells($col . $startRow . ':' . $config['merge_end'] . $startRow);
                    }

                    $sheet->setCellValue($cell, $cleanValue);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }

                $startRow++;
            }

            // Save the spreadsheet to a file in 'media' folder
            $writer = new Xlsx($spreadsheet);
            $filename = 'dataset.xlsx';
            $directory = 'media';
            $filePath = $directory . '/' . $filename;

            // Check if the directory exists; if not, create it
            if (!is_dir('storage/' . $directory)) {
                mkdir('storage/' . $directory, 0777, true); // Create directory with appropriate permissions
            }

            // Save the file
            $writer->save('storage/' . $filePath);


            return response()->json(['status' => '1', 'message' => 'Export successful', 'file' => $filePath]);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => '0', 'message' => __('validation.unable_to_process')], 200);
        }

    }

}
