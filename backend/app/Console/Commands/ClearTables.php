<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\Shop;

class ClearTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Tables';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        try {
            // Get all database connections from config
            $connections = array_keys(config('database.connections'));

            foreach ($connections as $connection) {
                try {
                    // Set the current connection
                    DB::setDefaultConnection($connection);

                    // Get database name and tables
                    $database = DB::getDatabaseName();
                    $tables = DB::select('SHOW TABLES');

                    Log::info("Processing database: {$database} on connection: {$connection}");

                    foreach ($tables as $table) {
                        $tableName = $table->{'Tables_in_' . $database};

                        // Check for temporary tables with 'temp_' prefix
                        if (strpos($tableName, 'temp_') !== false) {
                            Schema::connection($connection)->dropIfExists($tableName);
                            Log::info("Deleted table with prefix 'temp_': {$tableName} from database: {$database}");
                            continue; // Skip further checks for this table
                        }

                        // Check for 'item_' or 'dataset_' prefixes
                        if (strpos($tableName, 'item_') === 0 || strpos($tableName, 'dataset_') === 0) {
                            // Extract mobile number and business name
                            $prefix = strpos($tableName, 'item_') === 0 ? 'item' : 'dataset_item';
                            $pattern = "/^{$prefix}_(.+)_(\d+)$/";

                            if (preg_match($pattern, $tableName, $matches)) {
                                $business_name = str_replace('_', ' ', $matches[1]); // Replace underscores with spaces
                                $business_name = strtolower($business_name);         // Convert to lowercase
                                $mobile = $matches[2];                              // e.g., '123' from 'item_something_123'

                                // Check if user exists
                                $user = User::where('mobile', $mobile)->first();
                                $userExists = $user !== null;

                                // Check if shop exists, only if user exists
                                $shopExists = false;
                                if ($userExists) {
                                    // $shopExists = Shop::whereRaw('LOWER(business_name) = ?', [$business_name])
                                    //     ->where('user_id', $user->user_id)
                                    //     ->exists();

                                    $shopExists = DB::table('shops')
                                        ->whereRaw('LOWER(business_name) = ?', [$business_name])
                                        ->where('user_id', $user->user_id)
                                        ->exists();


                                }


                                $this->info("Table: {$tableName}, Prefix: {$prefix}");
                                $this->info("Business Name: {$business_name}, Mobile: {$mobile}");
                                $this->info("User Exists: " . ($userExists ? 'Yes' : 'No'));
                                $this->info("Shop Exists: " . ($shopExists ? 'Yes' : 'No'));


                                // Delete table if no user exists OR no shop exists for the user
                                if (!$userExists || ($userExists && !$shopExists)) {
                                    Schema::connection($connection)->dropIfExists($tableName);
                                    Log::info("Deleted table with prefix '{$prefix}_' (no matching user or shop): {$tableName} from database: {$database}");
                                }
                            } else {
                                Log::warning("Table {$tableName} has prefix '{$prefix}_' but does not match expected format. Skipping.");
                            }
                        }
                    }

                } catch (\Exception $e) {
                    Log::warning("Skipping connection {$connection}: " . $e->getMessage());
                    continue; // Continue with next connection if one fails
                }
            }

            $this->info('All eligible tables across all databases have been deleted successfully.');
            Log::info('Multi-database cleanup operation completed successfully');

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Error during multi-database cleanup operation: ' . $e->getMessage());
        } finally {
            // Reset to default connection
            DB::setDefaultConnection(config('database.default'));
        }
    }

}

