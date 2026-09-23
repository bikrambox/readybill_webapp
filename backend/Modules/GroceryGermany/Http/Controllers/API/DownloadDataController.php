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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Modules\GroceryGermany\Helpers\TableNameHelper;

class DownloadDataController extends Controller
{
    // public function export()
    // {
    //     if (Auth::guard('api')->check()) {
    //         try {
    //             $user = Auth::guard('api')->user();
    //             $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();
    //             // $table_name = 'item_' . $user->username;

    //             $table_name = TableNameHelper::getTableNameOfLoggedInUser();

    //             $data = DB::table($table_name)->get()->toArray();

    //             if (count($data) <= 0) {
    //                 throw new \Exception('No Product Found');
    //             }

    //             // Define the header names mapping, excluding the id column
    //             $headerMapping = [
    //                 'id' => '', // Skip the id column
    //                 'item_name' => 'ITEM NAME',
    //                 'quantity' => 'QUANTITY',
    //                 'min_stock_alert' => 'MINIMUM STOCK ALERT',
    //                 'mrp' => 'MRP',
    //                 'sale_price' => 'SALE PRICE',
    //                 'full_unit' => '',
    //                 'short_unit' => 'UNIT',
    //                 'tax1' => '',
    //                 'rate1' => 'VAT',
    //                 'created_at' => '',
    //                 'updated_at' => '',
    //                 'tags' => '',
    //             ];

    //             // Filter out the id column from the header array
    //             $headerArray = array_filter(array_map(function ($column) use ($headerMapping) {
    //                 return $headerMapping[$column] ?: null; // Use null coalescing operator to return null for empty items
    //             }, array_keys((array) $data[0])));

    //             // Remove null values from the header array
    //             $headerArray = array_values(array_filter($headerArray));

    //             // Filter out the id column from the data array
    //             $filteredDataArray = array_map(function ($row) {
    //                 unset($row->id);
    //                 unset($row->full_unit);
    //                 unset($row->tax1);
    //                 unset($row->tags);
    //                 unset($row->created_at);
    //                 unset($row->updated_at);
    //                 return (array) $row;
    //             }, $data);

    //             // Create 'media' folder if not exists
    //             $mediaFolder = storage_path('app/public/media');
    //             if (!file_exists($mediaFolder)) {
    //                 mkdir($mediaFolder, 0777, true);
    //             }

    //             // Delete any existing file in 'media' folder
    //             $existingFile = $mediaFolder . '/exported_data.xlsx';
    //             if (file_exists($existingFile)) {
    //                 unlink($existingFile);
    //             }

    //             $spreadsheet = new Spreadsheet();
    //             $sheet = $spreadsheet->getActiveSheet();

    //             // Add headers to the sheet
    //             $lastHeaderColumnIndex = count($headerArray); // Get the index of the last header column


    //             // Merge cells for "ITEM NAME" header
    //             if (in_array('ITEM NAME', $headerArray)) {
    //                 $sheet->mergeCells('A1:D1');
    //             }

    //             // Set headers
    //             foreach ($headerArray as $key => $header) {
    //                 // Determine the cell coordinate based on the header
    //                 switch ($header) {
    //                     case 'ITEM NAME':
    //                         $cellCoordinate = chr(65 + $key) . '1';
    //                         break;
    //                     case 'QUANTITY':
    //                         $cellCoordinate = chr(68 + $key) . '1';
    //                         // Increase column width
    //                         $sheet->getColumnDimension(chr(68 + $key))->setWidth(15);
    //                         break;
    //                     case 'MINIMUM STOCK ALERT':
    //                         $cellCoordinate = chr(68 + $key) . '1';
    //                         break;
    //                     case 'MRP':
    //                     case 'SALE PRICE':
    //                     case 'UNIT':
    //                     case 'VAT':
    //                         $cellCoordinate = chr(69 + $key) . '1';
    //                     case 'TAGS':
    //                         $cellCoordinate = chr(69 + $key) . '1';
    //                         break;
    //                     default:
    //                         // Set a default cell coordinate if header doesn't match any case
    //                         $cellCoordinate = chr(65 + $key) . '1';
    //                 }

    //                 $sheet->setCellValue($cellCoordinate, $header);
    //                 $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);

    //             }

    //             // // Add data to the sheet
    //             // $sheet->fromArray($filteredDataArray, null, 'A2');


    //             // Add data to the sheet
    //             $startRow = 2; // Starting row for data
    //             foreach ($filteredDataArray as $rowData) {
    //                 $rowData = array_values($rowData); // Ensure the data array is indexed numerically
    //                 $columnIndex = 0;

