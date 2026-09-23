<?php

namespace Modules\GroceryGermany\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Validator;
use Auth;
use Illuminate\Support\Facades\DB;

use Modules\GroceryGermany\Helpers\TableNameHelper;

use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    public function preference(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $validate = Validator::make($request->all(), [
                'preference_mrp' => 'nullable|in:0,1',
                'preference_mrp_invoice' => [
                    'nullable',
                    'in:0,1',
                    function ($attribute, $value, $fail) {
                        if (request()->input('preference_mrp') == 0 && $value == 1) {
                            $fail(__('preferences.preference_mrp_option_check'));
                        }
                    },
                ],
                'preference_quantity' => 'nullable|in:0,1',
                'preference_hsn' => 'nullable|in:0,1',
                'preference_hsn_invoice' => [
                    'nullable',
                    'in:0,1',
                    function ($attribute, $value, $fail) {
                        if (request()->input('preference_hsn') == 0 && $value == 1) {
                            $fail(__('preferences.preference_hsn_option_check'));
                        }
                    },
                ],
                'preference_invoice_format' => ['nullable', Rule::in(array_keys(config('general.invoice_format')))],

                'preference_transaction_mark_as_paid' => [
                    'nullable',
                    'in:0,1',
                ],

                'preference_barcode' => 'nullable|in:0,1',

                // 'taxes' => 'nullable',
            ], [
                'preference_mrp.in' => __('preferences.invalid', ['attribute' => __('preferences.attributes.preference_mrp')]),
                'preference_mrp_invoice.in' => __('preferences.invalid', ['attribute' => __('preferences.attributes.preference_mrp_invoice')]),
                'preference_quantity.in' => __('preferences.invalid', ['attribute' => __('preferences.attributes.preference_quantity')]),
                'preference_hsn.in' => __('preferences.invalid', ['attribute' => __('preferences.attributes.preference_hsn')]),
                'preference_hsn_invoice.in' => __('preferences.invalid', ['attribute' => __('preferences.attributes.preference_hsn_invoice')]),
                'preference_invoice_format.in' => __('preferences.invalid', ['attribute' => __('preferences.attributes.preference_invoice_format')]),
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            $user = Auth::guard('api')->user();
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();
            // $table_name = 'item_' . $user->username;

            $table_name = TableNameHelper::getTableNameOfLoggedInUser();

            // COMMENTED
            // if( $preferences->preference_quantity != $request->preference_quantity ){

            //     if($request->preference_quantity == 1){
            //         DB::table($table_name)->update(['quantity' => 0]);
            //     }
            //     else if($request->preference_quantity == 0){
            //         DB::table($table_name)->update(['quantity' => 'NA']);
            //     }
            // }
            // COMMENTED

            if ($preferences) {
                // update
                $data = [
                    'user_id' => $user->user_id,
                    'preference_mrp' => $request->preference_mrp ?? 0,
                    'preference_mrp_invoice' => $request->preference_mrp_invoice ?? 0,
                    'preference_quantity' => $request->preference_quantity ?? 0,
                    'preference_hsn' => $request->preference_hsn ?? 0,
                    'preference_hsn_invoice' => $request->preference_hsn_invoice ?? 0,
                    'preference_invoice_format' => $request->preference_invoice_format ?? 0,
                    'preference_transaction_mark_as_paid' => $request->preference_transaction_mark_as_paid ?? 0,
                    'preference_barcode' => $request->preference_barcode ?? 0,
                    // 'taxes' => $request->taxes,
                ];
                $result = DB::table('preferences')->where('id', $preferences->id)->update($data);
            } else {
                // save
                $data = [
                    'user_id' => $user->user_id,
                    'preference_mrp' => $request->preference_mrp ?? 0,
                    'preference_mrp_invoice' => $request->preference_mrp_invoice ?? 0,
                    'preference_quantity' => $request->preference_quantity ?? 0,
                    'preference_hsn' => $request->preference_hsn ?? 0,
                    'preference_hsn_invoice' => $request->preference_hsn_invoice ?? 0,
                    'preference_invoice_format' => $request->preference_invoice_format ?? 0,
                    'preference_transaction_mark_as_paid' => $request->preference_transaction_mark_as_paid ?? 0,
                    'preference_barcode' => $request->preference_barcode ?? 0,
                    // 'taxes' => json_encode($request->taxes),
                ];

                $result = DB::table('preferences')->insert($data);
            }

            return response()->json([
                'status' => 'success',
                'data' => $result
            ], 200);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function getUserPreferences()
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();
            // $table_name = 'item_' . $user->username;
            $preferenceId = DB::table('preferences')->where('user_id', $user->user_id)->first();

            $preferenceData = null;
            if ($preferenceId) {
                $preferenceData = DB::table('preferences')->find($preferenceId->id);
            }

            return response()->json([
                'status' => 'success',
                'data' => $preferenceData
            ], 200);
        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

}
