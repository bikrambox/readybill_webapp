<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Helpers\TableNameHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\GroceryIndia\Helpers\InventoryHelpher;

class ScriptController extends Controller
{
    public function addBarcode()
    {
        $users = User::where('isAdmin', 1)->get();

        foreach ($users as $user) {

            $table_name = TableNameHelper::getTableName($user->user_id);

            // connection name from module_type
            $connection = $user->module_type;

            if (
                Schema::connection($connection)->hasTable($table_name) &&
                !Schema::connection($connection)->hasColumn($table_name, 'barcode')
            ) {

                Schema::connection($connection)->table($table_name, function (Blueprint $table) {
                    $table->string('barcode', 50)->nullable()->after('tags');
                });

            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Barcode column added successfully'
        ]);
    }

    // ################################################### ADD PURCHASE PRICE FIELD IN SHOP USER INVENTORY TBALE ###################################################
    // public function addPurchasePrice()
    // {
    //     try {

    //         $users = User::where('isAdmin', 1)->get();

    //         foreach ($users as $user) {

    //             try {

    //                 $table_name = TableNameHelper::getTableName($user->user_id);

    //                 // connection name from module_type
    //                 $connection = $user->module_type;

    //                 Log::debug('Checking table for purchase_price column', [
    //                     'user_id' => $user->user_id,
    //                     'connection' => $connection,
    //                     'table_name' => $table_name
    //                 ]);

    //                 if (
    //                     Schema::connection($connection)->hasTable($table_name) &&
    //                     !Schema::connection($connection)->hasColumn($table_name, 'purchase_price')
    //                 ) {

    //                     Schema::connection($connection)->table($table_name, function (Blueprint $table) {
    //                         $table->decimal('purchase_price', 20, 2)
    //                         ->default(0)
    //                             ->after('min_stock_alert');
    //                     });

    //                     Log::debug('purchase_price column added successfully', [
    //                         'table_name' => $table_name,
    //                         'connection' => $connection
    //                     ]);

    //                 } else {

    //                     Log::debug('Table not found or column already exists', [
    //                         'table_name' => $table_name,
    //                         'connection' => $connection
    //                     ]);

    //                 }

    //             } catch (\Exception $e) {

    //                 Log::error('Error while updating table', [
    //                     'user_id' => $user->user_id,
    //                     'table_name' => $table_name ?? '',
    //                     'connection' => $connection ?? '',
    //                     'message' => $e->getMessage(),
    //                     'line' => $e->getLine(),
    //                     'file' => $e->getFile()
    //                 ]);

    //             }
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Purchase price column added successfully'
    //         ]);

    //     } catch (\Exception $e) {

    //         Log::error('addPurchasePrice function error', [
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile()
    //         ]);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong'
    //         ], 500);
    //     }
    // }

