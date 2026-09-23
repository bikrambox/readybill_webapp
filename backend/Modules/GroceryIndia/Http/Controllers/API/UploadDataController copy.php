<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;

use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Schema;

use Modules\GroceryIndia\Jobs\ProcessExcelUploadJob;
use Modules\GroceryIndia\Jobs\ProcessExportToInventoryJob;
use Modules\GroceryIndia\Jobs\FetchUploadedExcelDataJob;

use Illuminate\Support\Facades\Log;

use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\Shop;

use Modules\GroceryIndia\Helpers\TagsHelper;
use Modules\GroceryIndia\Helpers\ItemHelpher;
use Modules\GroceryIndia\Helpers\ExcelHelpher;

use Modules\GroceryIndia\Rules\HsnCode;
use Modules\GroceryIndia\Rules\ValidBarcode;
use Modules\Core\Rules\NoSpecialCharacter;
use Modules\Core\Rules\NoScriptTag;


class UploadDataController extends Controller
{

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

    public function getActiveJob(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            // Query the jobs table for active jobs (not reserved too long ago)
            $jobs = DB::connection('central')->table('jobs')
                ->where('queue', 'default') // Adjust queue names as per your setup
                // ->whereIn('payload', [
                //     '%ProcessExcelUploadJob%',
                //     '%ProcessExportToInventoryJob%'
                // ])
                ->where(function ($query) {
                    $query->whereNull('reserved_at')
                        ->orWhere('reserved_at', '>=', now()->subMinutes(30)->timestamp);
                })
                ->get();

            foreach ($jobs as $job) {
                // Decode the payload
                $payload = json_decode($job->payload, true);
                if (!isset($payload['data']['command'])) {
                    continue;
                }

                // Unserialize the command to extract userId
                try {
                    $command = unserialize($payload['data']['command']);
                    $userId = $command->userId ?? null;

                    // dd($userId);

                    if ($userId === $user->user_id) {
                        $jobId = null;
                        $jobType = null;

                        if ($payload['displayName'] === 'App\\Jobs\\ProcessExcelUploadJob') {
                            $jobId = $command->jobId;
                            $jobType = 'upload';
                            $cacheKey = "excel_upload_progress_{$user->user_id}_{$jobId}";
                        } elseif ($payload['displayName'] === 'App\\Jobs\\ProcessExportToInventoryJob') {
                            $jobId = $command->jobId;
                            $jobType = 'export';
                            $cacheKey = "export_inventory_progress_{$user->user_id}_{$jobId}";
                        }

                        if ($jobId && $jobType) {
                            $progress = Cache::get($cacheKey, [
                                'percentage' => 0,
                                'message' => 'Waiting to start...',
                                'success' => null,
                            ]);

                            return response()->json([
                                'status' => 1,
                                'job_type' => $jobType,
                                'job_id' => $jobId,
                                'uuid' => $payload['uuid'],
                                'progress' => $progress,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    report($e);
                    \Log::warning('Failed to unserialize job payload: ' . $e->getMessage());
                    continue;
                }
            }

            // No active jobs found
            return response()->json([
                'status' => 0,
                'message' => __('validation.No active jobs found'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking active jobs: ' . $e->getMessage());
            report($e);
            return response()->json([
                'status' => 0,
                'message' => __('validation.An error occurred while checking active jobs'),
                'error' => __('validation.unable_to_process'),
            ], 500);
        }
    }

    // public function handleExcelUpload(Request $request)
    // {
    //     try {
    //         $user = Auth::guard('api')->user();
    //         $jobId = $request->input('job_id');

    //         // If job_id is provided, check progress
    //         if ($jobId) {
    //             $progress = Cache::get("excel_upload_progress_{$user->user_id}_{$jobId}", [
    //                 'percentage' => 0,
    //                 'message' => __('validation.Waiting to start') . '...',
    //                 'success' => null,
    //             ]);

    //             if ($progress['percentage'] === 100 && $progress['success'] !== null) {
    //                 if ($progress['success']) {
    //                     $result = Cache::get("excel_upload_result_{$user->user_id}_{$jobId}");
    //                     return response()->json([
    //                         'status' => 2,
    //                         'progress' => $progress,
    //                         'job_id' => $jobId,
    //                     ]);
    //                 } else {
    //                     return response()->json([
    //                         'status' => 0,
    //                         'progress' => $progress,
    //                     ], 422);
    //                 }
    //             }

    //             return response()->json([
    //                 'status' => 1,
    //                 'progress' => $progress,
    //             ]);
    //         }

    //         // New upload flow
    //         ini_set('memory_limit', '1024000M');
    //         set_time_limit(-1);
    //         ini_set('max_execution_time', -1);
    //         ini_set('max_input_time', -1);

    //         $validator = Validator::make($request->all(), [
    //             'file' => 'required|mimes:xlsx,csv|max:10240',
    //         ],[

    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => 2,
    //                 'message' => __('validation.Validation Error'),
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $file = $request->file('file');
    //         $newJobId = uniqid('excel_');

    //         // ✅ Delete any previous temp Excel files for this user before storing new one
    //         $previousJobId = $request->input('previous_job_id'); // optional: pass from frontend
    //         ExcelHelpher::deletePreviousUpload($user->user_id, $previousJobId ?: null);

    //         $filePath = $file->storeAs('temp', "excel_{$user->user_id}_{$newJobId}.{$file->extension()}");

    //         ProcessExcelUploadJob::dispatch(storage_path('app/' . $filePath), $user->user_id, $newJobId);

    //         return response()->json([
    //             'status' => 1,
    //             'message' => __('validation.Excel processing started'),
    //             'job_id' => $newJobId,
    //         ], 202);

    //     } catch (\Exception $e) {
    //         report($e);
    //         return response()->json([
    //             'status' => 0,
    //             'message' => __('validation.An error occurred while processing the request'),
    //             'error' => __('validation.unable_to_process'),
    //         ], 500);
    //     }
    // }

    public function handleExcelUpload(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $jobId = $request->input('job_id');

            if ($jobId) {
                $progress = Cache::get("excel_upload_progress_{$user->user_id}_{$jobId}", [
                    'percentage' => 0,
                    'message' => __('validation.Waiting to start') . '...',
                    'success' => null,
                ]);

                if ($progress['percentage'] === 100 && $progress['success'] !== null) {
                    if ($progress['success']) {
                        return response()->json([
                            'status' => 2,
                            'progress' => $progress,
                            'job_id' => $jobId,
                        ]);
                    }

                    return response()->json([
                        'status' => 0,
                        'progress' => $progress,
                    ], 422);
                }

                return response()->json([
                    'status' => 1,
                    'progress' => $progress,
                ]);
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,csv|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => __('validation.Validation Error'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $file = $request->file('file');
            $path = $file->getRealPath();

            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $highestColumn = $sheet->getHighestColumn();
            $headers = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, false)[0] ?? [];

            $headers = array_values(array_filter(array_map(function ($header) {
                return strtoupper(trim((string) $header));
            }, $headers), fn($value) => $value !== ''));

            // $headerCheck = ExcelHelpher::checkHeader('upload-data', $headers);

            // if ($headerCheck) {
            //     return response()->json([
            //         'status' => 0,
            //         'message' => $headerCheck['message'],
            //         'errors' => [
            //             'expected_headers' => $headerCheck['expected_headers'],
            //             'received_headers' => $headerCheck['received_headers'],
            //         ],
            //     ], $headerCheck['code'] ?? 422);
            // }

            $newJobId = uniqid('excel_');
            $previousJobId = $request->input('previous_job_id');
            ExcelHelpher::deletePreviousUpload($user->user_id, $previousJobId ?: null);

            $filePath = $file->storeAs('temp', "excel_{$user->user_id}_{$newJobId}.{$file->extension()}");

            ProcessExcelUploadJob::dispatch(
                storage_path('app/' . $filePath),
                $user->user_id,
                $newJobId
            );

            return response()->json([
                'status' => 1,
                'message' => __('validation.Excel processing started'),
                'job_id' => $newJobId,
            ], 202);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => 0,
                'message' => __('validation.An error occurred while processing the request'),
                'error' => __('validation.unable_to_process'),
            ], 500);
        }
    }

    public function fetchUploadedExcelData(Request $request)
    {
        try {
            $user = User::find(Auth::guard('api')->user()->user_id);
            $jobId = $request->input('job_id');
            $draw = $request->input('draw', 0);
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value');
            $order = $request->input('order.0', []);
            $orderColumn = $order['column'] ?? null;
            $orderDirection = $order['dir'] ?? 'asc';

            if (!$jobId) {
                return response()->json([
                    'draw' => intval($draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'status' => 0,
                    'message' => 'Job ID is required.',
                    'progress' => ['percentage' => 0, 'message' => 'Job ID is required.', 'success' => false],
                ], 400);
            }

            $cacheKey = "excel_fetch_result_{$user->user_id}_{$jobId}";
            $progressKey = "excel_fetch_progress_{$user->user_id}_{$jobId}";

            // ── Unique lock per query params (page + search + sort) ──────────────
            $searchHash = md5(($searchValue ?? '') . ($orderColumn ?? '') . $orderDirection);
            $dispatchKey = "excel_fetch_dispatched_{$user->user_id}_{$jobId}_{$start}_{$length}_{$searchHash}";

            $progress = Cache::get($progressKey, [
                'percentage' => 0,
                'message' => __('validation.Waiting to start') . '...',
                'success' => null,
            ]);

            // ✅ Job is done — return cached result immediately, never re-dispatch
            if ($progress['percentage'] === 100 && $progress['success'] !== null) {
                $result = Cache::get($cacheKey, [
                    'draw' => intval($draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'status' => 0,
                    'message' => __('validation.No data available'),
                    'progress' => $progress,
                ]);
                $result['draw'] = intval($draw);
                $result['progress'] = $progress;
                return response()->json($result);
            }

            // ✅ Only dispatch once per unique query — not on every poll
            if (!Cache::has($dispatchKey)) {
                Cache::put($dispatchKey, true, now()->addMinutes(5));

                // Track this dispatch key so patchCache/insert can clear it later
                $dispatchIndex = "excel_fetch_dispatch_keys_{$user->user_id}_{$jobId}";
                $existingKeys = Cache::get($dispatchIndex, []);
                $existingKeys[] = $dispatchKey;
                Cache::put($dispatchIndex, array_unique($existingKeys), now()->addMinutes(30));

                FetchUploadedExcelDataJob::dispatch(
                    $user->user_id,
                    $jobId,
                    $draw,
                    $start,
                    $length,
                    $searchValue,
                    $orderColumn,
                    $orderDirection
                );
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'status' => 1,
                'message' => __('validation.Job dispatched, processing data') . '...',
                'progress' => $progress,
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'status' => 0,
                'message' => __('validation.unable_to_process'),
                'progress' => ['percentage' => 100, 'message' => __('validation.unable_to_process'), 'success' => false],
            ], 500);
        }
    }


    public function updateCellData(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized'),
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $table_name = ExcelHelpher::createTempTableName($user->user_id);
        $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

        // $columnMap = [
        //     1 => 'item_name',
        //     2 => 'quantity',
        //     3 => 'min_stock_alert',
        //     4 => 'purchase_price',
        //     5 => 'mrp',
        //     6 => 'sale_price',
        //     7 => 'short_unit',
        //     8 => 'hsn',
        //     9 => 'barcode',
        //     10 => 'gst',
        //     11 => 'cess',
        // ];

        $columnMap = self::COLUMN_MAP;

        if (($request->id != 0) && empty($request->cell_index) && !array_key_exists($request->cell_index, $columnMap)) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Invalid cell index'),
            ], 400);
        }

        $column_name = $request->id == 0 ? $columnMap : $columnMap[$request->cell_index];

        $rules = $request->id == 0 ? [
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
                        $fail('Sale price cannot be greater than MRP.');
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
        ] : [
            'id' => ['required', 'numeric', 'exclude_if:id,0'],
            'row_index' => 'required|numeric',
            'cell_index' => 'required|numeric',
            $column_name => match ($column_name) {
                'sku' => $preferences->preference_sku == 1 ? 'required|max:50|string|unique:' . $table_name . ',item_name,' . $request->id : 'nullable|max:50|string|unique:' . $table_name . ',item_name,' . $request->id,
                'category_id' => $preferences->preference_category == 1 ? 'requrired|integer|exists:categories,id' : 'nullable|integer|exists:categories,id',

                'item_name' => 'required|unique:' . $table_name . ',item_name,' . $request->id,
                'quantity' => $preferences->preference_quantity == 1 ? 'required|integer|gte:0|max:100000' : 'nullable|integer|gte:0|max:100000',
                'min_stock_alert' => 'nullable|integer|gte:0',
                'purchase_price' => $preferences->preference_purchase_price == 1 ? 'required|numeric|gte:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                'mrp' => $preferences->preference_mrp == 1 ? 'required|numeric|gt:0|max:100000' : 'nullable|numeric|gt:0|max:100000',
                'sale_price' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'max:100000',
                    function ($attribute, $value, $fail) use ($request, $preferences) {
                            $mrp = $preferences->preference_mrp == 1 ? floatval($request->input('mrp')) : floatval($value);
                            if (floatval($value) > $mrp) {
                                $fail('Sale price cannot be greater than MRP.');
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
            },
        ];

        $validate = Validator::make($request->all(), $rules);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error'),
                'data' => $validate->errors(),
                'row_index' => $request->row_index,
                'cell_index' => $request->cell_index ?? 0,
                'errors' => [
                    'coordinates' => array_keys($validate->errors()->toArray()),
                    'grid_coordinates' => array_map(
                        fn($key) => str_pad($request->row_index, 2, '0', STR_PAD_LEFT)
                        . ','
                        . str_pad(array_search($key, $columnMap), 2, '0', STR_PAD_LEFT),
                        array_keys($validate->errors()->toArray())
                    ),
                    'messages' => array_values($validate->errors()->all()),
                ],
            ], 403);
        }

        // ─────────────────────────────────────────────────────────────────────
        // INLINE HELPER: Clear all registered dispatch locks for a jobId
        // ─────────────────────────────────────────────────────────────────────
        $clearDispatchLocks = function (int $userId, string $jobId) {
            $dispatchIndex = "excel_fetch_dispatch_keys_{$userId}_{$jobId}";
            $allDispatchKeys = Cache::get($dispatchIndex, []);
            foreach ($allDispatchKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget($dispatchIndex);
        };

        // ─────────────────────────────────────────────────────────────────────
        // INLINE HELPER: Recompute errors block for all flagged rows in cache
        // ─────────────────────────────────────────────────────────────────────
        $recomputeCacheErrors = function (array &$cachedResult) use ($user, $preferences, $table_name) {
            $errors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];

            $fieldOrder = [
                'item_name',
                'quantity',
                'min_stock_alert',
                'purchase_price',
                'mrp',
                'sale_price',
                'unit',
                'hsn',
                'barcode',
                'gst',
                'cess',
            ];

            // Build id → normalised_name map for duplicate detection
            $allItemNameMap = DB::table($table_name)
                ->select('id', 'item_name')
                ->get()
                ->mapWithKeys(fn($r) => [
                    $r->id => strtoupper(trim(
                        ItemHelpher::checkSpecialCharacterFromString($r->item_name ?? '')
                    ))
                ])
                ->toArray();

            foreach ($cachedResult['data'] as $newIndex => $row) {
                // Only re-validate rows flagged as having errors — skip clean rows
                if ((int) ($row['flag'] ?? 0) === 0)
                    continue;

                $itemNamesExcludingCurrent = array_values(
                    array_filter(
                        $allItemNameMap,
                        fn($id) => $id !== $row['id'],
                        ARRAY_FILTER_USE_KEY
                    )
                );

                $rowArray = [
                    'item_name' => $row['item_name'],
                    'quantity' => $row['quantity'],
                    'min_stock_alert' => $row['min_stock_alert'],
                    'purchase_price' => $row['purchase_price'],
                    'mrp' => $row['mrp'],
                    'sale_price' => $row['sale_price'],
                    'unit' => $row['unit'],
                    'hsn' => $row['hsn'],
                    'barcode' => $row['barcode'],
                    'gst' => $row['gst'],
                    'cess' => $row['cess'],
                ];

                $validationResult = ExcelHelpher::validateRow(
                    $rowArray,
                    $preferences,
                    0,
                    $itemNamesExcludingCurrent,
                    $user
                );

                // Update flag in case it changed (e.g. user fixed the error)
                $cachedResult['data'][$newIndex]['flag'] = $validationResult['flag'];

                if (!empty($validationResult['errors'])) {
                    foreach ($validationResult['errors'] as $field => $errorMessage) {
                        $colIndex = array_search($field, $fieldOrder, true);
                        $colIndex = $colIndex !== false ? $colIndex + 1 : 1;

                        $errors['coordinates'][] = "items.{$newIndex}.{$field}";
                        $errors['grid_coordinates'][] = sprintf("%02d,%02d", $newIndex, $colIndex);

                        if (!in_array($errorMessage, $errors['messages'])) {
                            $errors['messages'][] = $errorMessage;
                        }
                    }
                }
            }

            $cachedResult['errors'] = $errors;
            $cachedResult['message'] = empty($errors['messages'])
                ? __('validation.Data fetched successfully')
                : __('validation.Some rows contain errors');
        };

        // ─────────────────────────────────────────────────────────────────────
        // HELPER: Patch a single row in cache + recompute errors + clear locks
        // ─────────────────────────────────────────────────────────────────────
        $patchCache = function (array $fieldsToUpdate, int $rowId, ?string $jobId) use ($user, $clearDispatchLocks, $recomputeCacheErrors) {
            if (!$jobId)
                return;

            $cacheKey = "excel_fetch_result_{$user->user_id}_{$jobId}";
            $progressKey = "excel_fetch_progress_{$user->user_id}_{$jobId}";

            $cachedResult = Cache::get($cacheKey);
            if (!$cachedResult)
                return;

            // Map DB field names → cache field names
            $cacheFieldMap = [
                'short_unit' => 'unit',
                'rate1' => 'gst',
                'rate2' => 'cess',
            ];

            // Patch the specific row's fields in the cached data array
            $cachedResult['data'] = array_map(
                function ($row) use ($fieldsToUpdate, $rowId, $cacheFieldMap) {
                    if ((int) $row['id'] !== $rowId)
                        return $row;
                    foreach ($fieldsToUpdate as $dbField => $value) {
                        $cacheField = $cacheFieldMap[$dbField] ?? $dbField;
                        if (array_key_exists($cacheField, $row)) {
                            $row[$cacheField] = $value;
                        }
                    }
                    return $row;
                },
                $cachedResult['data'] ?? []
            );

            // ✅ Recompute errors for all flagged rows after the patch
            $recomputeCacheErrors($cachedResult);

            Cache::put($cacheKey, $cachedResult, now()->addMinutes(30));

            // Mark progress complete — next poll returns patched data immediately
            Cache::put($progressKey, [
                'percentage' => 100,
                'message' => __('validation.Data fetched successfully'),
                'success' => true,
            ], now()->addMinutes(30));

            // ✅ Clear ALL dispatch locks for this jobId
            $clearDispatchLocks($user->user_id, $jobId);
        };

        // ─────────────────────────────────────────────────────────────────────
        // NEW ROW INSERT (id == 0)
        // ─────────────────────────────────────────────────────────────────────
        if ($request->id == 0) {

            $updateData = [
                'item_name' => ItemHelpher::checkSpecialCharacterFromString($request->input('item_name')),
                'tags' => TagsHelper::createOrUpdateTag($request->input('item_name')),
                'quantity' => $request->input('quantity') ?? 0,
                'min_stock_alert' => $request->input('min_stock_alert') ?? 0,
                'purchase_price' => $request->input('purchase_price') ?? 0,
                'mrp' => $request->input('mrp') ?? '-',
                'sale_price' => $request->input('sale_price'),
                'full_unit' => ItemHelpher::getFullUnit(strtoupper($request->input('short_unit'))),
                'short_unit' => $request->input('short_unit'),
                'hsn' => $request->input('hsn') ?? '-',
                'barcode' => $request->input('barcode') ?? '',
                'tax1' => 'GST',
                'rate1' => $request->input('gst') ?? 0,
                'tax2' => 'CESS',
                'rate2' => $request->input('cess') ?? 0,
            ];

            $newId = DB::table($table_name)->insertGetId(
                array_merge($updateData, ['created_at' => now(), 'updated_at' => now()])
            );

            $jobId = $request->input('job_id');
            if ($jobId) {
                $cacheKey = "excel_fetch_result_{$user->user_id}_{$jobId}";
                $progressKey = "excel_fetch_progress_{$user->user_id}_{$jobId}";

                $cachedResult = Cache::get($cacheKey);

                if ($cachedResult) {
                    $newRow = [
                        'original_index' => 0,
                        'item_name' => $updateData['item_name'],
                        'quantity' => $updateData['quantity'],
                        'min_stock_alert' => $updateData['min_stock_alert'],
                        'purchase_price' => $updateData['purchase_price'],
                        'mrp' => $updateData['mrp'],
                        'sale_price' => $updateData['sale_price'],
                        'unit' => $updateData['short_unit'],
                        'hsn' => $updateData['hsn'],
                        'barcode' => $updateData['barcode'],
                        'gst' => $updateData['rate1'],
                        'cess' => $updateData['rate2'],
                        'flag' => 1, // corrected by recomputeCacheErrors below
                        'id' => $newId,
                    ];

                    // Shift original_index of existing rows by +1
                    $existingData = array_map(function ($row) {
                        $row['original_index'] = $row['original_index'] + 1;
                        return $row;
                    }, $cachedResult['data'] ?? []);

                    array_unshift($existingData, $newRow);

                    $cachedResult['data'] = $existingData;
                    $cachedResult['recordsTotal'] = ($cachedResult['recordsTotal'] ?? 0) + 1;
                    $cachedResult['recordsFiltered'] = ($cachedResult['recordsFiltered'] ?? 0) + 1;

                    // ✅ Recompute errors — new row gets proper error coordinates
                    $recomputeCacheErrors($cachedResult);

                    Cache::put($cacheKey, $cachedResult, now()->addMinutes(30));

                    Cache::put($progressKey, [
                        'percentage' => 100,
                        'message' => __('validation.Data fetched successfully'),
                        'success' => true,
                    ], now()->addMinutes(30));

                    // ✅ Clear ALL dispatch locks
                    $clearDispatchLocks($user->user_id, $jobId);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => __('validation.New Item Successfully Added'),
                'data' => ['id' => $newId],
            ], 200);
        }

        // ─────────────────────────────────────────────────────────────────────
        // EXISTING ROW UPDATE
        // ─────────────────────────────────────────────────────────────────────
        if ($column_name === 'item_name') {
            $updateData = [
                'item_name' => ItemHelpher::checkSpecialCharacterFromString($request->input($column_name)),
                'tags' => TagsHelper::createOrUpdateTag($request->input($column_name)),
            ];
        } elseif ($column_name === 'short_unit') {
            $updateData = [
                'full_unit' => ItemHelpher::getFullUnit(strtoupper($request->input($column_name))),
                'short_unit' => $request->input($column_name),
            ];
        } elseif ($column_name === 'gst') {
            $updateData = ['tax1' => 'GST', 'rate1' => $request->input($column_name)];
        } elseif ($column_name === 'cess') {
            $updateData = ['tax2' => 'CESS', 'rate2' => $request->input($column_name)];
        } else {
            $updateData = [$column_name => $request->input($column_name)];
        }

        $updated = DB::table($table_name)->where('id', $request->id)->update($updateData);

        if ($updated) {
            // ✅ Patch cache in-place — errors recomputed, no job re-dispatch
            $patchCache($updateData, (int) $request->id, $request->input('job_id'));
        }

        return response()->json([
            'status' => $updated ? 'success' : 'failed',
            'message' => $updated
                ? __('validation.Item successfully updated')
                : __('validation.No changes made'),
            'data' => $updateData,
        ], 200);
    }

    public function addMultipleItems(Request $request)
    {
        try {
            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Unauthorized')
                ], 401);
            }

