<?php

namespace Modules\GroceryGermany\Helpers;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use Modules\Authentication\Entities\User;
use Modules\Authentication\Helpers\UserHelper;

use Modules\GroceryGermany\Rules\ValidBarcode;

class ExcelHelpher
{

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

            // Clean headers by replacing _x000D_ (carriage return) and convert to uppercase
            $headers = array_map(fn($header) => strtoupper(trim(str_replace("_x000D_", " ", $header))), $sheetData[0]);

            if ($section == 'dataset') {
                // Define expected headers in uppercase
                $expectedHeaders = [
                    'ITEM NAME',
                    'QUANTITY',
                    'MINIMUM STOCK ALERT',
                    'MRP',
                    'SALE PRICE',
                    'UNIT',
                    'VAT'
                ];
            }
            else{
                // Define expected headers in uppercase
                $expectedHeaders = [
                    'ITEM NAME',
                    'QUANTITY',
                    'MINIMUM STOCK ALERT',
                    'MRP',
                    'SALE PRICE',
                    'UNIT',
                    'BARCODE',
                    'VAT',
                ];
            }

            // Validate headers by comparing uppercase headers
            if (array_diff($expectedHeaders, $headers)) {
                return [
                    'status' => '4',
                    'message' => __('dataset_validation.Excel format does not match the required format'),
                    'expected_headers' => $expectedHeaders,
                    'received_headers' => $headers,
                    'code' => 403,
                ];
            }

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
                        'unit' => strtoupper(trim($rowData['UNIT'])),
                        'vat' => trim($rowData['VAT']),
                    ];
                }
                else{
                    $fields = [
                        'item_name' => $item_name,
                        'quantity' => trim($rowData['QUANTITY']),
                        'min_stock_alert' => trim($rowData['MINIMUM STOCK ALERT']),
                        'mrp' => trim($rowData['MRP']),
                        'sale_price' => trim($rowData['SALE PRICE']),
                        'unit' => strtoupper(trim($rowData['UNIT'])),
                        'barcode' => trim($rowData['BARCODE']),
                        'vat' => trim($rowData['VAT']),
                    ];
                }


                // Push the processed row into the formattedData array
                $formattedData[] = $fields;
            }

            return $formattedData;
        } catch (\Exception $e) {
            // Handle any unexpected exceptions
            report($e);
            return [
                'status' => '0',
                'message' => __('dataset_validation.An error occurred while processing the Excel file'),
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    public static function validateRow($row, $preferences, $action = 0, $existingItemNames = [], $user)
    {
        $rowErrors = [];
        $flag = 0;

        // if($user=='empty'){
        //     $table_name = TableNameHelper::getTableNameOfLoggedInUser();
        // }
        // else{
        //     $table_name = TableNameHelper::getTableName($user->user_id);
        // }

        $table_name = TableNameHelper::getTableName($user->user_id);

        foreach ($row as $key => $value) {
            switch ($key) {
                case 'item_name':

                    $cleanedItemName = strtoupper(trim(ItemHelpher::checkSpecialCharacterFromString($value)));

                    // Check for duplicates within the Excel file
                    // if (count(array_filter($existingItemNames, fn($name) => $name === $cleanedItemName)) > 1) {
                    //     $rowErrors['item_name'] = "Duplicate item name found within the Excel file.";
                    //     $flag = 1;
                    // }

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
                    if ($preferences->preference_quantity == 0 && !in_array(strtoupper($quantity), [""])) {
                        if (!is_numeric($quantity)) {
                            $rowErrors['quantity'] = __('dataset_validation.The stock preference is disabled. Quantity must be a number or empty');
                            $flag = 1;
                        }
                        if ($quantity < 0) {
                            $rowErrors['quantity'] = __('dataset_validation.Quantity cannot be negative');
                            $flag = 1;
                        }
                    } elseif ($preferences->preference_quantity == 1) {
                        if (!is_numeric($quantity) || $quantity < 0) {
                            $rowErrors['quantity'] = __('dataset_validation.Quantity must be a valid non-negative number');
                            $flag = 1;
                        }
                    }
                    if ($row['min_stock_alert'] != null && is_numeric($row['min_stock_alert']) && $row['quantity'] === null) {
                        $rowErrors['min_stock_alert'] = __('dataset_validation.The minimum stock alert cannot be accepted without specifying a stock');
                        $flag = 1;
                    }
                    break;

                case 'min_stock_alert':
                    $min_stock_alert = str_replace(',', '.', $value);
                    if (!in_array(strtoupper($min_stock_alert), [""])) {
                        if (!is_numeric($min_stock_alert)) {
                            $rowErrors['min_stock_alert'] = __('dataset_validation.Min stock alert must be a number or empty');
                            $flag = 1;
                        }
                    }
                    if (($quantity == "") && ($min_stock_alert != "")) {
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

                case 'unit':

                    $units = array_map('strtoupper', array_values(config('german_units.units')));
                    $validator = Validator::make(
                        ['unit' => strtoupper($value)],
                        ['unit' => ['required', Rule::in($units)]]
                    );

                    if ($validator->fails()) {
                        $rowErrors['unit'] = __('dataset_validation.Invalid Unit');
                        $flag = 1;
                    }
                    break;

                case 'barcode':
                    $validator = Validator::make(['barcode' => $value], ['barcode' => ['nullable', new ValidBarcode($table_name)]]);
                    if ($validator->fails()) {
                        $rowErrors['barcode'] = __('validation.Barcode must be alphanumeric and not exceed 50 characters.');
                        $flag = 1;
                    }
                    break;

                case 'vat':
                    // $rate = str_replace(',', '.', $value);
                    // if (strtoupper($rate) && (!is_numeric($rate) || $rate != 7 || $rate !=19 )) {
                    //     $rowErrors[$key] = ucfirst($key) . __("dataset_validation.must be a number between 0 and 100") . " .";
                    //     $flag = 1;
                    // }

                    $validator = Validator::make(['vat' => $value], ['vat' => ['required', Rule::in(array_column(config('german_tax.taxes'), 'value'))]]);
                    if ($validator->fails()) {
                        $rowErrors['rate1'] = __('dataset_validation.vat will either 7 or 14');
                        $flag = 1;
                    }
                    break;
            }
        }

        return ['errors' => $rowErrors, 'flag' => $flag];
    }

    public static function generateTempTableForAdmin($user_id)
    {
        $user = User::find($user_id);

        $country_details = json_decode($user->country_details);
        $region = $country_details->region;

        // dd($region);

        $module_type = $user->module_type;

        $table_name = ExcelHelpher::createTempTableName($user->user_id);

        ExcelHelpher::dropTempTable($table_name, $module_type);

        switch ($region) {
            case 'Europe':

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        item_name VARCHAR(255) NULL,
                        quantity DECIMAL(20,2) NULL,
                        min_stock_alert DECIMAL(20,2) NULL,
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

            case 'asia':

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        item_name VARCHAR(255) NULL,
                        quantity DECIMAL(20,2) NULL,
                        min_stock_alert DECIMAL(20,2) NULL,
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
                        item_name VARCHAR(255) NULL,
                        quantity DECIMAL(20,2) NULL,
                        min_stock_alert DECIMAL(20,2) NULL,
                        mrp DECIMAL(20,2) NULL,
                        sale_price DECIMAL(20,2) NULL,
                        full_unit VARCHAR(50) NULL,
                        short_unit VARCHAR(50) NULL,
                        hsn VARCHAR(50) NULL,
                        barcode VARCHAR(50) NULL,
                        tax1 VARCHAR(10) NULL,
                        rate1 DECIMAL(20,2) NULL,
                        tax2 VARCHAR(10) NULL,
                        rate2 DECIMAL(20,2) NULL,
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
        $user = User::find($user_id);


        $source = UserHelper::get_request_source();

        if (strtolower($source) == 'web') {
            $temp_table_name = 'temp_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile . "_" . $source);
        } else {
            $temp_table_name = 'temp_item_' . strtolower(str_replace(' ', '_', $user->shop->business_name) . "_" . $user->mobile);
        }
        return $temp_table_name;
    }

}


?>