<?php

namespace Modules\Authentication\Helpers;

use Illuminate\Support\Facades\DB;

use Exception;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;


use Modules\Authentication\Entities\ApiKey;
use Modules\Authentication\Entities\User;

use Modules\Core\Helpers\CountryHelpher;

class UserHelper
{

    public static function checkUserShopDetails($mobile)
    {
        $user = User::where('mobile', $mobile)->where('isAdmin', 1)->first();

        if (!$user) {
            return [
                'user_id' => 0,
                'checkUser' => 0,
            ];
        }

        return [
            'user_id' => $user->user_id,
            // 'checkUser' => Shop::where('user_id', $user->user_id)->count(),
            'checkUser' => DB::connection($user->module_type)->table('shops')->where('user_id', $user->user_id)->count(),
        ];
    }

    public static function apiKeyGenerate(int $user_id): string
    {
        $maxAttempts = 3; // Define the maximum number of attempts
        $attempt = 0;

        $user = User::find($user_id);

        do {
            try {
                $attempt++;

                // Find the user by ID
                $user = User::find($user_id);

                // Ensure the user exists
                if (!$user) {
                    throw new \InvalidArgumentException(__('validation.User not found.'));
                }

                $dial_code = CountryHelpher::getDialCodeFromCountryJson($user->country_details);


                if (!$dial_code) {
                    throw new \InvalidArgumentException(__('validation.Dial code not found.'));
                }

                // Validate and truncate input
                $countryCode = substr(preg_replace('/[^0-9]/', '', $dial_code ?? ''), 0, 5);
                $mobileNumber = substr(preg_replace('/[^0-9]/', '', $user->mobile ?? ''), 0, 10);

                // Ensure both fields are non-empty
                if (empty($countryCode) || empty($mobileNumber)) {
                    throw new \InvalidArgumentException(__('validation.Invalid user data: country code or mobile number missing.'));
                }

                // Create a unique combination
                $randomBytes = bin2hex(random_bytes(16)); // 16 bytes = 32 hex characters
                $combination = $countryCode . $mobileNumber . $randomBytes;

                // Generate the API key using a secure hash
                $apiKey = hash('sha256', $combination);

                // Truncate to max size (255 characters) if needed
                $apiKey = substr($apiKey, 0, 255);

                // Check if the API key is valid (length or other custom rules can be checked here)
                if (strlen($apiKey) > 255) {
                    throw new Exception(__('validation.API key exceeds the maximum allowed size.'));
                }


                // // Store the API key in the database
                // ApiKey::create([
                //     'key' => $apiKey,
                //     'user_id' => $user_id,
                // ]);

                ApiKey::updateOrCreate(
                    ['user_id' => $user_id], // Attributes to match
                    ['key' => $apiKey]       // Values to set or update
                );


                // Return the generated API key
                return $apiKey;
            } catch (Exception $e) {

                report($e);

                // Log the exception for debugging purposes
                Log::error('API Key generation attempt failed: ' . $e->getMessage());

                // Retry the generation if attempts are left
                if ($attempt >= $maxAttempts) {
                    throw new Exception(__('validation.Failed to generate API key after') . ' ' . $maxAttempts . ' ' . __('validation.attempts') . '.');
                }
            }
        } while ($attempt < $maxAttempts);
    }

    public static function createOrUpdateAdminData($user_id, $name, $email, $business_name, $address, $gstin, $imageName, $module_type)
    {

        // Create admin record
        DB::connection($module_type)->table('shops')->updateOrInsert(
            ['user_id' => $user_id],
            [
                'email' => $email,
                'name' => $name,
                'business_name' => $business_name,
                'address' => $address,
                'gstin' => $gstin ?? 'NA',
                'logo' => $imageName ?? 'NA',
            ]
        );
    }

