<?php

namespace Modules\Authentication\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\DB;
use Auth;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use Modules\Core\Entities\OTP;
use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\Staff;

use Modules\Core\Helpers\ResponseHelper;
use Modules\Core\Helpers\SendMessageHelper;
use Modules\Core\Helpers\OTPHelper;


use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DeleteController extends Controller
{

    // public function deleteAllAccounts()
    // {
    //     // if (!config('app.allow_mass_delete', false)) {
    //     //     return ResponseHelper::responseFn(0, 403, 'Mass account deletion is disabled.', []);
    //     // }

    //     $users = User::where('isAdmin', 1)->get();

    //     // if ($users->isEmpty()) {
    //     //     return ResponseHelper::responseFn(1, 200, 'No admin accounts found.', []);
    //     // }

    //     $results = ['deleted' => 0, 'errors' => []];

    //     DB::beginTransaction();
    //     try {

    //         foreach ($users as $user) {

    //             $module_type = $user->module_type;
    //             $business_name = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($user->shop->business_name));
    //             $mobile_no = $user->mobile;

    //             $shopConnection = DB::connection($module_type);
    //             $tables = [
    //                 'inventory' => "item_{$business_name}_{$mobile_no}",
    //                 'dataset' => "dataset_item_{$business_name}_{$mobile_no}",
    //                 'temp' => "temp_item_{$business_name}_{$mobile_no}",
    //                 'temp1' => "temp_item_{$business_name}_{$mobile_no}_web",
    //             ];

    //             \Log::info("Starting deletion for user: {$user->user_id}");

    //             $shopConnection->beginTransaction();

    //             try {
    //                 foreach ($tables as $key => $table) {
    //                     if (Schema::connection($module_type)->hasTable($table)) {
    //                         \Log::info("Deleting data from table: {$table}");
    //                         $shopConnection->table($table)->delete();
    //                         \Log::info("Dropping table: {$table}");
    //                         Schema::connection($module_type)->dropIfExists($table);
    //                     } else {
    //                         \Log::info("Table does not exist: {$table}");
    //                     }
    //                 }

    //                 $user->tokens()->delete(); // Delete all tokens
    //                 Cache::forget(config('cache.prefix') . 'user_' . $user->mobile);
    //                 Cache::forget(config('cache.prefix') . 'items_' . $user->mobile);
    //                 Cache::forget(config('cache.prefix') . 'all_items_' . $user->mobile);
    //                 Cache::forget(config('cache.prefix') . 'transactions_' . $user->mobile);

    //                 \Log::info("Deleting user: {$user->user_id}");
    //                 $user->delete();

    //                 $shopConnection->commit();
    //                 $results['deleted']++;
    //                 \Log::info("Account deletion completed for user: {$user->user_id}");
    //             } catch (\Exception $e) {
    //                 $shopConnection->rollBack();
    //                 \Log::error("Failed to delete user {$user->user_id}: " . $e->getMessage());
    //                 $results['errors'][] = "User {$user->user_id}: {$e->getMessage()}";
    //             }
    //         }

            
    //         // delete user whose isAdmin is 0
    //         $users = User::where('isAdmin', 0)->delete();
    //         // delete user whose isAdmin is 0


    //         DB::commit();
    //         return ResponseHelper::responseFn(1, 200, 'Account deletion completed.', $results);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error("Mass deletion failed: " . $e->getMessage());
    //         return ResponseHelper::responseFn(0, 500, 'Failed to delete accounts: ' . $e->getMessage(), $results);
    //     }
    // }


    public function deleteAccount(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return ResponseHelper::responseFn(0, 401, 'Unauthorized', []);
        }

        $validate = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:central.users',
            'otp' => 'required|digits:6',
        ], [
            'user_id.required' => __('delete_account_validation.required', ['attribute' => __('delete_account_validation.attributes.user_id')]),
            'user_id.numeric' => __('delete_account_validation.numeric', ['attribute' => __('delete_account_validation.attributes.user_id')]),
            'user_id.exists' => __('delete_account_validation.exists', ['attribute' => __('delete_account_validation.attributes.user_id')]),
            'otp.required' => __('delete_account_validation.required', ['attribute' => __('delete_account_validation.attributes.otp')]),
            'otp.digits' => __('delete_account_validation.digits', ['attribute' => __('delete_account_validation.attributes.otp'), 'digits' => 6]),
        ]);

        if ($validate->fails()) {
            return ResponseHelper::responseFn(0, 400, __('validation.Validation Error'), ['errors' => $validate->errors()]);
        }

        $user = Auth::guard('api')->user();
        $user_id = $user->user_id;
        $user = User::find($user_id);

        $OTP_Expiry = env('OTP_EXPIRY', 5);
        $OTP_Count = (int) env('OTP_COUNT', 3);

        $otp = Otp::where('mobile', $user->mobile)->first();
        if (!$otp) {
            return ResponseHelper::responseFn(0, 404, __('validation.No OTP record found for this phone number'), []);
        }

        if (Carbon::now()->gt(Carbon::parse($otp->created_at)->addMinutes($OTP_Expiry))) {
            return ResponseHelper::responseFn(0, 410, __('validation.OTP has expired. Please request a new OTP'), []);
        }

        if ($otp->code !== $request->otp) {
            $otp->increment('count');
            if ($otp->count >= $OTP_Count) {
                return ResponseHelper::responseFn(0, 429, __('validation.Maximum attempts reached'), ['retry_after' => $OTP_Expiry . ' ' . __('validation.minutes')]);
            }
            return ResponseHelper::responseFn(0, 400, __('validation.Invalid OTP. Please try again'), [
                'errors' => ['otp' => [__('validation.Invalid OTP. Attempt Left') . ' - ' . ($OTP_Count - $otp->count)]],
            ]);
        }

        $mobile = $otp->mobile;
        $otp->delete();

        $module_type = $user->module_type;
        $business_name = $user->shop->business_name;
        $mobile_no = $user->mobile;

        $shopConnection = DB::connection($module_type);

        $tables = [
            'inventory' => 'item_' . strtolower(str_replace(' ', '_', $business_name) . "_" . $mobile_no),
            'dataset' => 'dataset_item_' . strtolower(str_replace(' ', '_', $business_name) . "_" . $mobile_no),
            'temp' => 'temp_item_' . strtolower(str_replace(' ', '_', $business_name) . "_" . $mobile_no),
        ];

        try {

            \Log::info('Starting shop database transaction to delete data.');

            $shopConnection->beginTransaction();

            foreach ($tables as $key => $table) {
                if (!empty($shopConnection->select("SHOW TABLES LIKE '{$table}'"))) {
                    \Log::info("Deleting data from table: {$table}");
                    $shopConnection->statement("DELETE FROM {$table}");
                } else {
                    \Log::info("Table does not exist: {$table}");
                }
            }

            // delete shop staff accounts
            $staffs = Staff::where('addedBy',$user->shop->shop_id)->get();

            foreach($staffs as $staff){

                $staffUser = User::find($staff->user_id);
                $staffUser->delete();
                $staff->delete();
            }
            // delete shop staff accounts


            $shopConnection->commit();
            \Log::info('Data deletion transaction committed successfully.');

            foreach ($tables as $key => $table) {
                if (!empty($shopConnection->select("SHOW TABLES LIKE '{$table}'"))) {
                    \Log::info("Dropping table after commit: {$table}");
                    $shopConnection->statement("DROP TABLE {$table}");
                }
            }

            $all_tokens = $user->tokens;
            foreach ($all_tokens as $token) {
                if ($token->expires_at && Carbon::now()->gt($token->expires_at)) {
                    $token->delete();
                }
            }

            $current_token = $user->token();
            if ($current_token) {
                $current_token->delete();
            }

            Cache::forget(env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile);
            Cache::forget(env('CACHE_KEY_PREFIX') . 'items_' . $user->mobile);
            Cache::forget(env('CACHE_KEY_PREFIX') . 'all_items_' . $user->mobile);
            Cache::forget(env('CACHE_KEY_PREFIX') . 'transactions_' . $user->mobile);

            \Log::info("Deleting user: {$user->user_id}");
            $user->delete();

            \Log::info('Account deletion completed successfully.');
            return ResponseHelper::responseFn(1, 200, __('validation.Account deleted successfully'), []);

        } catch (\Exception $e) {

            report($e);

            \Log::error("Transaction failed: " . $e->getMessage());

            if ($shopConnection->transactionLevel() > 0) {
                $shopConnection->rollBack();
                \Log::info('Data deletion transaction rolled back successfully.');
            }

            return response()->json([
                'status' => 'error',
                'message' => __('validation.unable_to_process'),
            ], 500);
        }
    }
}