    public function addPurchasePrice()
    {
        try {

            $users = User::where('isAdmin', 1)->get();

            foreach ($users as $user) {

                try {

                    $table_name = TableNameHelper::getTableName($user->user_id);

                    // connection name from module_type
                    $connection = $user->module_type;

                    Log::debug('Checking table for purchase_price column', [
                        'user_id' => $user->user_id,
                        'connection' => $connection,
                        'table_name' => $table_name
                    ]);

                    if (Schema::connection($connection)->hasTable($table_name)) {

                        // first drop column if exists
                        if (Schema::connection($connection)->hasColumn($table_name, 'purchase_price')) {

                            Schema::connection($connection)->table($table_name, function (Blueprint $table) {
                                $table->dropColumn('purchase_price');
                            });

                            Log::debug('purchase_price column dropped successfully', [
                                'table_name' => $table_name,
                                'connection' => $connection
                            ]);
                        }

                        // then add column
                        Schema::connection($connection)->table($table_name, function (Blueprint $table) {
                            $table->decimal('purchase_price', 20, 2)
                                ->default(0)
                                ->after('min_stock_alert');
                        });

                        Log::debug('purchase_price column added successfully', [
                            'table_name' => $table_name,
                            'connection' => $connection
                        ]);

                    } else {

                        Log::debug('Table not found', [
                            'table_name' => $table_name,
                            'connection' => $connection
                        ]);
                    }

                } catch (\Exception $e) {

                    Log::error('Error while updating table', [
                        'user_id' => $user->user_id,
                        'table_name' => $table_name ?? '',
                        'connection' => $connection ?? '',
                        'message' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile()
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Purchase price column updated successfully'
            ]);

        } catch (\Exception $e) {

            Log::error('addPurchasePrice function error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    // ################################################### ADD PURCHASE PRICE FIELD IN SHOP USER INVENTORY TBALE ###################################################


    // ################################################### ADD SKU FIELD IN SHOP USER INVENTORY TBALE ###################################################
    public function addSku()
    {
        try {
            $users = User::where('isAdmin', 1)->get();

            $summary = [
                'total_users' => $users->count(),
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'table_not_found' => 0,
                'invalid_connection' => 0,
                'details' => []
            ];

            foreach ($users as $user) {

                $table_name = null;
                $connection = null;

                try {
                    $table_name = 'dataset_'.TableNameHelper::getTableName($user->user_id);
                    $connection = $user->module_type;

                    // validate connection
                    if (!config("database.connections.$connection")) {
                        $summary['invalid_connection']++;

                        $summary['details'][] = [
                            'user_id' => $user->user_id,
                            'table' => $table_name,
                            'connection' => $connection,
                            'status' => 'invalid_connection'
                        ];
                        continue;
                    }

                    if (!Schema::connection($connection)->hasTable($table_name)) {
                        $summary['table_not_found']++;

                        $summary['details'][] = [
                            'user_id' => $user->user_id,
                            'table' => $table_name,
                            'status' => 'table_not_found'
                        ];
                        continue;
                    }

                    /*
                    |-----------------------------------------
                    | DROP OLD COLUMNS SAFELY
                    |-----------------------------------------
                    */
                    Schema::connection($connection)->table($table_name, function (Blueprint $table) use ($connection, $table_name) {

                        // remove old sku_sequence if exists
                        if (Schema::connection($connection)->hasColumn($table_name, 'sku_sequence')) {
                            try {
                                $table->dropUnique(['sku_sequence']);
                            } catch (\Exception $e) {
                            }

                            try {
                                $table->dropColumn('sku_sequence');
                            } catch (\Exception $e) {
                            }
                        }

                        // remove old sku
                        if (Schema::connection($connection)->hasColumn($table_name, 'sku')) {
                            try {
                                $table->dropUnique(['sku']);
                            } catch (\Exception $e) {
                            }

                            try {
                                $table->dropColumn('sku');
                            } catch (\Exception $e) {
                            }
                        }

                        // remove old category_id
                        if (Schema::connection($connection)->hasColumn($table_name, 'category_id')) {
                            try {
                                $table->dropColumn('category_id');
                            } catch (\Exception $e) {
                            }
                        }
                    });

                    /*
                    |-----------------------------------------
                    | ADD NEW COLUMNS
                    |-----------------------------------------
                    */
                    Schema::connection($connection)->table($table_name, function (Blueprint $table) {
                        $table->string('sku', 50)
                            ->nullable()
                            ->unique()
                            ->after('id');

                        $table->unsignedBigInteger('category_id')
                            ->nullable()
                            ->after('sku');
                    });

                    $summary['success']++;

                    $summary['details'][] = [
                        'user_id' => $user->user_id,
                        'table' => $table_name,
                        'status' => 'updated'
                    ];

                } catch (\Exception $e) {

                    $summary['failed']++;

                    $summary['details'][] = [
                        'user_id' => $user->user_id,
                        'table' => $table_name,
                        'connection' => $connection,
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];

                    Log::error('Error updating table', [
                        'user_id' => $user->user_id,
                        'table_name' => $table_name,
                        'connection' => $connection,
                        'message' => $e->getMessage(),
                        'line' => $e->getLine()
                    ]);
                }

                $summary['processed']++;
            }

            return response()->json([
                'status' => true,
                'message' => 'Process completed successfully',
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('addSku failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
    // ################################################### ADD SKU FIELD IN SHOP USER INVENTORY TBALE ###################################################


    // ################################################### GENERATE RANDOM BILLS ###################################################

    public function generateBills()
    {
        for ($i = 117; $i <= 216; $i++) {

            // Random date between Jan 2025 and Mar 2026
            $randomDate = \Carbon\Carbon::createFromTimestamp(
                rand(
                    strtotime('2025-01-01'),
                    strtotime('2026-03-30')
                )
            );

            $rate = rand(20, 300);
            $quantity = rand(1, 5);
            $amount = $rate * $quantity;

            DB::connection('grocery_india')->table('billing')->insert([
                'shop_id' => 69,
                'user_id' => 112,
                'invoice_number' => 'RB2025/' . $i,
                'invoice_count' => $i,

                'item_list' => json_encode([
                    [
                        "itemId" => rand(200, 400),
                        "itemName" => "Sample Item " . $i,
                        "quantity" => number_format($quantity, 2),
                        "rate" => number_format($rate, 2),
                        "selectedUnit" => "PCS",
                        "amount" => number_format($amount, 2),
                        "isDelete" => "0",
                        "isRefund" => "0",
                        "mrp" => number_format($rate, 10),
                        "hsn" => "1001",
                        "tax1" => [
                            "name" => "GST",
                            "percent" => "5.00",
                            "amount" => number_format($amount * 0.05, 2)
                        ],
                        "tax2" => [
                            "name" => "CESS",
                            "percent" => "0.00",
                            "amount" => "0.00"
                        ]
                    ]
                ]),

                'total_price' => $amount,
                'payment_status' => rand(0, 1),

                'created_at' => $randomDate,
                'updated_at' => $randomDate
            ]);
        }
    }

    // ################################################### GENERATE RANDOM BILLS ###################################################


    // ################################################### GENERATE SKU FOR EXISTING ITEMS ###################################################

    // public function generateSKUforExistingItems()
    // {
    //     try {
    //         $users = User::where('isAdmin', 1)->get();

    //         $totalTables = $users->count();
    //         $processedTables = 0;
    //         $skippedTables = 0;
    //         $totalItems = 0;
    //         $totalPendingItems = 0;
    //         $updatedCount = 0;

    //         Log::info("SKU generation started", [
    //             'total_tables' => $totalTables
    //         ]);

    //         foreach ($users as $user) {

    //             $tableName = null;
    //             $connection = null;

    //             try {
    //                 $tableName = TableNameHelper::getTableName($user->user_id);
    //                 $connection = $user->module_type;

    //                 Log::info("Checking table", [
    //                     'user_id' => $user->user_id,
    //                     'connection' => $connection,
    //                     'table' => $tableName
    //                 ]);

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Validate table and sku column
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 if (!Schema::connection($connection)->hasTable($tableName)) {
    //                     Log::warning("Table not found: {$tableName}");
    //                     $skippedTables++;
    //                     continue;
    //                 }

    //                 if (!Schema::connection($connection)->hasColumn($tableName, 'sku')) {
    //                     Log::warning("SKU column missing in {$tableName}");
    //                     $skippedTables++;
    //                     continue;
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Count records
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 $tableTotalItems = DB::connection($connection)
    //                     ->table($tableName)
    //                     ->count();

    //                 $pendingItems = DB::connection($connection)
    //                     ->table($tableName)
    //                     ->where(function ($q) {
    //                         $q->whereNull('sku')
    //                             ->orWhere('sku', '');
    //                     })
    //                     ->count();

    //                 $totalItems += $tableTotalItems;
    //                 $totalPendingItems += $pendingItems;

    //                 Log::info("Table stats", [
    //                     'table' => $tableName,
    //                     'total_items' => $tableTotalItems,
    //                     'pending_sku_items' => $pendingItems
    //                 ]);

    //                 if ($pendingItems == 0) {
    //                     Log::info("No pending SKU found for {$tableName}");
    //                     $processedTables++;
    //                     continue;
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Get last sequence
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 $lastSequence = (int) DB::connection($connection)
    //                     ->table($tableName)
    //                     ->max('sku_sequence');

    //                 $lastSequence = $lastSequence > 0 ? $lastSequence : 0;

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Generate SKU
    //                 |--------------------------------------------------------------------------
    //                 */
    //                 DB::connection($connection)
    //                     ->table($tableName)
    //                     ->where(function ($q) {
    //                         $q->whereNull('sku')
    //                             ->orWhere('sku', '');
    //                     })
    //                     ->orderBy('id')
    //                     ->chunk(500, function ($items) use ($connection, $tableName, &$updatedCount, &$lastSequence) {
    //                         foreach ($items as $item) {

    //                             $shortUnit = strtoupper($item->short_unit ?? '');

    //                             $lastSequence++;

    //                             $sku = InventoryHelpher::generateSkuFromSequence(
    //                                 $item->item_name,
    //                                 $shortUnit,
    //                                 $lastSequence
    //                             );

    //                             $updated = DB::connection($connection)
    //                                 ->table($tableName)
    //                                 ->where('id', $item->id)
    //                                 ->update([
    //                                     'sku_sequence' => $lastSequence,
    //                                     'sku' => $sku,
    //                                     'updated_at' => now()
    //                                 ]);

    //                             if ($updated) {
    //                                 $updatedCount++;
    //                             }
    //                         }
    //                     });

    //                 $processedTables++;

    //                 Log::info("Completed table", [
    //                     'table' => $tableName
    //                 ]);

    //             } catch (\Exception $e) {
    //                 $skippedTables++;

    //                 Log::error("Failed for table", [
    //                     'user_id' => $user->user_id,
    //                     'table' => $tableName,
    //                     'connection' => $connection,
    //                     'message' => $e->getMessage(),
    //                     'line' => $e->getLine()
    //                 ]);
    //             }
    //         }

    //         Log::info("SKU generation completed", [
    //             'total_tables' => $totalTables,
    //             'processed_tables' => $processedTables,
    //             'skipped_tables' => $skippedTables,
    //             'total_items' => $totalItems,
    //             'pending_items' => $totalPendingItems,
    //             'updated_items' => $updatedCount
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'SKU generated successfully',
    //             'summary' => [
    //                 'total_tables' => $totalTables,
    //                 'processed_tables' => $processedTables,
    //                 'skipped_tables' => $skippedTables,
    //                 'total_items' => $totalItems,
    //                 'pending_items' => $totalPendingItems,
    //                 'updated_items' => $updatedCount
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {

    //         Log::error('Global SKU generation failed', [
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile()
    //         ]);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to generate SKU',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // ################################################### GENERATE SKU FOR EXISTING ITEMS ###################################################
    
    
    // ################################################### ADD BARCODE TO DATASET ###################################################
    public function addBarcodeToDatset()
    {
        $users = User::where('isAdmin', 1)
        ->where('module_type','grocery_india')->get();

        foreach ($users as $user) {

            $table_name = 'dataset_' .TableNameHelper::getTableName($user->user_id);

            // connection name from module_type
            $connection = $user->module_type;

            if (
                Schema::connection($connection)->hasTable($table_name) &&
                !Schema::connection($connection)->hasColumn($table_name, 'barcode')
            ) {

                Schema::connection($connection)->table($table_name, function (Blueprint $table) {
                    $table->string('barcode', 50)->nullable()->after('tags');
                });

            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Barcode column added successfully'
        ]);
    }

    // ################################################### ADD BARCODE TO DATASET ###################################################


}