    public static function generateItemTableForAdmin($table_name, $module_type, $region = 'other', $country_name='')
    {

        switch ($region) {
            case 'europe':

                DB::connection($module_type)->statement("DROP TABLE IF EXISTS {$table_name}");

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE IF NOT EXISTS {$table_name} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sku VARCHAR(50) UNIQUE NULL,
                    category_id BIGINT UNSIGNED NULL,
                    item_name VARCHAR(255) UNIQUE NOT NULL,
                    quantity DECIMAL(20,2) NOT NULL,
                    min_stock_alert DECIMAL(20,2) NOT NULL,
                    purchase_price DECIMAL(20,2) NOT NULL, 
                    mrp DECIMAL(20,2) NOT NULL, 
                    sale_price DECIMAL(20,2) NOT NULL,
                    full_unit VARCHAR(50) NOT NULL,
                    short_unit VARCHAR(50) NOT NULL,
                    tax1 VARCHAR(10) NOT NULL,
                    rate1 DECIMAL(5,2) NOT NULL,
                    tags LONGTEXT NOT NULL,
                    barcode VARCHAR(50) NULL,  -- NEW FIELD BARCODE ADDED
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                break;

            case 'asia':

                DB::connection($module_type)->statement("DROP TABLE IF EXISTS {$table_name}");

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE IF NOT EXISTS {$table_name} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sku VARCHAR(50) UNIQUE NULL,
                    category_id BIGINT UNSIGNED NULL,
                    item_name VARCHAR(255) UNIQUE NOT NULL,
                    quantity DECIMAL(20,2) NOT NULL,
                    min_stock_alert DECIMAL(20,2) NOT NULL,
                    purchase_price DECIMAL(20,2) NOT NULL, 
                    mrp DECIMAL(20,2) NOT NULL, 
                    sale_price DECIMAL(20,2) NOT NULL,
                    full_unit VARCHAR(50) NOT NULL,
                    short_unit VARCHAR(50) NOT NULL,
                    hsn VARCHAR(50) NOT NULL,
                    tax1 VARCHAR(10) NOT NULL,
                    rate1 DECIMAL(5,2) NOT NULL,
                    tax2 VARCHAR(10) NOT NULL,
                    rate2 DECIMAL(5,2) NOT NULL,
                    tags LONGTEXT NOT NULL,
                    barcode VARCHAR(50) NULL,  -- NEW FIELD BARCODE ADDED
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                break;

            case 'uae':
                break;

            default:
                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE IF NOT EXISTS {$table_name} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sku VARCHAR(50) UNIQUE NULL,
                    category_id BIGINT UNSIGNED NULL,
                    item_name VARCHAR(255) UNIQUE NOT NULL,
                    quantity DECIMAL(20,2) NOT NULL,
                    min_stock_alert DECIMAL(20,2) NOT NULL,
                    purchase_price DECIMAL(20,2) NOT NULL, 
                    mrp DECIMAL(20,2) NOT NULL, 
                    sale_price DECIMAL(20,2) NOT NULL,
                    full_unit VARCHAR(50) NOT NULL,
                    short_unit VARCHAR(50) NOT NULL,
                    hsn VARCHAR(50) NOT NULL,
                    tax1 VARCHAR(10) NOT NULL,
                    rate1 DECIMAL(5,2) NOT NULL,
                    tax2 VARCHAR(10) NOT NULL,
                    rate2 DECIMAL(5,2) NOT NULL,
                    tags LONGTEXT NOT NULL,
                    barcode VARCHAR(50) NULL,  -- NEW FIELD BARCODE ADDED
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                break;
        }
    }

