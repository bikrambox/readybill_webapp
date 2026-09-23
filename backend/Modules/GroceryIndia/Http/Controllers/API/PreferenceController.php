<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Validator;
use Auth;

use Illuminate\Support\Facades\DB;

use Modules\GroceryIndia\Helpers\TableNameHelper;

use Illuminate\Validation\Rule;

use Modules\Authentication\Entities\User;

use Modules\Core\Rules\NoScriptTag;
use Modules\Authentication\Rules\GstinRule;

use Modules\Core\Helpers\CommonHelpher;

class PreferenceController extends Controller
{
    public function preference(Request $request)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();
            $user = User::find($user->user_id);
            $shop = $user->shop;
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

            try {
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
                    'preference_invoice_format' => ['nullable', Rule::in(array_keys(config('general.invoice_format')))],

                    'preference_transaction_mark_as_paid' => [
                        'nullable',
                        'in:0,1',
                    ],


                    'preference_barcode' => 'nullable|in:0,1',
                    'preference_invoice_gst_complaint' => 'nullable|in:0,1',

                    // 'preference_hsn' => 'nullable|in:0,1',
                    'preference_hsn' => [
                        'nullable',
                        'in:0,1',
                        'required_if:preference_invoice_gst_complaint,1',
                        // function ($attribute, $value, $fail) {

                        //     if (request()->input('preference_invoice_gst_complaint') == 0 && $value == 1) {
                        //         $fail(__('validation.preference_hsn_required'));
                        //     }
                        // },

                    ],
                    'preference_hsn_invoice' => [
                        'nullable',
                        'in:0,1',
                        function ($attribute, $value, $fail) {

                            if (request()->input('preference_invoice_gst_complaint') == 1 && $value == 0) {
                                $fail(__('validation.preference_hsn_invoice_required'));
                            }
                            if (request()->input('preference_hsn') == 0 && $value == 1) {
                                $fail(__('preferences.preference_hsn_option_check'));
                            }
                        },
                    ],

                    // 'signature' => [
                    //     // ignore if $user->shop->signature is present
                    //     'nullable',
                    //     'image',
                    //     'mimes:jpeg,png,gif,jpg,heic',
                    //     'max:5120',
                    //     'required_if:preference_invoice_gst_complaint,1',
                    // ],

                    'signature' => [
                        'nullable',
                        'image',
                        'mimes:jpeg,png,gif,jpg,heic',
                        'max:5120',
                        function ($attribute, $value, $fail) use ($shop) {
                            if (
                                request()->input('preference_invoice_gst_complaint') == 1 &&
                                !$shop->signature &&
                                !request()->hasFile('signature')
                            ) {
                                $fail(__('validation.signaturre_required'));
                            }
                        },
                    ],

                    'gstin' => [
                        'nullable',
                        new NoScriptTag(),
                        new GstinRule(),
                        function ($attribute, $value, $fail) use ($request, $user) {

                            if (trim($value) === 'NA' || empty($value)) {
                                return;
                            }

                            $groceryDb = $user->module_type;

                            $existsInShops = DB::connection($groceryDb)
                                ->table('shops')
                                ->where('gstin', $value)
                                ->where('user_id', '!=', $user->user_id)
                                ->exists();

                            if ($existsInShops) {
                                $fail(__('validation.The GST Number has already registered'));
                            }
                        },
                    ],

                    'preference_purchase_price' => 'nullable|in:0,1',
                    // 'preference_category' => 'nullable|in:0,1',
                    'preference_sku' => 'nullable|in:0,1',

                    'preference_l1_category' => [
                        'nullable',
                        'in:0,1',
                        function ($attribute, $value, $fail) use ($preferences) {

                            $oldL1 = $preferences->preference_l1_category;
                            $oldL2 = $preferences->preference_l2_category;

                            $newL1 = (int) $value;
                            $newL2 = (int) request('preference_l2_category');

                            // CASE 1:
                            // L2 is being enabled, but L1 is not enabled
                            // (first enable L1)
                            if ($oldL2 == 0 && $newL2 == 1 && $newL1 == 0) {
                                $fail(__('validation.First enable L1 category before enabling L2 category'));
                                return;
                            }

                            // CASE 2:
                            // L2 is already enabled and user tries to disable L1
                            if ($oldL2 == 1 && $newL2 == 1 && $oldL1 == 1 && $newL1 == 0) {
                                $fail(__('validation.You cannot disable L1 category without disabling L2 category first'));
                                return;
                            }
                        },
                    ],

                    'preference_l2_category' => 'nullable|in:0,1',

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

                // $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();
                // $table_name = 'item_' . $user->username;

                // $user = User::find($user->user_id);
                // $shop = $user->shop;

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
                        'preference_invoice_gst_complaint' => $request->preference_invoice_gst_complaint ?? 0,
                        'preference_purchase_price' => $request->preference_purchase_price ?? 0,
                        // 'preference_category' => $request->preference_category ?? 0,
                        'preference_sku' => $request->preference_sku ?? 0,
                        'preference_l1_category' => $request->preference_l1_category ?? 0,
                        'preference_l2_category' => $request->preference_l2_category ?? 0,
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
                        'preference_invoice_gst_complaint' => $request->preference_invoice_gst_complaint ?? 0,
                        'preference_purchase_price' => $request->preference_purchase_price ?? 0,
                        // 'preference_category' => $request->preference_category ?? 0,
                        'preference_sku' => $request->preference_sku ?? 0,
                        'preference_l1_category' => $request->preference_l1_category ?? 0,
                        'preference_l2_category' => $request->preference_l2_category ?? 0,
                        // 'taxes' => json_encode($request->taxes),
                    ];

