<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\GroceryIndia\Helpers\TableNameHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadDataController extends Controller
{

    // public function export()
    // {
    //     if (!Auth::guard('api')->check()) {
    //         return response()->json(['status' => 'failed', 'message' => __('validation.Unauthorized')], 401);
    //     }

    //     try {
    //         $user = Auth::guard('api')->user();
    //         $table_name = TableNameHelper::getTableNameOfLoggedInUser();
    //         $data = DB::table($table_name)
    //             ->leftJoin('categories', 'categories.id', '=', $table_name . '.category_id')
    //             ->select(
    //                 $table_name . '.*',
    //                 DB::raw("COALESCE(categories.name, 'NA') as category_name"),
    //                 DB::raw("COALESCE({$table_name}.sku, 'NA') as sku")
    //             )
    //             ->get()
    //             ->toArray();

    //         if (count($data) <= 0) {
    //             throw new \Exception('No Product Found');
    //         }

    //         // Explicit column map: field => [label, column letter, merge_end (optional)]
    //         $columnMap = [
    //             'item_name' => ['label' => 'ITEM NAME', 'col' => 'A', 'merge_end' => 'D'],
    //             'sku' => ['label' => 'SKU', 'col' => 'E', 'merge_end' => 'F'],
    //             'category_name' => ['label' => 'CATEGORY', 'col' => 'G', 'merge_end' => 'H'],
    //             'quantity' => ['label' => 'QUANTITY', 'col' => 'I'],
    //             'min_stock_alert' => ['label' => 'MINIMUM STOCK ALERT', 'col' => 'J', 'merge_end' => 'K'],
    //             'mrp' => ['label' => 'MRP', 'col' => 'L'],
    //             'sale_price' => ['label' => 'SALE PRICE', 'col' => 'M'],
    //             'purchase_price' => ['label' => 'PURCHASE PRICE', 'col' => 'N'],
    //             'short_unit' => ['label' => 'UNIT', 'col' => 'O'],
    //             'hsn' => ['label' => 'HSN', 'col' => 'P'],
    //             'barcode' => ['label' => 'BARCODE', 'col' => 'Q'],
    //             'rate1' => ['label' => 'GST', 'col' => 'R'],
    //             'rate2' => ['label' => 'CESS', 'col' => 'S'],
    //         ];

    //         // Fields to skip entirely
    //         $skipFields = ['id', 'full_unit', 'tax1', 'tax2', 'tags', 'created_at', 'updated_at'];

    //         $spreadsheet = new Spreadsheet();
    //         $sheet = $spreadsheet->getActiveSheet();

    //         // --- Write headers ---
    //         foreach ($columnMap as $field => $config) {
    //             $col = $config['col'];
    //             $cell = $col . '1';

    //             if (isset($config['merge_end'])) {
    //                 $sheet->mergeCells($col . '1:' . $config['merge_end'] . '1');
    //             }

    //             $sheet->setCellValue($cell, $config['label']);
    //             $sheet->getStyle($cell)->getFont()->setBold(true);
    //             $sheet->getColumnDimension($col)->setWidth(18);
    //         }

    //         // --- Write data rows ---
    //         $startRow = 2;
    //         foreach ($data as $row) {
    //             $row = (array) $row;

    //             foreach ($columnMap as $field => $config) {
    //                 $col = $config['col'];
    //                 $cell = $col . $startRow;
    //                 $value = $row[$field] ?? '';

    //                 // Normalize empty/dash values
    //                 $cleanValue = ($value !== '' && $value !== '–') ? $value : '';

    //                 if (isset($config['merge_end'])) {
    //                     $sheet->mergeCells($col . $startRow . ':' . $config['merge_end'] . $startRow);
    //                 }

    //                 $sheet->setCellValue($cell, $cleanValue);
    //                 $sheet->getStyle($cell)->getAlignment()
    //                     ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    //             }

    //             $startRow++;
    //         }

    //         // Save the spreadsheet to a file in 'media' folder
    //         $writer = new Xlsx($spreadsheet);
    //         $filename = 'exported_data.xlsx';
    //         $directory = 'media';
    //         $filePath = $directory . '/' . $filename;

    //         // Check if the directory exists; if not, create it
    //         if (!is_dir('storage/' . $directory)) {
    //             mkdir('storage/' . $directory, 0777, true); // Create directory with appropriate permissions
    //         }

    //         // Save the file
    //         $writer->save('storage/' . $filePath);

    //         return response()->json(['status' => '1', 'message' => 'Export successful', 'file' => $filePath]);

    //     } catch (\Exception $e) {
    //         report($e);
    //         return response()->json(
    //             [
    //                 'status' => '0',
    //                 'message' => __('validation.unable_to_process')
    //             ],
    //             200
    //         );
    //     }
    // }


    public function export()
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => '0',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $data = DB::table($table_name)
                ->leftJoin('categories', 'categories.id', '=', $table_name . '.category_id')
                ->select(
                    $table_name . '.*',
                    DB::raw("COALESCE(categories.name, 'NA') as category_name"),
                    DB::raw("COALESCE({$table_name}.sku, 'NA') as sku")
                )
                ->get()
                ->toArray();

            if (count($data) <= 0) {
                return response()->json([
                    'status' => '0',
                    'message' => 'No Product Found'
                ], 404);
            }

            $columnMap = [
                'item_name' => ['label' => 'ITEM NAME', 'col' => 'A', 'merge_end' => 'D'],
                'sku' => ['label' => 'SKU', 'col' => 'E', 'merge_end' => 'F'],
                'category_name' => ['label' => 'CATEGORY', 'col' => 'G', 'merge_end' => 'H'],
                'quantity' => ['label' => 'QUANTITY', 'col' => 'I'],
                'min_stock_alert' => ['label' => 'MINIMUM STOCK ALERT', 'col' => 'J', 'merge_end' => 'K'],
                'mrp' => ['label' => 'MRP', 'col' => 'L'],
                'sale_price' => ['label' => 'SALE PRICE', 'col' => 'M'],
                'purchase_price' => ['label' => 'PURCHASE PRICE', 'col' => 'N'],
                'short_unit' => ['label' => 'UNIT', 'col' => 'O'],
                'hsn' => ['label' => 'HSN', 'col' => 'P'],
                'barcode' => ['label' => 'BARCODE', 'col' => 'Q'],
                'rate1' => ['label' => 'GST', 'col' => 'R'],
                'rate2' => ['label' => 'CESS', 'col' => 'S'],
            ];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

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

            $startRow = 2;
            foreach ($data as $row) {
                $row = (array) $row;

                foreach ($columnMap as $field => $config) {
                    $col = $config['col'];
                    $cell = $col . $startRow;
                    $value = $row[$field] ?? '';
                    $cleanValue = ($value !== '' && $value !== '–') ? $value : '';

                    if (isset($config['merge_end'])) {
                        $sheet->mergeCells($col . $startRow . ':' . $config['merge_end'] . $startRow);
                    }

                    $sheet->setCellValue($cell, $cleanValue);
                }

                $startRow++;
            }

            $directory = 'exports/' . $user->id;
            $filename = 'items_' . now()->format('Ymd_His') . '_' . Str::random(12) . '.xlsx';

            Storage::disk('local')->makeDirectory($directory);

            $relativePath = $directory . '/' . $filename;
            $absolutePath = Storage::disk('local')->path($relativePath);

            $writer = new Xlsx($spreadsheet);
            $writer->save($absolutePath);

            return response()->download(
                $absolutePath,
                'products_export.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                    'X-Content-Type-Options' => 'nosniff',
                ]
            )->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => '0',
                'message' => __('validation.unable_to_process')
            ], 500);
        }
    }


    // public function downloadPreDataset()
    // {
    //     try {

    //         // Check API authentication
    //         if (!Auth::guard('api')->check()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => __('validation.Unauthorized')
    //             ], 401);
    //         }

    //         // Set the file path
    //         $filePath = 'dataset/sample_dataset.xlsx';
    //         $actualPath = 'storage/dataset/sample_dataset.xlsx';


    //         // Check if file exists
    //         if (!File::exists($actualPath)) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => __('validation.File not found')
    //             ], 404);
    //         }

    //         return response()->json([
    //             'status' => '1',
    //             'message' => __('validation.Export successful'),
    //             'file' => '/' . $filePath
    //         ]);

    //     } catch (\Exception $e) {
    //         // Log::error('Download Dataset failed', [
    //         //     'status' => '0',
    //         //     'error' => $e->getMessage(),
    //         //     'trace' => $e->getTraceAsString()
    //         // ]);

    //         report($e);

    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.unable_to_process'),
    //         ], 500);
    //     }
    // }

    // public function downloadPreDataset()
    // {
    //     try {
    //         if (!Auth::guard('api')->check()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => __('validation.Unauthorized')
    //             ], 401);
    //         }

    //         $disk = 'local';
    //         $filePath = 'dataset/sample_dataset.xlsx';

    //         if (!Storage::disk($disk)->exists($filePath)) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => __('validation.File not found')
    //             ], 404);
    //         }

    //         return Storage::disk($disk)->download(
    //             $filePath,
    //             'sample_dataset.xlsx',
    //             [
    //                 'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    //                 'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    //                 'Pragma' => 'no-cache',
    //                 'Expires' => '0',
    //                 'X-Content-Type-Options' => 'nosniff',
    //             ]
    //         );
    //     } catch (\Throwable $e) {
    //         report($e);

    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.unable_to_process'),
    //         ], 500);
    //     }
    // }

    public function downloadPreDataset()
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => '0',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            // $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            $dataset_table_name = 'dataset_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);


            $data = DB::table($dataset_table_name)
                ->leftJoin('categories', 'categories.id', '=', $dataset_table_name . '.category_id')
                ->select(
                    $dataset_table_name . '.*',
                    DB::raw("COALESCE(categories.name, 'NA') as category_name"),
                    DB::raw("COALESCE({$dataset_table_name}.sku, 'NA') as sku")
                )
                ->get()
                ->toArray();

            if (count($data) <= 0) {
                return response()->json([
                    'status' => '0',
                    'message' => 'No Product Found'
                ], 404);
            }

            $columnMap = [
                'item_name' => ['label' => 'ITEM NAME', 'col' => 'A', 'merge_end' => 'D'],
                'sku' => ['label' => 'SKU', 'col' => 'E', 'merge_end' => 'F'],
                'category_name' => ['label' => 'CATEGORY', 'col' => 'G', 'merge_end' => 'H'],
                'quantity' => ['label' => 'QUANTITY', 'col' => 'I'],
                'min_stock_alert' => ['label' => 'MINIMUM STOCK ALERT', 'col' => 'J', 'merge_end' => 'K'],
                'mrp' => ['label' => 'MRP', 'col' => 'L'],
                'sale_price' => ['label' => 'SALE PRICE', 'col' => 'M'],
                'purchase_price' => ['label' => 'PURCHASE PRICE', 'col' => 'N'],
                'short_unit' => ['label' => 'UNIT', 'col' => 'O'],
                'hsn' => ['label' => 'HSN', 'col' => 'P'],
                'barcode' => ['label' => 'BARCODE', 'col' => 'Q'],
                'rate1' => ['label' => 'GST', 'col' => 'R'],
                'rate2' => ['label' => 'CESS', 'col' => 'S'],
            ];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

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

            $startRow = 2;
            foreach ($data as $row) {
                $row = (array) $row;

                foreach ($columnMap as $field => $config) {
                    $col = $config['col'];
                    $cell = $col . $startRow;
                    $value = $row[$field] ?? '';
                    $cleanValue = ($value !== '' && $value !== '–') ? $value : '';

                    if (isset($config['merge_end'])) {
                        $sheet->mergeCells($col . $startRow . ':' . $config['merge_end'] . $startRow);
                    }

                    $sheet->setCellValue($cell, $cleanValue);
                }

                $startRow++;
            }

            $directory = 'exports/' . $user->id;
            $filename = 'items_' . now()->format('Ymd_His') . '_' . Str::random(12) . '.xlsx';

            Storage::disk('local')->makeDirectory($directory);

            $relativePath = $directory . '/' . $filename;
            $absolutePath = Storage::disk('local')->path($relativePath);

            $writer = new Xlsx($spreadsheet);
            $writer->save($absolutePath);

            return response()->download(
                $absolutePath,
                'products_export.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                    'X-Content-Type-Options' => 'nosniff',
                ]
            )->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => '0',
                'message' => __('validation.unable_to_process')
            ], 500);
        }
    }

}