            $user = Auth::guard('api')->user();
            $jobId = $request->input('job_id');

            // If job_id is provided, check progress
            if ($jobId) {
                $progress = Cache::get("export_inventory_progress_{$user->user_id}_{$jobId}", [
                    'percentage' => 0,
                    'message' => __('validation.Waiting to start') . '...',
                    'success' => null,
                ]);

                if ($progress['percentage'] === 100 && $progress['success'] !== null) {
                    if ($progress['success']) {
                        return response()->json([
                            'status' => 1,
                            'progress' => $progress,
                        ]);
                    } else {
                        $result = Cache::get("export_inventory_result_{$user->user_id}_{$jobId}");
                        return response()->json([
                            'status' => 0,
                            'progress' => $progress,
                            'result' => $result,
                            'errors' => $result['errors'],
                            'data' => $result['data'],
                            'isErrorExsist' => $result['isErrorExsist'],
                        ], 200);
                    }
                }

                return response()->json([
                    'status' => 1,
                    'progress' => $progress,
                ]);
            }

            // If no job_id, initiate new export
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

            $temp_table_name = ExcelHelpher::createTempTableName($user->user_id);

            if (!Schema::connection($user->module_type)->hasTable($temp_table_name)) {
                return response()->json([
                    'status' => 0,
                    'message' => __('validation.No table found, returning empty data'),
                ], 404);
            }