    public static function generateDatasetTableForAdmin($table_name, $module_type, $region = 'other')
    {
        // Check if table exists
        if (Schema::connection($module_type)->hasTable($table_name)) {
            return; // Table already exists, no need to create
        }

        switch ($region) {
            case 'Europe':

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sku VARCHAR(50) UNIQUE NULL,
                    category_id BIGINT UNSIGNED NULL,
                    item_name VARCHAR(255) NULL,
                    -- sku VARCHAR(50) UNIQUE NOT NULL,
                    quantity DECIMAL(20,2) NULL,
                    min_stock_alert DECIMAL(20,2) NULL,
                    purchase_price DECIMAL(20,2) NOT NULL, 
                    mrp DECIMAL(20,2) NULL,
                    sale_price DECIMAL(20,2) NULL,
                    full_unit VARCHAR(50) NULL,
                    short_unit VARCHAR(50) NULL,
                    tax1 VARCHAR(10) NULL,
                    rate1 DECIMAL(5,2) NULL,
                    tags LONGTEXT NULL,
                    barcode VARCHAR(50) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                break;

            case 'asia':

                // Create dynamic table
                DB::connection($module_type)->statement("CREATE TABLE {$table_name} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sku VARCHAR(50) UNIQUE NULL,
                    category_id BIGINT UNSIGNED NULL,
                    item_name VARCHAR(255) NULL,
                    -- sku VARCHAR(50) UNIQUE NOT NULL,
                    quantity DECIMAL(20,2) NULL,
                    min_stock_alert DECIMAL(20,2) NULL,
                    purchase_price DECIMAL(20,2) NOT NULL, 
                    mrp DECIMAL(20,2) NULL,
                    sale_price DECIMAL(20,2) NULL,
                    full_unit VARCHAR(50) NULL,
                    short_unit VARCHAR(50) NULL,
                    hsn VARCHAR(50) NULL,
                    tax1 VARCHAR(10) NULL,
                    rate1 DECIMAL(5,2) NULL,
                    tax2 VARCHAR(10) NULL,
                    rate2 DECIMAL(5,2) NULL,
                    tags LONGTEXT NULL,
                    barcode VARCHAR(50) NULL,
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
                    -- sku VARCHAR(50) UNIQUE NOT NULL,
                    quantity DECIMAL(20,2) NULL,
                    min_stock_alert DECIMAL(20,2) NULL,
                    purchase_price DECIMAL(20,2) NOT NULL, 
                    mrp DECIMAL(20,2) NULL,
                    sale_price DECIMAL(20,2) NULL,
                    full_unit VARCHAR(50) NULL,
                    short_unit VARCHAR(50) NULL,
                    hsn VARCHAR(50) NULL,
                    tax1 VARCHAR(10) NULL,
                    rate1 DECIMAL(5,2) NULL,
                    tax2 VARCHAR(10) NULL,
                    rate2 DECIMAL(5,2) NULL,
                    tags LONGTEXT NULL,
                    barcode VARCHAR(50) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FULLTEXT(item_name, tags) -- Adding FULLTEXT index
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                break;
        }
    }

    public static function createOrUpdatePreference($user_id, $module_type)
    {
        $preferences = DB::connection($module_type)->table('preferences')->where('user_id', $user_id)->first();

        // CREATE PREFERENCE FOR USER
        $preferences = DB::connection($module_type)->table('preferences')->where('user_id', $user_id)->first();
        if ($preferences) {
            // update
            $data = [
                'user_id' => $user_id,
                'preference_mrp' => 0,
                'preference_mrp_invoice' => 0,
                'preference_quantity' => 0,
                'preference_hsn' => 0,
                'preference_hsn_invoice' => 0,
                'preference_invoice_format' => 0,
                'preference_transaction_mark_as_paid' => 0,
                'preference_barcode' => 0,
                'preference_invoice_gst_complaint' => 0,
                'preference_purchase_price' => 0,
                'preference_sku' => 0,
                'preference_l1_category' => 0,
                'preference_l2_category' => 0,
            ];
            $result = DB::connection($module_type)->table('preferences')->where('id', $preferences->id)->update($data);
        } else {
            // save
            $data = [
                'user_id' => $user_id,
                'preference_mrp' => 0,
                'preference_mrp_invoice' => 0,
                'preference_quantity' => 0,
                'preference_hsn' => 0,
                'preference_hsn_invoice' => 0,
                'preference_invoice_format' => 0,
                'preference_transaction_mark_as_paid' => 0,
                'preference_barcode' => 0,
                'preference_invoice_gst_complaint' => 0,
                'preference_purchase_price' => 0,
                'preference_sku' => 0,
                'preference_l1_category' => 0,
                'preference_l2_category' => 0,
            ];

            $result = DB::connection($module_type)->table('preferences')->insert($data);
        }
        // CREATE PREFERENCE FOR USER
    }

    // ASSIGN SUBSCRPTION
    public static function assignSubscription($shop_id, $subscription_id, $module_type)
    {
        // Find the shop by its ID
        // $shop = Shop::find($shop_id);
        
        $shop = DB::connection($module_type)->table('shops')->where('shop_id', $shop_id)->first();
    

        if (!$shop) {
            // Handle case where the shop is not found
            return [
                'success' => false,
                'message' => __('validation.Shop not found')
            ];
        }

        // Calculate start and end dates
        $startDate = $shop->created_at;
        $endDate = Carbon::parse($shop->created_at)->addDays(env('SUBSCRIPTION_FREE_PLAN_EXPIRY_DAYS'));

        // Prepare data for insertion
        $data = [
            'shop_id' => $shop_id,
            'subscription_id' => $subscription_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_status' => 'free',
            // 'created_at' => now(), // Add timestamps if needed
            // 'updated_at' => now()
        ];

        // Insert into the database and check if successful
        $result = DB::connection($module_type)->table('shop_subscriptions')->insert($data);

        return [
            'success' => $result,
            'message' => $result ? __('validation.Subscription assigned successfully') : __('validation.Failed to assign subscription')
        ];
    }
    // ASSIGN SUBSCRPTION


    // FUNCTIONALITY UPDATED => ASSIGN SUBSCRIPTION => FREE PLAN WILL BE VALID FOR LIMETIME
    
    // FUNCTIONALITY UPDATED => ASSIGN SUBSCRIPTION => FREE PLAN WILL BE VALID FOR LIMETIME


    // CHECK SUBSCRIPTION

    public static function checkShopSubscription($shopId,$module_type)
    {
        try {


            // Retrieve the latest subscription for the shop
            // $subscription = ShopSubscriptions::where('shop_id', $shopId)
            //     ->orderBy('end_date', 'desc')
            //     ->first();

            $subscription = DB::connection($module_type)->table('shop_subscriptions')
                ->where('shop_id', $shopId)
                ->orderBy('end_date', 'desc')
                ->first();

                
            if (!$subscription) {
                // No subscription found
                return [
                    'status' => 0,
                    'message' => __('validation.No subscription found for the shop'),
                    'code' => 403,
                ];
            }

            // Check subscription expiry
            // $currentDate = Carbon::now();

            // $currentDate = Carbon::parse("2025-01-21 00:00:00");


            // $endOfDay = Carbon::parse($subscription->end_date)->endOfDay(); // Expire at 11:59 PM of the expiration date

            // if ($currentDate->greaterThan($endOfDay)) {
            if (UserHelper::isSubscriptionExpired($subscription, $subscription->end_date) == 1) {
                return [
                    'status' => 0,
                    'message' => __('validation.The shop subscription has expired'),
                    'code' => 200,
                ];
            }

            // Check payment status
            if (!in_array($subscription->payment_status, ['free', 'paid'])) {
                return [
                    'status' => 0,
                    'message' => __('validation.The subscription payment status is not valid'),
                    'code' => 403,
                ];
            }

            // Subscription is valid
            return [
                'status' => 1,
                'message' => __('valid.Valid subscription'),
                'code' => 200,
            ];

        } catch (Exception $e) {

            report($e);

            \Log::error('Shop Subscription Check Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 0,
                'message' => __('validation.Internal Server Error'),
                'code' => 500,
            ];
        }
    }
    // CHECK SUBSCRIPTION


    // CHECK SUBSCRIPTIN IS EXPIRED OR NOT

    public static function isSubscriptionExpired($shop_subscription, $subscription_expiry_date)
    {

        $currentDate = Carbon::now();
        // $currentDate = Carbon::parse("2025-01-21 00:00:00");
        // $currentDate = Carbon::parse("2025-01-20 23:59:00");
        $endOfDay = Carbon::parse($subscription_expiry_date)->endOfDay(); // Expire at 11:59 PM of the expiration date

        $isSubscriptionExpired = 0;
        if ($shop_subscription) {
            $endOfDay = Carbon::parse($subscription_expiry_date)->endOfDay(); // Expire at 11:59 PM of the expiration date

            if ($currentDate->greaterThan($endOfDay)) {
                $isSubscriptionExpired = 1; // Subscription is expired
            }
        } else {
            $isSubscriptionExpired = 2; // No subscription exists
        }

        return $isSubscriptionExpired;

    }

    // CHECK SUBSCRIPTIN IS EXPIRED OR NOT

    public static function renameTable($business_name, $old_mobile, $new_mobile)
    {
        $business_name = strtolower(str_replace(' ', '_', $business_name));

        // Define table name patterns
        $tables = [
            'item_' => 'Main',
            'dataset_item_' => 'Dataset',
        ];

        foreach ($tables as $prefix => $label) {
            $old_table = "{$prefix}{$business_name}_{$old_mobile}";
            $new_table = "{$prefix}{$business_name}_{$new_mobile}";

            // Check if old table exists
            if (!Schema::hasTable($old_table)) {
                return [
                    'status' => false,
                    'message' => __("validation.{$label} old table does not exist"),
                    'table' => $old_table
                ];
            }

            // Prevent name conflict
            if (Schema::hasTable($new_table)) {
                return [
                    'status' => false,
                    'message' => __("validation.{$label} new table name already exists"),
                    'table' => $new_table
                ];
            }

            // Rename table
            try {
                DB::statement("ALTER TABLE `$old_table` RENAME TO `$new_table`");
            } catch (\Exception $e) {

                report($e);

                return [
                    'status' => false,
                    'message' => __("validation.Failed to rename {$label} table"),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'status' => true,
            'message' => __('validation.Tables renamed successfully')
        ];
    }

    public static function getUserPreference($user)
    {

        $user_id = 0;

        $user = User::find($user->user_id);

        if ($user->isAdmin == 1) {
            $user_id = $user->user_id;
        } else {

            $staff = $user->staff;
            // $shop = Shop::find($staff->addedBy);
            $shop = DB::table('shops')->where('shop_id', $staff->addedBy)->first();

            $user_id = $shop->user_id;

        }

        $preferences = DB::table('preferences')->where('user_id', $user_id)->first();

        return $preferences;
    }

    public static function get_request_source()
    {
        $request = request();
        $source = 'unknown';

        try {
            // Check for custom header (recommended for apps)
            $appHeader = $request->header('X-APP-SOURCE');

            if ($appHeader) {
                $source = strtolower($appHeader) === 'mobile' ? 'app' : 'web';
            } else {
                // Fallback to User-Agent detection
                $userAgent = $request->header('User-Agent', '');

                // Log::info("source: {$userAgent}");

                if (preg_match('/(iPhone|iPad|Android|Mobile|Flutter|ReactNative|Dart)/i', $userAgent)) {
                    $source = 'app';
                } else {
                    $source = 'web';
                }
            }

            // Log the detection
            Log::debug('Request source detected', [
                'source' => $source,
                'user_agent' => $userAgent ?? 'Not provided',
                'ip' => $request->ip(),
                'custom_header' => $appHeader ?? 'Not provided',
            ]);

            return $source;

        } catch (\Exception $e) {

            report($e);
            
            Log::error('Error detecting request source', [
                'error' => $e->getMessage(),
                'user_agent' => $userAgent ?? 'Not provided',
                'ip' => $request->ip(),
            ]);
            return 'unknown';
        }
    }

    public static function getCategories($user_id) {
        $user = User::find($user_id);

        if (!$user) {
            return collect(); // or return [];
        }

        $shopType = null;

        // Admin user
        if ($user->isAdmin == 1) {
            $shopType = $user->module_type;
        }
        // Staff user
        elseif ($user->isAdmin == 0 && $user->staff) {

            $shop = DB::table('shops')
                ->where('shop_id', $user->staff->addedBy)
                ->first();

            if (!$shop) {
                return collect();
            }

            $staffOwner = User::find($shop->user_id);

            if (!$staffOwner) {
                return collect();
            }

            $shopType = $staffOwner->module_type;
        }

        if (!$shopType) {
            return collect();
        }

        return DB::connection($shopType)
            ->table('categories')
            ->get();
    }

}