    //                 // Loop through each value in the row data
    //                 foreach ($rowData as $value) {
    //                     // Determine the cell coordinate based on the header pattern
    //                     switch ($headerArray[$columnIndex]) {
    //                         case 'ITEM NAME':
    //                             $cellCoordinate = chr(65 + $columnIndex) . $startRow;
    //                             $sheet->mergeCells($cellCoordinate . ':' . chr(68 + $columnIndex) . $startRow); // Merge cells for ITEM NAME column
    //                             $sheet->setCellValue($cellCoordinate, $value); // Set cell value
    //                             break;
    //                         case 'QUANTITY':
    //                             $cellCoordinate = chr(68 + $columnIndex) . $startRow;
    //                             // Increase column width
    //                             $sheet->getColumnDimension(chr(68 + $key))->setWidth(15);
    //                             // $sheet->setCellValue($cellCoordinate, ($preferences->preference_quantity == 1) ? $value : ""); // Set cell value
    //                             $sheet->setCellValue($cellCoordinate, ($value != "" && $value != "–") ? $value : "");
    //                             break;
    //                         case 'MINIMUM STOCK ALERT':
    //                             $cellCoordinate = chr(68 + $columnIndex) . $startRow;
    //                             $sheet->mergeCells($cellCoordinate . ':' . chr(69 + $columnIndex) . $startRow);
    //                             // $sheet->setCellValue($cellCoordinate, ($preferences->preference_quantity == 1) ? $value : ""); // Set cell value
    //                             $sheet->setCellValue($cellCoordinate, ($value != "" && $value != "–") ? $value : "");
    //                             break;
    //                         case 'MRP':
    //                             // Set a default cell coordinate if header doesn't match any case
    //                             $cellCoordinate = chr(69 + $columnIndex) . $startRow;
    //                             // $sheet->setCellValue($cellCoordinate, ($preferences->preference_mrp == 1) ? $value : ""); // Set cell value
    //                             $sheet->setCellValue($cellCoordinate, ($value != "" && $value != "–") ? $value : "");
    //                             break;
    //                         case 'SALE PRICE':
    //                             // Set a default cell coordinate if header doesn't match any case
    //                             $cellCoordinate = chr(69 + $columnIndex) . $startRow;
    //                             $sheet->setCellValue($cellCoordinate, $value); // Set cell value
    //                             break;
    //                         case 'UNIT':
    //                             // Set a default cell coordinate if header doesn't match any case
    //                             $cellCoordinate = chr(69 + $columnIndex) . $startRow;
    //                             $sheet->setCellValue($cellCoordinate, $value); // Set cell value
    //                             break;
    //                         case 'VAT':
    //                             $cellCoordinate = chr(69 + $columnIndex) . $startRow;
    //                             $sheet->setCellValue($cellCoordinate, $value); // Set cell value
    //                             break;
    //                         case 'TAGS':
    //                             $cellCoordinate = chr(69 + $columnIndex) . $startRow;
    //                             $sheet->setCellValue($cellCoordinate, $value); // Set cell value
    //                             break;
    //                         // default:
    //                         // Set a default cell coordinate if header doesn't match any case
    //                         // $cellCoordinate = chr(65 + $columnIndex) . $startRow;
    //                     }

    //                     // $sheet->setCellValue($cellCoordinate, $value); // Set cell value
    //                     $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT); // Align text to the left
    //                     $columnIndex++; // Move to the next column
    //                 }

    //                 $startRow++; // Move to the next row
    //             }


    //             // Save the spreadsheet to a file in 'media' folder
    //             $writer = new Xlsx($spreadsheet);
    //             $filename = 'exported_data.xlsx';
    //             $directory = 'media';
    //             $filePath = $directory . '/' . $filename;

    //             // Check if the directory exists; if not, create it
    //             if (!is_dir('storage/' . $directory)) {
    //                 mkdir('storage/' . $directory, 0755, true); // Create directory with appropriate permissions
    //             }

    //             // Save the file
    //             $writer->save('storage/' . $filePath);

    //             return response()->json(['status' => '1', 'message' => 'Export successful', 'file' => $filePath]);
    //         } catch (\Exception $e) {
    //             // Handle any exceptions here
    //             return response()->json(['status' => '0', 'message' => $e->getMessage()], 200);
    //         }
    //     }

    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => __('validation.Unauthorized')
    //     ], 401);
    // }

    public function export()
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['status' => 'failed', 'message' => __('validation.Unauthorized')], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            $table_name = TableNameHelper::getTableNameOfLoggedInUser();
            $data = DB::table($table_name)->get()->toArray();

            if (count($data) <= 0) {
                throw new \Exception('No Product Found');
            }

            // Explicit column map: field => [label, column letter, merge_end (optional)]
            $columnMap = [
                'item_name' => ['label' => 'ITEM NAME', 'col' => 'A', 'merge_end' => 'D'],
                'quantity' => ['label' => 'QUANTITY', 'col' => 'E'],
                'min_stock_alert' => ['label' => 'MINIMUM STOCK ALERT', 'col' => 'F', 'merge_end' => 'G'],
                'mrp' => ['label' => 'MRP', 'col' => 'H'],
                'sale_price' => ['label' => 'SALE PRICE', 'col' => 'I'],
                'short_unit' => ['label' => 'UNIT', 'col' => 'J'],
                'barcode' => ['label' => 'BARCODE', 'col' => 'K'],
                'rate1' => ['label' => 'VAT', 'col' => 'L'],
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
            $filename = 'exported_data.xlsx';
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


    public function downloadPreDataset()
    {
        try {

            // Check API authentication
            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Unauthorized')
                ], 401);
            }

            // Set the file path
            $filePath = 'dataset/germany_sample_dataset.xlsx';
            $actualPath = 'storage/dataset/sample_dataset.xlsx';

            // Check if file exists
            if (!File::exists($actualPath)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.File not found')
                ], 404);
            }

            return response()->json([
                'status' => '1',
                'message' => __('validation.Export successful'),
                'file' => '/' . $filePath
            ]);

        } catch (\Exception $e) {
            Log::error('Download Dataset failed', [
                'status' => '0',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            report($e);
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.unable_to_process')
            ], 500);
        }
    }
}
