<?php

namespace Modules\GroceryGermany\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Modules\GroceryGermany\Helpers\ExcelHelpher;
use Modules\GroceryGermany\Helpers\ItemHelpher;

use Modules\Authentication\Entities\User;

class ProcessExcelUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 5 minutes
    public $tries = 5; // Retry 5 times

    protected $filePath;
    public $userId;
    public $jobId;

    public function __construct($filePath, $userId, $jobId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        try {
            $this->updateProgress(10, __('validation.Starting Excel processing') . '...');

            $user = User::findOrFail($this->userId);
            $temp_table_name = ExcelHelpher::createTempTableName($this->userId);

            ExcelHelpher::generateTempTableForAdmin($this->userId);

            $this->updateProgress(20, __('validation.Parsing Excel file') . '...');
            $data = ExcelHelpher::parseExcel($this->filePath);

            if (isset($data['status']) && $data['status'] != '0') {
                $this->updateProgress(100, 'Failed: ' . $data['message'], false);
                return;
            }

            $rowCount = count($data);
            if ($rowCount > 50000) {
                $this->updateProgress(100, __('validation.Failed: The Excel file contains') . ' ' . $rowCount . ' ' . __('validation.rows, but the maximum allowed is 50,000'), false);
                return;
            }

            $this->updateProgress(30, __('validation.Fetching user preferences') . '...');
            $preferences = DB::connection($user->module_type)->table('preferences')->where('user_id', $this->userId)->first();

            $validRows = [];
            $invalidRows = [];
            $errorMap = [];
            $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $itemNames = [];

            $this->updateProgress(40, __('validation.Collecting item names for duplicate checking') . '...');
            foreach ($data as $row) {
                $itemName = $row['item_name'] ?? 'New Item';
                $cleanedItemName = strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($itemName)));
                $itemNames[] = $cleanedItemName;
            }

            $chunkSize = 1000;
            $totalChunks = ceil(count($data) / $chunkSize);
            $chunkIndex = 0;

            $this->updateProgress(50, __('validation.Processing data chunks') . '...');
            foreach (array_chunk($data, $chunkSize) as $chunk) {
                $insertData = [];

                foreach ($chunk as $key => $row) {
                    $globalKey = ($chunkIndex * $chunkSize) + $key;
                    $validationResult = ExcelHelpher::validateRow($row, $preferences, 0, $itemNames, $user);

                    $rowData = [
                        'original_index' => $globalKey,
                        'item_name' => $row['item_name'] ?? 'New Item',
                        'quantity' => $row['quantity'] ?? '0',
                        'min_stock_alert' => $row['min_stock_alert'] ?? '0',
                        'mrp' => $row['mrp'] ?? '0',
                        'sale_price' => $row['sale_price'] ?? '0',
                        'unit' => $row['unit'] ?? 'Unit',
                        'barcode' => $row['barcode'] ?? '',
                        'vat' => $row['vat'] ?? '0',
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
                        'quantity' => !empty($row['quantity']) ? $row['quantity'] : '0',
                        'min_stock_alert' => !empty($row['min_stock_alert']) ? $row['min_stock_alert'] : '0',
                        // 'mrp' => $rowData['mrp'],
                        // 'sale_price' => $rowData['sale_price'],
                        // 'mrp' => isset($rowData['mrp']) ? (float) trim($rowData['mrp']) : 0.0,
                        // 'sale_price' => isset($rowData['sale_price']) ? (float) trim($rowData['sale_price']) : 0.0,
                        'mrp' => !empty($row['mrp']) ? $row['mrp'] : '0',
                        'sale_price' => !empty($row['sale_price']) ? $row['sale_price'] : '0',
                        'full_unit' => ItemHelpher::getFullUnit(strtoupper($row['unit'])),
                        'short_unit' => $rowData['unit'],
                        'barcode' => $rowData['barcode'],
                        'tax1' => 'VAT',
                        'rate1' => !empty($row['vat']) ? $row['vat'] : '0',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($insertData)) {
                    DB::connection($user->module_type)->transaction(function () use ($temp_table_name, $insertData, $user) {
                        DB::connection($user->module_type)->table($temp_table_name)->insert($insertData);
                    });
                }

                $chunkIndex++;
                $progress = 50 + (($chunkIndex / $totalChunks) * 40);
                if ($progress % 5 == 0) {
                    $this->updateProgress($progress, "Processed chunk $chunkIndex of $totalChunks...");
                }
            }

            $response = array_merge($invalidRows, $validRows);
            $responseData = [
                'data' => $response,
                'status' => empty($newErrors['messages']) ? 1 : 0,
                'errors' => $newErrors,
                'message' => empty($newErrors['messages']) ? __('validation.File processed successfully') : __('validation.Some rows contain errors'),
                'itemNames' => $itemNames,
            ];

            Cache::put("excel_upload_result_{$this->userId}_{$this->jobId}", $responseData, now()->addHours(1));
            $this->updateProgress(100, __('validation.Excel processing completed successfully'), true);

            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        } catch (\Exception $e) {
            \Log::error('ProcessExcelUploadJob failed', [
                'user_id' => $this->userId,
                'job_id' => $this->jobId,
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->updateProgress(100, __('validation.Failed: An error occurred while processing the Excel file') . ': ' . $e->getMessage(), false);
        }
    }

    protected function updateProgress($percentage, $message, $success = null)
    {
        Cache::put("excel_upload_progress_{$this->userId}_{$this->jobId}", [
            'percentage' => $percentage,
            'message' => $message,
            'success' => $success,
        ], now()->addHours(1));
    }
}
