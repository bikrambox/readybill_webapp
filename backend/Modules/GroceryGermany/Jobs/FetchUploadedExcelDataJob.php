<?php

namespace Modules\GroceryGermany\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

use Modules\GroceryGermany\Helpers\ExcelHelpher;
use Modules\GroceryGermany\Helpers\ItemHelpher;

use Modules\Authentication\Entities\User;

class FetchUploadedExcelDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 5 minutes
    public $tries = 5; // Retry 5 times

    public $userId;
    public $jobId;
    protected $draw;
    protected $start;
    protected $length;
    protected $searchValue;
    protected $orderColumn;
    protected $orderDirection;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $jobId, $draw = 0, $start = 0, $length = 10, $searchValue = null, $orderColumn = null, $orderDirection = 'asc')
    {
        $this->userId = $userId;
        $this->jobId = $jobId;
        $this->draw = $draw;
        $this->start = $start;
        $this->length = $length;
        $this->searchValue = $searchValue;
        $this->orderColumn = $orderColumn;
        $this->orderDirection = $orderDirection;
    }

    /**
     * Execute the job.
     */
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

            // Generate table name
            $temp_table_name = ExcelHelpher::createTempTableName($this->userId);
            $preferences = DB::connection($user->module_type)->table('preferences')->where('user_id', $this->userId)->first();
            if (!$preferences) {
                $this->cacheErrorResponse(__('validation.User preferences not found'));
                return;
            }

            // Check if temporary table exists
            if (!Schema::connection($user->module_type)->hasTable($temp_table_name)) {
                $this->cacheErrorResponse(__('validation.No table found, returning empty data'));
                return;
            }

            $this->updateProgress(30, __('validation.Building query') . '...');

            // Build query
            $query = DB::connection($user->module_type)->table($temp_table_name);
            $totalRecords = $query->count();

            $this->updateProgress(40, __('validation.Applying filters') . '...');

            // Apply search filter
            $filteredRecords = $totalRecords;
            if (!empty($this->searchValue)) {
                $query->where(function ($q) {
                    $q->where('item_name', 'like', "%{$this->searchValue}%")
                        ->orWhere('quantity', 'like', "%{$this->searchValue}%")
                        ->orWhere('min_stock_alert', 'like', "%{$this->searchValue}%")
                        ->orWhere('mrp', 'like', "%{$this->searchValue}%")
                        ->orWhere('sale_price', 'like', "%{$this->searchValue}%")
                        ->orWhere('short_unit', 'like', "%{$this->searchValue}%")
                        ->orWhere('barcode', 'like', "%{$this->searchValue}%")
                        ->orWhere('rate1', 'like', "%{$this->searchValue}%");
                });
                $filteredRecords = $query->count();
            }

            $this->updateProgress(50, 'Applying sorting...');

            // Apply sorting
            $columnMap = [
                1 => 'item_name',
                2 => 'quantity',
                3 => 'min_stock_alert',
                4 => 'mrp',
                5 => 'sale_price',
                6 => 'short_unit',
                7 => 'barcode',
                8 => 'rate1',
            ];

            if ($this->orderColumn && isset($columnMap[$this->orderColumn])) {
                $columnName = $columnMap[$this->orderColumn];
                $direction = strtolower($this->orderDirection) === 'desc' ? 'desc' : 'asc';
                $numericColumns = ['quantity', 'min_stock_alert', 'mrp', 'sale_price', 'rate1'];
                if (in_array($columnName, $numericColumns)) {
                    $query->orderByRaw("CAST({$columnName} AS DECIMAL(15,2)) {$direction}");
                } else {
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('id', 'asc');
            }

            $this->updateProgress(60, __('validation.Applying pagination') . '...');

            // Apply pagination
            if ($this->length > 0) {
                $query->offset($this->start)->limit($this->length);
            }

            $this->updateProgress(70, __('validation.Fetching data') . '...');

            // Fetch data
            $allData = $query->get();
            $data = [];
            $errors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $errorMap = [];

            $this->updateProgress(80, __('validation.Validating data') . '...');

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
                    'mrp' => $row->mrp ?? '',
                    'sale_price' => $row->sale_price ?? '',
                    'unit' => $row->short_unit ?? '',
                    'barcode' => $row->barcode ?? '',
                    'vat' => $row->rate1 ?? '',
                ];

                $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, 0, $itemNames, $user);

                if (!empty($validationResult['errors'])) {
                    $errorMap[$key + $this->start] = $validationResult['errors'];
                }

                $data[] = [
                    'original_index' => $key + $this->start,
                    'item_name' => $row->item_name ?? '',
                    'quantity' => $row->quantity ?? '',
                    'min_stock_alert' => $row->min_stock_alert ?? '',
                    'mrp' => $row->mrp ?? '',
                    'sale_price' => $row->sale_price ?? '',
                    'unit' => $row->short_unit ?? '',
                    'barcode' => $row->barcode ?? '',
                    'vat' => $row->rate1 ?? '',
                    'flag' => $validationResult['flag'],
                    'id' => $row->id,
                ];
            }

            $this->updateProgress(90, __('validation.Generating errors') . '...');

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

            // $status = empty($errorMap) ? 1 : 0;
            $status = 1;
            $message = empty($errorMap) ? __('validation.Data fetched successfully') : __('validation.Some rows contain errors');

            $this->updateProgress(95, __('validation.Preparing response') . '...');

            // Prepare response
            $response = [
                'draw' => intval($this->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => array_values($data),
                'status' => $status,
                'errors' => $errors,
                'message' => $message,
            ];

            Cache::put("excel_fetch_result_{$this->userId}_{$this->jobId}", $response, now()->addMinutes(30));
            $this->updateProgress(100, __('valiadtion.Data fetched successfully'), true);

        } catch (\Exception $e) {
            Log::error('FetchUploadedExcelDataJob failed', [
                'user_id' => $this->userId,
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);
            $this->cacheErrorResponse(__('validation.An error occurred while fetching data') . ': ' . $e->getMessage());
        }
    }

    /**
     * Cache an error response.
     */
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

        Cache::put("excel_fetch_result_{$this->userId}_{$this->jobId}", $response, now()->addMinutes(30));
        $this->updateProgress(100, $message, false);
    }

    /**
     * Update progress cache.
     */
    protected function updateProgress($percentage, $message, $success = null)
    {
        Cache::put("excel_fetch_progress_{$this->userId}_{$this->jobId}", [
            'percentage' => $percentage,
            'message' => $message,
            'success' => $success,
        ], now()->addMinutes(30));
    }
}
