<?php

namespace Modules\GroceryIndia\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

use Modules\GroceryIndia\Helpers\ExcelHelpher;
use Modules\GroceryIndia\Helpers\ItemHelpher;

use Modules\Authentication\Entities\User;

class FetchUploadedExcelDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 10 minutes
    public $tries = 5;

    public $userId;
    public $jobId;
    protected $draw;
    protected $start;
    protected $length;
    protected $searchValue;
    protected $orderColumn;
    protected $orderDirection;

    public function __construct(
        $userId,
        $jobId,
        $draw = 0,
        $start = 0,
        $length = 10,
        $searchValue = null,
        $orderColumn = null,
        $orderDirection = 'asc'
    ) {
        $this->userId = $userId;
        $this->jobId = $jobId;
        $this->draw = $draw;
        $this->start = $start;
        $this->length = $length;
        $this->searchValue = $searchValue;
        $this->orderColumn = $orderColumn;
        $this->orderDirection = $orderDirection;
    }

    public function handle()
    {
        try {
            $this->updateProgress(10, __('validation.Starting data fetch') . '...');

            // Validate user
            $user = User::find($this->userId);
            if (!$user) {
                $this->cacheErrorResponse(__('validation.User not found'));
                return;
            }

            $this->updateProgress(20, __('validation.Checking temporary table') . '...');

            $temp_table_name = ExcelHelpher::createTempTableName($this->userId);
            $preferences = DB::connection($user->module_type)
                ->table('preferences')
                ->where('user_id', $this->userId)
                ->first();

            if (!$preferences) {
                $this->cacheErrorResponse(__('validation.User preferences not found'));
                return;
            }

            if (!Schema::connection($user->module_type)->hasTable($temp_table_name)) {
                $this->cacheErrorResponse(__('validation.No table found, returning empty data'));
                return;
            }

            $this->updateProgress(30, __('validation.Building query') . '...');

            $query = DB::connection($user->module_type)->table($temp_table_name);
            $totalRecords = $query->count();

            $this->updateProgress(40, __('validation.Applying filters') . '...');

            $filteredRecords = $totalRecords;
            if (!empty($this->searchValue)) {
                $query->where(function ($q) {
                    $q->where('item_name', 'like', "%{$this->searchValue}%")
                        ->orWhere('quantity', 'like', "%{$this->searchValue}%")
                        ->orWhere('sku', 'like', "%{$this->searchValue}%")
                        ->orWhere('min_stock_alert', 'like', "%{$this->searchValue}%")
                        ->orWhere('purchase_price', 'like', "%{$this->searchValue}%")
                        ->orWhere('mrp', 'like', "%{$this->searchValue}%")
                        ->orWhere('sale_price', 'like', "%{$this->searchValue}%")
                        ->orWhere('short_unit', 'like', "%{$this->searchValue}%")
                        ->orWhere('hsn', 'like', "%{$this->searchValue}%")
                        ->orWhere('barcode', 'like', "%{$this->searchValue}%")
                        ->orWhere('rate1', 'like', "%{$this->searchValue}%")
                        ->orWhere('rate2', 'like', "%{$this->searchValue}%");
                });
                $filteredRecords = $query->count();
            }

            $this->updateProgress(50, 'Applying sorting...');

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
            //     10 => 'rate1',
            //     11 => 'rate2',
            // ];

            $columnMap = ExcelHelpher::getColumnsMap();

            if ($this->orderColumn && isset($columnMap[$this->orderColumn])) {
                $columnName = $columnMap[$this->orderColumn];
                $direction = strtolower($this->orderDirection) === 'desc' ? 'desc' : 'asc';
                $numericColumns = ['quantity', 'min_stock_alert', 'purchase_price', 'mrp', 'sale_price', 'rate1', 'rate2'];
                if (in_array($columnName, $numericColumns)) {
                    $query->orderByRaw("CAST({$columnName} AS DECIMAL(15,2)) {$direction}");
                } else {
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('id', 'asc');
            }

            $this->updateProgress(60, __('validation.Applying pagination') . '...');

            if ($this->length > 0) {
                $query->offset($this->start)->limit($this->length);
            }

            $this->updateProgress(70, __('validation.Fetching data') . '...');

            $allData = $query->get();

            $this->updateProgress(75, __('validation.Loading item names for duplicate check') . '...');

            // ─── Fetch ALL rows (id + item_name) once for duplicate detection ───────
            // We build a map of [ id => normalised_name ] so we can exclude
            // each row from its own duplicate check — prevents a freshly added
            // "New Item" row from being flagged against itself on the very first fetch.
            $allItemNameMap = DB::connection($user->module_type)
                ->table($temp_table_name)
                ->select('id', 'item_name')
                ->get()
                ->mapWithKeys(fn($r) => [
                    $r->id => strtoupper(trim(
                        ItemHelpher::checkSpecialCharacterFromString($r->item_name ?? '')
                    ))
                ])
                ->toArray();
            // ─────────────────────────────────────────────────────────────────────────

            $data = [];
            $errors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $errorMap = [];

            $this->updateProgress(80, __('validation.Validating data') . '...');

            foreach ($allData as $key => $row) {
                $rowArray = [
                    'sku' => $row->sku ?? '',
                    'barcode' => $row->barcode ?? '',
                    'item_name' => $row->item_name ?? '',
                    'category_id' => $row->category_id ?? '',
                    'unit' => $row->short_unit ?? '',
                    'hsn' => $row->hsn ?? '',
                    'quantity' => $row->quantity ?? '',
                    'min_stock_alert' => $row->min_stock_alert ?? '',
                    'purchase_price' => $row->purchase_price ?? '',
                    'mrp' => $row->mrp ?? '',
                    'sale_price' => $row->sale_price ?? '',
                    'gst' => $row->rate1 ?? '',
                    'cess' => $row->rate2 ?? '',
                ];

                // ✅ Build itemNames excluding the current row's own id
                // This ensures a row is never compared against itself,
                // so a newly added "New Item" row is not flagged as a duplicate
                // on the first fetch.
                $itemNamesExcludingCurrent = array_values(
                    array_filter(
                        $allItemNameMap,
                        fn($id) => $id !== $row->id,
                        ARRAY_FILTER_USE_KEY
                    )
                );

                $validationResult = ExcelHelpher::validateRow(
                    $rowArray,
                    $preferences,
                    0,
                    $itemNamesExcludingCurrent,
                    $user
                );

                if (!empty($validationResult['errors'])) {
                    $errorMap[$key + $this->start] = $validationResult['errors'];
                }

                $data[] = [
                    'original_index' => $key + $this->start,
                    'sku' => $row->sku ?? '',
                    'barcode' => $row->barcode ?? '',
                    'item_name' => $row->item_name ?? '',
                    'category_id' => $row->category_id ?? '',
                    'unit' => $row->short_unit ?? '',
                    'hsn' => $row->hsn ?? '',
                    'quantity' => $row->quantity ?? '',
                    'min_stock_alert' => $row->min_stock_alert ?? '',
                    'purchase_price' => $row->purchase_price ?? '',
                    'mrp' => $row->mrp ?? '',
                    'sale_price' => $row->sale_price ?? '',
                    'gst' => $row->rate1 ?? '',
                    'cess' => $row->rate2 ?? '',
                    'flag' => $validationResult['flag'],
                    'id' => $row->id,
                ];
            }

            $this->updateProgress(90, __('validation.Generating errors') . '...');

            foreach ($data as $newIndex => $row) {
                $originalIndex = $row['original_index'];

                if (isset($errorMap[$originalIndex])) {
                    foreach ($errorMap[$originalIndex] as $field => $errorMessage) {
                        $errors['coordinates'][] = "items.$newIndex.$field";

                        // Column index based on the $rowArray key order (1-based, matching frontend columnMap)
                        // $fieldOrder = ['item_name', 'quantity', 'min_stock_alert', 'purchase_price', 'mrp', 'sale_price', 'unit', 'hsn', 'barcode', 'gst', 'cess'];
                        
                        
                        $fieldOrder = [
                            'sku',
                            'barcode',
                            'item_name', 
                            'category_id',
                            'unit', 
                            'hsn', 
                            'quantity', 
                            'min_stock_alert', 
                            'purchase_price',
                            'mrp', 
                            'sale_price',  
                            'gst', 
                            'cess'
                        ];
                        
                        
                        $colIndex = array_search($field, $fieldOrder, true);
                        $colIndex = $colIndex !== false ? $colIndex + 1 : 1; // 1-based

                        $errors['grid_coordinates'][] = sprintf("%02d,%02d", $newIndex, $colIndex);

                        if (!in_array($errorMessage, $errors['messages'])) {
                            $errors['messages'][] = $errorMessage;
                        }
                    }
                }
            }

            $status = 1;
            $message = empty($errorMap)
                ? __('validation.Data fetched successfully')
                : __('validation.Some rows contain errors');

            $this->updateProgress(95, __('validation.Preparing response') . '...');

            $response = [
                'draw' => intval($this->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => array_values($data),
                'status' => $status,
                'errors' => $errors,
                'message' => $message,
            ];

            Cache::put(
                "excel_fetch_result_{$this->userId}_{$this->jobId}",
                $response,
                now()->addMinutes(30)
            );

            $this->updateProgress(100, __('valiadtion.Data fetched successfully'), true);

        } catch (\Exception $e) {
            Log::error('FetchUploadedExcelDataJob failed', [
                'user_id' => $this->userId,
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);
            $this->cacheErrorResponse(
                __('validation.An error occurred while fetching data') . ': ' . $e->getMessage()
            );
        }
    }

    protected function cacheErrorResponse($message)
    {
        $response = [
            'draw' => intval($this->draw),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'status' => 0,
            'errors' => ['coordinates' => [], 'grid_coordinates' => [], 'messages' => [$message]],
            'message' => $message,
        ];

        Cache::put(
            "excel_fetch_result_{$this->userId}_{$this->jobId}",
            $response,
            now()->addMinutes(30)
        );

        $this->updateProgress(100, $message, false);
    }

    protected function updateProgress($percentage, $message, $success = null)
    {
        Cache::put("excel_fetch_progress_{$this->userId}_{$this->jobId}", [
            'percentage' => $percentage,
            'message' => $message,
            'success' => $success,
        ], now()->addMinutes(30));
    }
}