            $jobId = uniqid('export_');

            // Dispatch the job
            ProcessExportToInventoryJob::dispatch($user->user_id, $jobId, $request->action);

            return response()->json([
                'status' => 1,
                'message' => __('validation.Export to inventory started'),
                'job_id' => $jobId,
            ], 202);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 0,
                'message' => __('validation.An error occurred while processing the request'),
                'error' => $e->getMessage(),
            ], 500);
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
        $table_name = ExcelHelpher::createTempTableName($user->user_id);

        if (!$table_name) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Table not found')
            ], 404);
        }

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

        $items = DB::table($table_name)->whereIn('id', $ids)->get();
        if ($items->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.No matching items found')
            ], 404);
        }

        $deleted = DB::table($table_name)->whereIn('id', $ids)->delete();

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

    ############################################################################################### APP VERSION CODE ###############################################################################################
    public function app_previewUploadedExcel(Request $request)
    {
        try {

            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get authenticated user
            $user = Auth::guard('api')->user();

            // Validate the uploaded file
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,csv|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }


            $filePath = $request->file('file');

            $userId = $user->user_id;
            $jobId = uniqid();

            // $user = User::findOrFail($userId);
            $temp_table_name = ExcelHelpher::app_createTempTableName($userId);
            ExcelHelpher::generateTempTableForAdmin($userId, $temp_table_name);

            $data = ExcelHelpher::parseExcel($filePath);

            if (isset($data['status']) && $data['status'] != '0') {
                return response()->json([
                    'status' => 0,
                    'message' => $data['message'],
                ], 400);
            }

            $rowCount = count($data);
            if ($rowCount > 50000) {
                return response()->json([
                    'status' => 0,
                    'message' => __('validation.The Excel file contains') . ' ' . $rowCount . ' ' . __('validation.rows, but the maximum allowed is 50,000')
                ], 400);
            }

            $preferences = DB::connection($user->module_type)
                ->table('preferences')
                ->where('user_id', $userId)
                ->first();

            $validRows = [];
            $invalidRows = [];
            $errorMap = [];
            $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $itemNames = [];

            foreach ($data as $row) {
                $itemName = $row['item_name'] ?? 'New Item';
                $cleanedItemName = strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($itemName)));
                $itemNames[] = $cleanedItemName;
            }

            $chunkSize = 1000;
            foreach (array_chunk($data, $chunkSize) as $chunkIndex => $chunk) {
                $insertData = [];

                foreach ($chunk as $key => $row) {
                    $globalKey = ($chunkIndex * $chunkSize) + $key;
                    $validationResult = ExcelHelpher::validateRow($row, $preferences, 0, $itemNames, $user);

                    $rowData = [
                        'original_index' => $globalKey,
                        'item_name' => $row['item_name'] ?? 'New Item',
                        'quantity' => $row['quantity'] ?? '0',
                        'min_stock_alert' => $row['min_stock_alert'] ?? '0',
                        'purchase_price' => $row['purchase_price'] ?? '0',
                        'mrp' => $row['mrp'] ?? '0',
                        'sale_price' => $row['sale_price'] ?? '0',
                        'unit' => $row['unit'] ?? 'Unit',
                        'hsn' => $row['hsn'] ?? '0000',
                        'barcode' => $row['barcode'] ?? '',
                        'gst' => $row['gst'] ?? '0',
                        'cess' => $row['cess'] ?? '0',
                        'flag' => $validationResult['flag'],
                    ];

                    if (!empty($validationResult['errors'])) {
                        $errorMap[$globalKey] = $validationResult['errors'];
                        $errorIndex = count($invalidRows);
                        foreach ($validationResult['errors'] as $field => $errorMessage) {
                            $newErrors['coordinates'][] = "items.$errorIndex.$field";
                            $newErrors['grid_coordinates'][] = sprintf("%02d,%02d", $errorIndex, array_search($field, array_keys($rowData), true));
                            if (!in_array($errorMessage, $newErrors['messages'])) {
                                $newErrors['messages'][] = $errorMessage;
                            }
                        }
                        $invalidRows[] = $rowData;
                    } else {
                        $validRows[] = $rowData;
                    }

                    $insertData[] = [
                        'item_name' => $rowData['item_name'],
                        // 'quantity' => $rowData['quantity'],
                        'quantity' => !empty($rowData['quantity']) ? $rowData['quantity'] : '0',
                        // 'min_stock_alert' => $rowData['min_stock_alert'],
                        'min_stock_alert' => !empty($rowData['min_stock_alert']) ? $rowData['min_stock_alert'] : '0',
                        // 'mrp' => $rowData['mrp'],
                        'purchase_price' => !empty($rowData['purchase_price']) ? $rowData['purchase_price'] : '0',
                        'mrp' => !empty($rowData['mrp']) ? $rowData['mrp'] : '0',
                        // 'sale_price' => $rowData['sale_price'],
                        'sale_price' => !empty($rowData['sale_price']) ? $rowData['sale_price'] : '0',
                        'full_unit' => $rowData['unit'],
                        'short_unit' => $rowData['unit'],
                        'hsn' => $rowData['hsn'],
                        'barcode' => $rowData['barcode'],
                        'tax1' => 'GST',
                        // 'rate1' => $rowData['gst'],
                        'rate1' => !empty($rowData['gst']) ? $rowData['gst'] : '0',
                        'tax2' => 'CESS',
                        // 'rate2' => $rowData['cess'],
                        'rate2' => !empty($rowData['cess']) ? $rowData['cess'] : '0',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($insertData)) {
                    DB::connection($user->module_type)->transaction(function () use ($temp_table_name, $insertData, $user) {
                        DB::connection($user->module_type)->table($temp_table_name)->insert($insertData);
                    });
                }
            }

            $response = array_merge($invalidRows, $validRows);
            $responseData = [
                'data' => $response,
                'status' => empty($newErrors['messages']) ? 1 : 0,
                'errors' => $newErrors,
                'message' => empty($newErrors['messages']) ? __('validation.File processed successfully') : __('valdation.Some rows contain errors'),
                'itemNames' => $itemNames,
            ];

            Cache::put("excel_upload_result_{$userId}_{$jobId}", $responseData, now()->addHours(1));

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Excel upload processing failed', [
                'user_id' => $userId,
                'job_id' => $jobId,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
            return response()->json([
                'status' => 0,
                'message' => __('validation.unable_to_process'),
            ], 500);
        }
    }

    public function app_addMultipleItems(Request $request)
    {
        try {

            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Unauthorized')
                ], 401);
            }

            $user = Auth::guard('api')->user();


            // Initial validation
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


            $userId = $user->user_id;
            $jobId = uniqid();
            $action = $request->input('action');

            $user = User::findOrFail($userId);
            $temp_table_name = ExcelHelpher::app_createTempTableName($userId);

            $table_name = '';
            if ($user->isAdmin == 1) {
                $business_name = strtolower(str_replace(' ', '_', $user->shop->business_name));
                $table_name = 'item_' . $business_name . '_' . $user->mobile;
            } else if ($user->isAdmin == 0) {
                $staff = $user->staff;
                $adminDetail = Shop::find($staff->addedBy);
                $business_name = strtolower(str_replace(' ', '_', $adminDetail->business_name));
                $table_name = 'item_' . $business_name . '_' . $adminDetail->user->mobile;
            }

            $preferences = DB::connection($user->module_type)
                ->table('preferences')
                ->where('user_id', $userId)
                ->first();

            if (!Schema::connection($user->module_type)->hasTable($temp_table_name)) {
                return response()->json([
                    'status' => 0,
                    'message' => __('validation.No temporary table found')
                ], 400);
            }

            $itemNames = [];
            DB::connection($user->module_type)->table($temp_table_name)
                ->select('item_name')
                ->orderBy('id')
                ->get()
                ->each(function ($row) use (&$itemNames) {
                    $itemName = $row->item_name ?? 'New Item';
                    $cleanedItemName = strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($itemName)));
                    $itemNames[] = $cleanedItemName;
                });

            $isErrorExsist = 0;
            $errorMap = [];
            $response = [];
            $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $chunkSize = 1000;

            DB::connection($user->module_type)->table($temp_table_name)
                ->orderBy('id')
                ->chunk($chunkSize, function ($tempData, $chunkIndex) use ($preferences, $itemNames, &$isErrorExsist, &$errorMap, &$response, &$newErrors, $chunkSize, $user, $action) {
                    foreach ($tempData as $key => $row) {
                        $globalKey = ($chunkIndex * $chunkSize) + $key;
                        $rowArray = [
                            'item_name' => ItemHelpher::checkSpecialCharacterFromString(strtoupper($row->item_name ?? '')),
                            'quantity' => $row->quantity ?? '',
                            'min_stock_alert' => $row->min_stock_alert ?? '',
                            'purchase_price' => $row->purchase_price ?? '',
                            'mrp' => $row->mrp ?? '',
                            'sale_price' => $row->sale_price ?? '',
                            'unit' => $row->short_unit ?? '',
                            'hsn' => $row->hsn ?? '',
                            'barcode' => $row->barcode ?? '',
                            'gst' => $row->rate1 ?? '',
                            'cess' => $row->rate2 ?? '',
                        ];

                        $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, $action, $itemNames, $user);

                        if (!empty($validationResult['errors'])) {
                            $errorMap[$globalKey] = $validationResult['errors'];
                            $isErrorExsist = 1;
                        }

                        $response[] = [
                            'original_index' => $globalKey,
                            'item_name' => $rowArray['item_name'],
                            'quantity' => $rowArray['quantity'],
                            'min_stock_alert' => $rowArray['min_stock_alert'],
                            'purchase_price' => $rowArray['purchase_price'],
                            'mrp' => $rowArray['mrp'],
                            'sale_price' => $rowArray['sale_price'],
                            'unit' => $rowArray['unit'],
                            'hsn' => $rowArray['hsn'],
                            'barcode' => $rowArray['barcode'],
                            'gst' => $rowArray['gst'],
                            'cess' => $rowArray['cess'],
                            'flag' => $validationResult['flag'],
                        ];
                    }
                });

            usort($response, fn($a, $b) => $b['flag'] <=> $a['flag']);

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

            if ($isErrorExsist == 0) {
                try {
                    DB::beginTransaction();

                    if ($action == 2) {
                        DB::connection($user->module_type)->table($table_name)->delete();
                    }

                    $totalInserted = 0;
                    DB::connection($user->module_type)->table($temp_table_name)
                        ->orderBy('id')
                        ->chunk($chunkSize, function ($tempData) use ($table_name, &$totalInserted, $user) {
                            $insertData = [];
                            foreach ($tempData as $row) {
                                $item_name = ItemHelpher::checkSpecialCharacterFromString(strtoupper($row->item_name ?? ''));
                                $insertData[] = [
                                    'item_name' => $item_name,
                                    'quantity' => !empty($row->quantity) ? $row->quantity : '–',
                                    'min_stock_alert' => !empty($row->min_stock_alert) ? $row->min_stock_alert : '–',
                                    'purchase_price' => !empty($row->purchase_price) ? $row->purchase_price : 0,
                                    'mrp' => !empty($row->mrp) ? $row->mrp : '–',
                                    'sale_price' => $row->sale_price,
                                    'full_unit' => ItemHelpher::getFullUnit(strtoupper($row->short_unit)),
                                    'short_unit' => strtoupper($row->short_unit ?? ''),
                                    'hsn' => (preg_match('/^0+$/', $row->hsn ?? '')) ? 0 : (!empty($row->hsn) ? $row->hsn : '–'),
                                    'barcode' => (preg_match('/^0+$/', $row->barcode ?? '')) ? 0 : (!empty($row->barcode) ? $row->barcode : '–'),
                                    'tax1' => 'GST',
                                    'rate1' => $row->rate1 ?? 0,
                                    'tax2' => 'CESS',
                                    'rate2' => $row->rate2 ?? 0,
                                    'tags' => TagsHelper::createOrUpdateTag($item_name),
                                ];
                            }

                            DB::connection($user->module_type)->table($table_name)->insert($insertData);
                            $totalInserted += count($insertData);
                            unset($insertData);
                        });

                    DB::commit();

                    ExcelHelpher::dropTempTable($temp_table_name, $user->module_type);
                    Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);
                    Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

                    return response()->json([
                        'status' => 1,
                        'message' => "$totalInserted " . __('validation.Items Successfully Added'),
                        'data' => $response,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Export to inventory transaction failed: ' . $e->getMessage());
                    report($e);
                    return response()->json([
                        'status' => 0,
                        'message' => __('validation.unable_to_process'),
                    ], 500);
                }
            } else {
                $responseData = [
                    'status' => 'failed',
                    'message' => __('validation.Validation Error! No database changes were made'),
                    'errors' => $newErrors,
                    'data' => $response,
                    'isErrorExsist' => $isErrorExsist,
                ];
                Cache::put("export_inventory_result_{$userId}_{$jobId}", $responseData, now()->addHours(1));

                return response()->json($responseData, 400);
            }
        } catch (\Exception $e) {
            \Log::error('Export to inventory failed: ' . $e->getMessage());
            report($e);
            return response()->json([
                'status' => 0,
                'message' => __('validation.unable_to_process'),
            ], 500);
        }
    }

    public function app_fetchExcelData(Request $request)
    {
        try {

            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Unauthorized')
                ], 401);
            }

            // Get authenticated user
            $user = Auth::guard('api')->user();

            // Validate input parameters
            $userId = $user->user_id;
            $jobId = $request->input('job_id');
            $draw = $request->input('draw', 0);
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value');
            $orderColumn = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'asc');

            // // Validate user
            // // $user = User::find($userId);
            // if (!$user) {
            //     return $this->errorResponse($draw, 'User not found.');
            // }

            // Generate table name
            $temp_table_name = ExcelHelpher::app_createTempTableName($userId);
            $preferences = DB::connection($user->module_type)->table('preferences')->where('user_id', $userId)->first();
            if (!$preferences) {
                return $this->errorResponse($draw, __('validation.User preferences not found'));
            }

            // Check if temporary table exists
            if (!Schema::connection($user->module_type)->hasTable($temp_table_name)) {
                return $this->errorResponse($draw, __('validation.No table found, returning empty data'));
            }

            // Build query
            $query = DB::connection($user->module_type)->table($temp_table_name);
            $totalRecords = $query->count();

            // Apply search filter
            $filteredRecords = $totalRecords;
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('item_name', 'like', "%{$searchValue}%")
                        ->orWhere('quantity', 'like', "%{$searchValue}%")
                        ->orWhere('min_stock_alert', 'like', "%{$searchValue}%")
                        ->orWhere('purchase_price', 'like', "%{$searchValue}%")
                        ->orWhere('mrp', 'like', "%{$searchValue}%")
                        ->orWhere('sale_price', 'like', "%{$searchValue}%")
                        ->orWhere('short_unit', 'like', "%{$searchValue}%")
                        ->orWhere('hsn', 'like', "%{$searchValue}%")
                        ->orWhere('barcode', 'like', "%{$searchValue}%")
                        ->orWhere('rate1', 'like', "%{$searchValue}%")
                        ->orWhere('rate2', 'like', "%{$searchValue}%");
                });
                $filteredRecords = $query->count();
            }

            // Apply sorting
            $columnMap = [
                1 => 'item_name',
                2 => 'quantity',
                3 => 'min_stock_alert',
                4 => 'purchase_price',
                5 => 'mrp',
                6 => 'sale_price',
                7 => 'short_unit',
                8 => 'hsn',
                9 => 'barcode',
                10 => 'rate1',
                11 => 'rate2',
            ];
            if ($orderColumn && isset($columnMap[$orderColumn])) {
                $query->orderBy($columnMap[$orderColumn], $orderDirection);
            } else {
                $query->orderBy('id', 'asc');
            }

            // Apply pagination
            if ($length > 0) {
                $query->offset($start)->limit($length);
            }

            // Fetch data
            $allData = $query->get();
            $data = [];
            $errors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $errorMap = [];

            // Collect item names for validation
            $itemNames = DB::connection($user->module_type)->table($temp_table_name)
                ->pluck('item_name')
                ->map(fn($name) => strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($name ?? 'New Item'))))
                ->toArray();

            // Process data
            foreach ($allData as $key => $row) {
                $rowArray = [
                    'item_name' => $row->item_name ?? '',
                    'quantity' => $row->quantity ?? '',
                    'min_stock_alert' => $row->min_stock_alert ?? '',
                    'purchase_price' => $row->purchase_price ?? '',
                    'mrp' => $row->mrp ?? '',
                    'sale_price' => $row->sale_price ?? '',
                    'unit' => $row->short_unit ?? '',
                    'hsn' => $row->hsn ?? '',
                    'barcode' => $row->barcode ?? '',
                    'gst' => $row->rate1 ?? '',
                    'cess' => $row->rate2 ?? '',
                ];

                $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, 0, $itemNames, $user);

                if (!empty($validationResult['errors'])) {
                    $errorMap[$key + $start] = $validationResult['errors'];
                }

                $data[] = [
                    'original_index' => $key + $start,
                    'item_name' => $row->item_name ?? '',
                    'quantity' => $row->quantity ?? '',
                    'min_stock_alert' => $row->min_stock_alert ?? '',
                    'purchase_price' => $row->purchase_price ?? '',
                    'mrp' => $row->mrp ?? '',
                    'sale_price' => $row->sale_price ?? '',
                    'unit' => $row->short_unit ?? '',
                    'hsn' => $row->hsn ?? '',
                    'barcode' => $row->barcode ?? '',
                    'gst' => $row->rate1 ?? '',
                    'cess' => $row->rate2 ?? '',
                    'flag' => $validationResult['flag'],
                    'id' => $row->id,
                ];
            }

            // Generate errors
            foreach ($data as $newIndex => $row) {
                $originalIndex = $row['original_index'];

                if (isset($errorMap[$originalIndex])) {
                    foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
                        $errors['coordinates'][] = "items.$newIndex.$field";
                        $errors['grid_coordinates'][] = sprintf("%02d,%02d", $newIndex, array_search($field, array_keys($row), true));
                        if (!in_array($errorMessage, $errors['messages'])) {
                            $errors['messages'][] = $errorMessage;
                        }
                    }
                }
            }

            $status = 1;
            $message = empty($errorMap) ? __('validation.Data fetched successfully') : __('validation.Some rows contain errors');

            // Prepare response
            $response = [
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => array_values($data),
                'status' => $status,
                'errors' => $errors,
                'message' => $message,
            ];

            Cache::put("excel_fetch_result_{$userId}_{$jobId}", $response, now()->addMinutes(30));

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('ExcelDataController failed', [
                'user_id' => $userId,
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
            report($e);
            return $this->errorResponse($draw, __('validation.unable_to_process'));
        }
    }

    protected function errorResponse($draw, $message)
    {
        $response = [
            'draw' => intval($draw),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'status' => 0,
            'errors' => ['coordinates' => [], 'grid_coordinates' => [], 'messages' => [$message]],
            'message' => $message,
        ];

        return response()->json($response, 400);
    }
    ############################################################################################### APP VERSION CODE ###############################################################################################

}