                    $result = DB::table('preferences')->insert($data);
                }


                if ($shop) {

                    $shop->gstin = $request->gstin ?? 'NA';

                    if ($request->hasFile('signature')) {
                        $signature = CommonHelpher::processPhoto($request, 'signature', 'shop/signature');
                        $shop->signature = $signature;
                    }

                    $shop->save();
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $result
                ], 200);


            } catch (\Exception $e) {
                report($e);
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process'),
                ], 500);
            }

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    // public function getUserPreferences()
    // {
    //     if (Auth::guard('api')->check()) {

    //         $user = Auth::guard('api')->user();

    //         $user = User::find($user->user_id);

    //         $preferenceData = null;

    //         if ($user->isAdmin == 1) {

    //             $preferenceId = DB::table('preferences')->where('user_id', $user->user_id)->first();

    //             if ($preferenceId) {
    //                 $preferenceData = DB::table('preferences')->find($preferenceId->id);
    //             }
    //         } else if ($user->isAdmin == 0) {

    //             $shop = DB::table('shops')->where('shop_id', $user->staff->addedBy)->first();


    //             $preferenceId = DB::table('preferences')->where('user_id', $shop->user_id)->first();

    //             if ($preferenceId) {
    //                 $preferenceData = DB::table('preferences')->find($preferenceId->id);
    //             }

    //         }

    //         $preferenceData['gstin'] = $preferenceData['gstin'] != 'NA' ? $preferenceData['gstin'] : "" ;


    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $preferenceData,
    //             'user' => $user,
    //             'shop' => $user->shop,
    //         ], 200);
    //     }
    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => __('validation.Unauthorized')
    //     ], 401);
    // }


    public function getUserPreferences()
    {
        if (Auth::guard('api')->check()) {

            $authUser = Auth::guard('api')->user();
            $user = User::find($authUser->user_id);

            if ($user->isAdmin == 1) {
                $userId = $user->user_id;
            } else {
                $shop = DB::table('shops')->where('shop_id', $user->staff->addedBy)->first();
                $userId = $shop->user_id ?? null;
            }

            $preferenceData = DB::table('preferences')
                ->where('user_id', $userId)
                ->first();

            if ($preferenceData) {
                $gstin = $user->shop->gstin ?? 'NA';
                $preferenceData->gstin = $gstin != 'NA' ? $gstin : '';
            }

            return response()->json([
                'status' => 'success',
                'data' => $preferenceData,
                'user' => $user,
                'shop' => $user->shop,
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

}
