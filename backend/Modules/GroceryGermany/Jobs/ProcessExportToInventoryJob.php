<?php

namespace Modules\GroceryGermany\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

use Modules\Authentication\Entities\User;
use Modules\GroceryGermany\Entities\Shop;

use Modules\GroceryGermany\Helpers\ExcelHelpher;
use Modules\GroceryGermany\Helpers\ItemHelpher;
use Modules\GroceryGermany\Helpers\TagsHelper;
use Modules\GroceryGermany\Helpers\TableNameHelper;

class ProcessExportToInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 5 minutes
    public $tries = 5; // Retry 5 times

    public $userId;
    public $jobId;
    protected $action;

    public function __construct($userId, $jobId, $action)
    {
        $this->userId = $userId;
        $this->jobId = $jobId;
        $this->action = $action;
    }

    public function handle()
    {
        try {
            $this->updateProgress(10, __('validation.Starting export to inventory') . '...');

            $user = User::findOrFail($this->userId);
            $temp_table_name = ExcelHelpher::createTempTableName($this->userId);
            // $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $table_name = '';
            if ($user->isAdmin == 1) {
                $business_name = strtolower(str_replace(' ', '_', $user->shop->business_name)); // Replace spaces with underscores
                $table_name = 'item_' . $business_name . '_' . $user->mobile;
            } else if ($user->isAdmin == 0) {
                $staff = $user->staff;
                $adminDetail = Shop::find($staff->addedBy);
                $business_name = strtolower(str_replace(' ', '_', $adminDetail->business_name)); // Replace spaces with underscores
                $table_name = 'item_' . $business_name . '_' . $adminDetail->user->mobile;
            }

            $preferences = DB::connection($user->module_type)->table('preferences')->where('user_id', $this->userId)->first();

            if (!Schema::connection($user->module_type)->hasTable($temp_table_name)) {
                $this->updateProgress(100, __('validation.Failed: No temporary table found'), false);
                return;
            }

            $this->updateProgress(20, __('validation.Collecting item names for validation') . '...');
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

            $this->updateProgress(30, __('validation.Validating data') . '...');
            $isErrorExsist = 0;
            $errorMap = [];
            $response = [];
            $newErrors = ['coordinates' => [], 'grid_coordinates' => [], 'messages' => []];
            $chunkSize = 1000;
            $totalChunks = ceil(DB::connection($user->module_type)->table($temp_table_name)->count() / $chunkSize);
            $chunkIndex = 0;

            DB::connection($user->module_type)->table($temp_table_name)
                ->orderBy('id')
                ->chunk($chunkSize, function ($tempData, $chunkIndex) use ($preferences, $itemNames, &$isErrorExsist, &$errorMap, &$response, &$newErrors, $chunkSize, $user, $totalChunks) {
                    foreach ($tempData as $key => $row) {
                        $globalKey = ($chunkIndex * $chunkSize) + $key;
                        $rowArray = [
                            'item_name' => ItemHelpher::checkSpecialCharacterFromString(strtoupper($row->item_name ?? '')),
                            'quantity' => $row->quantity ?? '',
                            'min_stock_alert' => $row->min_stock_alert ?? '',
                            'mrp' => $row->mrp ?? '',
                            'sale_price' => $row->sale_price ?? '',
                            'unit' => $row->short_unit ?? '',
                            'barcode' => $row->barcode ?? '',
                            'vat' => $row->rate1 ?? '',
                        ];

                        $validationResult = ExcelHelpher::validateRow($rowArray, $preferences, $this->action, $itemNames, $user);

                        

                        if (!empty($validationResult['errors'])) {
                            $errorMap[$globalKey] = $validationResult['errors'];
                            $isErrorExsist = 1;

                            // dd([
                            //     'isErrorExsist' => $isErrorExsist,
                            //     'errorMap' => $errorMap[$globalKey],
                            // ]);
                        }

                        $response[] = [
                            'original_index' => $globalKey,
                            'item_name' => $rowArray['item_name'],
                            'quantity' => $rowArray['quantity'],
                            'min_stock_alert' => $rowArray['min_stock_alert'],
                            'mrp' => $rowArray['mrp'],
                            'sale_price' => $rowArray['sale_price'],
                            'unit' => $rowArray['unit'],
                            'barcode' => $rowArray['barcode'],
                            'vat' => $rowArray['vat'],
                            'flag' => $validationResult['flag'],
                        ];
                    }

                    $chunkIndex++;
                    $progress = 30 + (($chunkIndex / $totalChunks) * 30);
                    $this->updateProgress($progress, __('validation.Validated chunk') . " $chunkIndex of $totalChunks...");
                });

            usort($response, fn($a, $b) => $b['flag'] <=> $a['flag']);

            // dd([
            //     'isErrorExsist' => $isErrorExsist,
            //     'response' => $response,
            // ]);


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
                $this->updateProgress(60, __('validation.Inserting data into main table') . '...');
                try {
                    DB::beginTransaction();

                    if ($this->action == 2) {
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
                                    'mrp' => !empty($row->mrp) ? $row->mrp : '–',
                                    'sale_price' => $row->sale_price,
                                    'full_unit' => ItemHelpher::getFullUnit(strtoupper($row->short_unit)),
                                    'short_unit' => strtoupper($row->short_unit ?? ''),
                                    'barcode' => (preg_match('/^0+$/', $row->barcode ?? '')) ? 0 : (!empty($row->barcode) ? $row->barcode : '–'),
                                    'tax1' => 'VAT',
                                    'rate1' => $row->rate1 ?? 0,
                                    'tags' => TagsHelper::createOrUpdateTag($item_name),
                                ];
                            }

                            DB::connection($user->module_type)->table($table_name)->insert($insertData);
                            $totalInserted += count($insertData);
                            unset($insertData);
                        });

                    DB::commit();

                    $this->updateProgress(90, __('validation.Cleaning up'));
                    ExcelHelpher::dropTempTable($temp_table_name, $user->module_type);
                    Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);
                    Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);

                    $this->updateProgress(100, "$totalInserted " . __('validation.Items Successfully Added'), true);
                } catch (\Exception $e) {
                    report($e);
                    DB::rollBack();
                    \Log::error('ProcessExportToInventoryJob transaction failed: ' . $e->getMessage());
                    $this->updateProgress(100, __('validation.unable_to_process'), false);
                }
            } else {
                $responseData = [
                    'status' => 'failed',
                    'message' => __('validation.Validation Error! No database changes were made'),
                    'errors' => $newErrors,
                    'data' => $response,
                    'isErrorExsist' => $isErrorExsist,
                ];
                Cache::put("export_inventory_result_{$this->userId}_{$this->jobId}", $responseData, now()->addHours(1));
                $this->updateProgress(100, 'Validation Error! No database changes were made.', false);
            }
        } catch (\Exception $e) {
            report($e);
            \Log::error('ProcessExportToInventoryJob failed: ' . $e->getMessage());
            $this->updateProgress(100, __('validation.unable_to_process'), false);
        }
    }

    protected function updateProgress($percentage, $message, $success = null)
    {
        Cache::put("export_inventory_progress_{$this->userId}_{$this->jobId}", [
            'percentage' => $percentage,
            'message' => $message,
            'success' => $success,
        ], now()->addHours(1));
    }
}
