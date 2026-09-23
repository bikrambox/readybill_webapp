<?php

namespace Modules\GroceryIndia\Helpers;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use Modules\Authentication\Entities\User;

use Modules\GroceryIndia\Rules\HsnCode;
use Modules\GroceryIndia\Rules\ValidBarcode;
use Modules\Core\Rules\NoSpecialCharacter;
use Modules\Core\Rules\NoScriptTag;

use Modules\Authentication\Helpers\UserHelper;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;


class ExcelHelpher
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

    public static function getColumnsMap()
    {
        return self::COLUMN_MAP;
    }

    public static function checkHeader($section = 'upload-data', $headers = [])
    {
        if ($section === 'dataset') {
            $expectedHeaders = [
                'ITEM NAME',
                'QUANTITY',
                'MINIMUM STOCK ALERT',
                'MRP',
                'SALE PRICE',
                'PURCHASE PRICE',
                'UNIT',
                'HSN',
                'GST',
                'CESS'
            ];
        } else {
            $expectedHeaders = [
                'ITEM NAME',
                'QUANTITY',
                'MINIMUM STOCK ALERT',
                'MRP',
                'SALE PRICE',
                'PURCHASE PRICE',
                'UNIT',
                'HSN',
                'BARCODE',
                'GST',
                'CESS'
            ];
        }

        $normalizedHeaders = array_values(array_map(function ($header) {
            return strtoupper(trim((string) $header));
        }, $headers));

        if ($expectedHeaders !== $normalizedHeaders) {
            return [
                'status' => '4',
                'message' => __('dataset_validation.Excel format does not match the required format'),
                'expected_headers' => $expectedHeaders,
                'received_headers' => $normalizedHeaders,
                'code' => 422,
            ];
        }

        return null;
    }

    public static function parseExcel($file, $section = 'upload-data')
    {
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            if (empty($sheetData) || !isset($sheetData[0])) {
                return [
                    'status' => '0',
                    'message' => __('dataset_validation.Empty dataset'),
                    'code' => 400,
                ];
            }

            // // Clean headers by replacing _x000D_ (carriage return) and convert to uppercase
            $headers = array_map(fn($header) => strtoupper(trim(str_replace("_x000D_", " ", $header))), $sheetData[0]);

            // if ($section == 'dataset') {
            //     // Define expected headers in uppercase
            //     $expectedHeaders = [
            //         'ITEM NAME',
            //         'QUANTITY',
            //         'MINIMUM STOCK ALERT',
            //         'MRP',
            //         'SALE PRICE',
            //         'PURCHASE PRICE',
            //         'UNIT',
            //         'HSN',
            //         'GST',
            //         'CESS'
            //     ];
            // } else {
            //     // Define expected headers in uppercase
            //     $expectedHeaders = [
            //         'ITEM NAME',
            //         'QUANTITY',
            //         'MINIMUM STOCK ALERT',
            //         'MRP',
            //         'SALE PRICE',
            //         'PURCHASE PRICE',
            //         'UNIT',
            //         'HSN',
            //         'BARCODE',
            //         'GST',
            //         'CESS'
            //     ];
            // }


            // // Validate headers by comparing uppercase headers
            // if (array_diff($expectedHeaders, $headers)) {
            //     return [
            //         'status' => '4',
            //         'message' => __('dataset_validation.Excel format does not match the required format'),
            //         'expected_headers' => $expectedHeaders,
            //         'received_headers' => $headers,
            //         'code' => 403,
            //     ];
            // }

            
            self::checkHeader($section, $headers);

            // Extract the data (skip the first row which is the header)
            $data = array_slice($sheetData, 1);

            // Process the data and assign proper keys while removing empty rows
            $formattedData = [];
            foreach ($data as $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Clean each cell by replacing _x000D_ (carriage return)
                $row = array_map(fn($cell) => str_replace("_x000D_", " ", $cell), $row);

                // Combine the row with the headers
                $rowData = array_combine($headers, $row);

                // Process each field and sanitize accordingly
                $item_name = ItemHelpher::checkSpecialCharacterFromString(strtoupper(trim($rowData['ITEM NAME'])));

                if ($section == 'dataset') {
                    $fields = [
                        'item_name' => $item_name,
                        'quantity' => trim($rowData['QUANTITY']),
                        'min_stock_alert' => trim($rowData['MINIMUM STOCK ALERT']),
                        'mrp' => trim($rowData['MRP']),
                        'sale_price' => trim($rowData['SALE PRICE']),
                        'purchase_price' => trim($rowData['PURCHASE PRICE']),
                        'hsn' => trim($rowData['HSN']),
                        'unit' => strtoupper(trim($rowData['UNIT'])),
                        'gst' => trim($rowData['GST']),
                        'cess' => trim($rowData['CESS']),
                    ];
                } else {
                    $fields = [
                        'item_name' => $item_name,
                        'quantity' => trim($rowData['QUANTITY']),
                        'min_stock_alert' => trim($rowData['MINIMUM STOCK ALERT']),
                        'mrp' => trim($rowData['MRP']),
                        'sale_price' => trim($rowData['SALE PRICE']),
                        'purchase_price' => trim($rowData['PURCHASE PRICE']),
                        'hsn' => trim($rowData['HSN']),
                        'barcode' => trim($rowData['BARCODE']),
                        'unit' => strtoupper(trim($rowData['UNIT'])),
                        'gst' => trim($rowData['GST']),
                        'cess' => trim($rowData['CESS']),
                    ];
                }

                // Push the processed row into the formattedData array
                $formattedData[] = $fields;
            }

            return $formattedData;
        } catch (\Exception $e) {

            report($e);
            // Handle any unexpected exceptions
            return [
                'status' => '0',
                'message' => __('dataset_validation.An error occurred while processing the Excel file'),
                'error' => __('validation.unable_to_process'),
                'code' => 500,
            ];
        }
    }

    public static function validateRow($row, $preferences, $action = 0, $existingItemNames = [], $user)
    {
        $rowErrors = [];
        $flag = 0;

        $table_name = TableNameHelper::getTableName($user->user_id);

        foreach ($row as $key => $value) {
            switch ($key) {
                case 'item_name':

                    $cleanedItemName = strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($value)));


                    // Convert item names to uppercase and check for duplicates within the Excel file
                    if (count(array_filter($existingItemNames, fn($name) => strtoupper($name) === strtoupper($cleanedItemName))) > 1) {
                        $rowErrors['item_name'] = __('dataset_validation.Duplicate item name found within the Excel file');
                        $flag = 1;
                    }

                    // Existing database check (unchanged)
                    if ($action != 2) {
                        $exists = DB::connection($user->module_type)->table($table_name)
                            ->whereRaw('UPPER(item_name) = ?', [$cleanedItemName])
                            ->exists();
                        if ($exists) {
                            $rowErrors['item_name'] = __('dataset_validation.Item(s) is already present in your inventory');
                            $flag = 1;
                        }
                    }
                    break;

                case 'quantity':
                    $quantity = str_replace(',', '.', $value);
                    $quantityEmpty = ($quantity === '' || $quantity === null);

                    if ($preferences->preference_quantity == 0) {
                        if (!$quantityEmpty) {
                            if (!is_numeric($quantity)) {
                                $rowErrors['quantity'] = __('dataset_validation.The stock preference is disabled. Quantity must be a number or empty');
                                $flag = 1;
                            } elseif ((float) $quantity < 0) {
                                $rowErrors['quantity'] = __('dataset_validation.Quantity cannot be negative');
                                $flag = 1;
                            } elseif (fmod((float) $quantity, 1.0) !== 0.0) {
                                $rowErrors['quantity'] = __('dataset_validation.Quantity must be a whole number');
                                $flag = 1;
                            }
                        }
                    } elseif ($preferences->preference_quantity == 1) {
                        if ($quantityEmpty) {
                            $rowErrors['quantity'] = __('dataset_validation.Quantity must be a valid non-negative number');
                            $flag = 1;
                        } elseif (!is_numeric($quantity)) {
                            $rowErrors['quantity'] = __('dataset_validation.Quantity must be a valid non-negative number');
                            $flag = 1;
                        } elseif ((float) $quantity < 0) {
                            $rowErrors['quantity'] = __('dataset_validation.Quantity cannot be negative');
                            $flag = 1;
                        } elseif (fmod((float) $quantity, 1.0) !== 0.0) {
                            $rowErrors['quantity'] = __('dataset_validation.Quantity must be a whole number');
                            $flag = 1;
                        }
                    }

                    if (!empty($row['min_stock_alert']) && is_numeric($row['min_stock_alert']) && $quantityEmpty) {
                        $rowErrors['min_stock_alert'] = __('dataset_validation.The minimum stock alert cannot be accepted without specifying a stock');
                        $flag = 1;
                    }
                    break;


                case 'min_stock_alert':
                    $min_stock_alert = str_replace(',', '.', $value);
                    $alertEmpty = ($min_stock_alert === '' || $min_stock_alert === null);

                    if (!$alertEmpty) {
                        if (!is_numeric($min_stock_alert)) {
                            $rowErrors['min_stock_alert'] = __('dataset_validation.Min stock alert must be a number or empty');
                            $flag = 1;
                        } elseif ((float) $min_stock_alert < 0) {
                            $rowErrors['min_stock_alert'] = __('dataset_validation.Min stock alert cannot be negative');
                            $flag = 1;
                        } elseif (fmod((float) $min_stock_alert, 1.0) !== 0.0) {
                            $rowErrors['min_stock_alert'] = __('dataset_validation.Min stock alert must be a whole number');
                            $flag = 1;
                        }
                    }

                    if (!$alertEmpty && $quantityEmpty) {
                        $rowErrors['min_stock_alert'] = __('dataset_validation.The minimum stock alert cannot be accepted without specifying a stock');
                        $flag = 1;
                    }
                    break;

         

                case 'mrp':
                    $mrp = str_replace(',', '.', $value);

                    if ($preferences->preference_mrp == 0 && !in_array(strtoupper($mrp), [""])) {
                        if (!is_numeric($mrp) || $mrp < 0) {
                            $rowErrors['mrp'] = __('dataset_validation.MRP must be a valid non-negative number');
                            $flag = 1;
                        }
                    } elseif ($preferences->preference_mrp == 1) {
                        if (!is_numeric($mrp) || $mrp < 0) {
                            $rowErrors['mrp'] = __('dataset_validation.MRP must be a valid non-negative number');
                            $flag = 1;
                        }
                    }
                    break;

                case 'sale_price':
                    $sale_price = str_replace(',', '.', $value);
                    if (!is_numeric($sale_price) || $sale_price <= 0) {
                        $rowErrors['sale_price'] = __('dataset_validation.Sale price must be a valid non-negative number');
                        $flag = 1;
                    }

                    if (is_numeric($sale_price) && $sale_price > $mrp) {
                        $rowErrors['sale_price'] = __('dataset_validation.Sale price cannot be greater than MRP');
                        $flag = 1;
                    }

                    break;

                case 'purchase_price':
                    $purchase_price = str_replace(',', '.', $value);

                    if ($preferences->preference_purchase_price == 0 && !in_array(strtoupper($purchase_price), [""])) {
                        if (!is_numeric($purchase_price) || $purchase_price < 0) {
                            $rowErrors['purchase_price'] = __('dataset_validation.Purchase Price must be a valid non-negative number');
                            $flag = 1;
                        }
                    } elseif ($preferences->preference_purchase_price == 1) {
                        if (!is_numeric($purchase_price) || $purchase_price < 0) {
                            $rowErrors['purchase_price'] = __('dataset_validation.Purchase Price must be a valid non-negative number');
                            $flag = 1;
                        }
                    }
                    break;

                case 'hsn':
                    $validator = Validator::make(['hsn' => $value], ['hsn' => ['nullable', new HsnCode($table_name)]]);
                    if ($validator->fails()) {
                        $rowErrors['hsn'] = __('dataset_validation.hsn_code_numeric_between_digit_cannot_be_zero_validation');
                        $flag = 1;
                    }
                    break;

                case 'barcode':
                    $validator = Validator::make(['barcode' => $value], ['barcode' => ['nullable', new ValidBarcode($table_name, null, $user->module_type)], new NoScriptTag]);
                    if ($validator->fails()) {
                        $rowErrors['barcode'] = __('validation.Barcode must be alphanumeric and not exceed 50 characters.');
                        $flag = 1;
                    }
                    break;

                case 'unit':
                    $validator = Validator::make(['unit' => $value], ['unit' => ['required', Rule::in(array_values(config('india_units.units')))]]);
                    if ($validator->fails()) {
                        $rowErrors['unit'] = __('dataset_validation.Invalid Unit');
                        $flag = 1;
                    }
                    break;

                case 'gst':
                case 'cess':
                    $rate = str_replace(',', '.', $value);
                    if (strtoupper($rate) && (!is_numeric($rate) || $rate < 0 || $rate >= 100)) {
                        $rowErrors[$key] = ucfirst($key) . __("dataset_validation.must be a number between 0 and 100") . " .";
                        $flag = 1;
                    }
                    break;
            }
        }

        return ['errors' => $rowErrors, 'flag' => $flag];
    }

    public static function generateTempTableForAdmin($user_id, $table_name='')
    {
        $user = User::find($user_id);

        $country_details = json_decode($user->country_details);
        $region = $user->region;

        $module_type = $user->module_type;

        $table_name = !empty($table_name) ? $table_name : ExcelHelpher::createTempTableName($user->user_id);

        ExcelHelpher::dropTempTable($table_name, $module_type);

        switch ($region) {
            case 'Europe':

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        sku VARCHAR(50) UNIQUE NULL,
                        category_id BIGINT UNSIGNED NULL,
                        item_name VARCHAR(255) NULL,
                        quantity DECIMAL(20,2) NULL,
                        min_stock_alert DECIMAL(20,2) NULL,
                        purchase_price DECIMAL(20,2) NOT NULL, 
                        mrp DECIMAL(20,2) NULL,
                        sale_price DECIMAL(20,2) NULL,
                        full_unit VARCHAR(50) NULL,
                        short_unit VARCHAR(50) NULL,
                        barcode VARCHAR(50) NULL,
                        tax1 VARCHAR(10) NULL,
                        rate1 DECIMAL(5,2) NULL,
                        tags LONGTEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                break;

            case 'india':

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        sku VARCHAR(50) UNIQUE NULL,
                        category_id BIGINT UNSIGNED NULL,
                        item_name VARCHAR(255) NULL,
                        quantity DECIMAL(20,2) NULL,
                        min_stock_alert DECIMAL(20,2) NULL,
                        purchase_price DECIMAL(20,2) NOT NULL, 
                        mrp DECIMAL(20,2) NULL,
                        sale_price DECIMAL(20,2) NULL,
                        full_unit VARCHAR(50) NULL,
                        short_unit VARCHAR(50) NULL,
                        hsn VARCHAR(50) NULL,
                        barcode VARCHAR(50) NULL,
                        tax1 VARCHAR(10) NULL,
                        rate1 DECIMAL(5,2) NULL,
                        tax2 VARCHAR(10) NULL,
                        rate2 DECIMAL(5,2) NULL,
                        tags LONGTEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                break;

            case 'uae':
                break;

            default:

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        sku VARCHAR(50) UNIQUE NULL,
                        category_id BIGINT UNSIGNED NULL,
                        item_name VARCHAR(255) NULL,
                        quantity DECIMAL(20,2) NULL,
                        min_stock_alert DECIMAL(20,2) NULL,
                        purchase_price DECIMAL(20,2) NOT NULL, 
                        mrp DECIMAL(20,2) NULL,
                        sale_price DECIMAL(20,2) NULL,
                        full_unit VARCHAR(50) NULL,
                        short_unit VARCHAR(50) NULL,
                        hsn VARCHAR(50) NULL,
                        barcode VARCHAR(50) NULL,
                        tax1 VARCHAR(10) NULL,
                        rate1 DECIMAL(5,2) NULL,
                        tax2 VARCHAR(10) NULL,
                        rate2 DECIMAL(5,2) NULL,
                        tags LONGTEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                break;
        }
    }

    public static function dropTempTable($table_name, $module_type)
    {
        DB::connection($module_type)->statement("DROP TABLE IF EXISTS {$table_name}");
    }

    public static function createTempTableName($user_id)
    {
        // $user = User::find($user_id);


        // $source = UserHelper::get_request_source();

        // // dd($source);

        // if (strtolower($source) == 'web') {
        //     $temp_table_name = 'temp_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile . "_" . $source);
        // } else {
        //     $temp_table_name = 'temp_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
        // }
        // return $temp_table_name;


        $user = User::find($user_id);
        $temp_table_name = 'temp_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile . "_" . "web");
        return $temp_table_name;
    }

    public static function app_createTempTableName($user_id)
    {
        $user = User::find($user_id);
        $temp_table_name = 'temp_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
        return $temp_table_name;
    }


    /**
     * Delete any previously uploaded Excel temp file for a given user.
     * Also clears the associated cache progress and result keys.
     */
    public static function deletePreviousUpload(int|string $userId, ?string $previousJobId = null): void
    {
        // If a specific job_id is passed, delete that file and its cache
        if ($previousJobId) {
            self::deleteByJobId($userId, $previousJobId);
            return;
        }

        // Otherwise, scan temp storage for any excel files belonging to this user
        $files = Storage::files('temp');

        foreach ($files as $file) {
            if (str_starts_with(basename($file), "excel_{$userId}_")) {
                Storage::delete($file);
            }
        }
    }

    /**
     * Delete a specific Excel upload file and its associated cache entries.
     */
    public static function deleteByJobId(int|string $userId, string $jobId): void
    {
        // Try both .xlsx and .csv extensions
        foreach (['xlsx', 'csv'] as $ext) {
            $path = "temp/excel_{$userId}_{$jobId}.{$ext}";
            if (Storage::exists($path)) {
                Storage::delete($path);
                break;
            }
        }

        // Clear progress and result cache
        Cache::forget("excel_upload_progress_{$userId}_{$jobId}");
        Cache::forget("excel_upload_result_{$userId}_{$jobId}");
    }

}


?>