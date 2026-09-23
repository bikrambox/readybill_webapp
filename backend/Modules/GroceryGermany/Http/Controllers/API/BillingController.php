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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

use Illuminate\Validation\Rule;
use DateTime;
use Kreait\Firebase\Factory;

use Modules\GroceryGermany\Entities\Billing;
use Modules\Authentication\Entities\User;
use Modules\GroceryGermany\Entities\Shop;

use Modules\GroceryGermany\Helpers\BillingHelpher;
use Modules\Authentication\Helpers\UserHelper;
use Modules\GroceryGermany\Helpers\TableNameHelper;
use Modules\GroceryGermany\Helpers\ItemHelpher;

use Modules\Core\Helpers\CommonHelpher;
use Modules\Core\Helpers\CountryHelpher;
use Modules\Core\Helpers\SendMessageHelper;

use Modules\GroceryGermany\Events\SaleCreated;
use Modules\GroceryGermany\Events\TransactionUpdated;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;

class BillingController extends Controller
{
    // protected $firebase;

    public function __construct()
    {

    }


    public function billing(Request $request)
    {

        // dd($request->all());

        // Start the database transaction
        DB::beginTransaction();

        try {

            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
                $item_table_name = TableNameHelper::getTableNameOfLoggedInUser();

                // check user preference setting
                // $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

                $preferences = UserHelper::getUserPreference($user);

                // dd($preferences);

                // Define the validation rules
                $rules = [
                    'itemList' => 'required|array|min:1',
                    // 'print'=> 'required|boolean',
                    'print' => 'required|string|in:0,1,true,false',
                    'isSendSms' => 'required|string|in:0,1,true,false',
                    'mobile' => [
                        function ($attribute, $value, $fail) use ($request) {
                            $shouldSendSms = in_array($request->isSendSms, ['1', 1, true, 'true'], true);

                            if ($shouldSendSms) {
                                $hasItems = is_array($request->itemList) && count($request->itemList) > 0;

                                // ❗ Fail if there are no items
                                if (!$hasItems) {
                                    $fail(__('billing_validation.Please add at least one item before proceeding'));
                                }

                                // ❗ Fail if mobile is empty
                                if (empty($value)) {
                                    $fail(__('validation.The mobile field is required'));
                                }
                            }
                        },
                        new PhoneNumber($request->country_code),
                    ],
                    'country_code' => [
                        'required_if:isSendSms,1,true',
                        new ValidCountryCode()
                    ],
                ];


                $customMessages = [
                    // 'mobile.required_if' => 'Mobile number is required when SMS sending is enabled.',
                    // 'mobile.required' => 'Mobile number is required.',
                    'mobile.numeric' => __('register_validation.numeric', ['attribute' => __('register_validation.attributes.mobile')]),
                    'mobile.digits' => __('register_validation.digits', ['attribute' => __('register_validation.attributes.mobile')]),
                    'country_code.required_if' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),
                ];

                // Perform validation
                $validate = Validator::make($request->all(), $rules, $customMessages);

                if ($validate->fails()) {
                    // Validation failed, return response with errors
                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    }
                }

                // Check if itemList is provided and not null
                if ($request->has('itemList') && is_array($request->itemList)) {
                    $rules = [];
                    foreach ($request->itemList as $index => $item) {
                        $rules["itemList.$index.itemId"] = 'required|numeric';
                        $rules["itemList.$index.itemName"] = 'required|string|max:255';
                        $rules["itemList.$index.quantity"] = 'required|numeric|gt:0';
                        $rules["itemList.$index.rate"] = 'required|numeric|gt:0';
                        $rules["itemList.$index.selectedUnit"] = 'required|string|max:255';
                        $rules["itemList.$index.amount"] = 'required|numeric|gt:0';
                        $rules["itemList.$index.isDelete"] = 'required|boolean';
                        $rules["itemList.$index.isRefund"] = 'required|boolean';
                        // $rules["itemList.$index.isRefund"] = 'required|string|in:0,1,true,false';
                        // Add more validation rules as needed for other fields of the item

                        // // Custom error messages
                        // $customMessages = [
                        //     "itemList.$index.itemId.required" => "The item ID at index $index is required.",
                        //     "itemList.$index.itemId.numeric" => "The item ID at index $index must be a number.",
                        //     "itemList.$index.itemName.required" => "The item name at index $index is required.",
                        //     "itemList.$index.itemName.string" => "The item name at index $index must be a string.",
                        //     "itemList.$index.itemName.max" => "The item name at index $index must not exceed 255 characters.",
                        //     "itemList.$index.quantity.gt" => "The quantity at index $index must be greater than zero.",
                        //     "itemList.$index.rate.gt" => "The rate at index $index must be greater than zero.",

                        //     'mobile.required_if' => __('register_validation.required', ['attribute' => __('register_validation.attributes.mobile')]),
                        //     'mobile.numeric' => __('register_validation.numeric', ['attribute' => __('register_validation.attributes.mobile')]),
                        //     'mobile.digits' => __('register_validation.digits', ['attribute' => __('register_validation.attributes.mobile')]),
                        //     'country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),


                        //     // Add custom messages for other rules as needed
                        // ];
                        // // $rules = array_merge($rules, $customMessages);

                        $itemMessages["itemList.$index.itemId.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.item_id'), 'index' => $index]);
                        $itemMessages["itemList.$index.itemId.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.item_id'), 'index' => $index]);

                        $itemMessages["itemList.$index.itemName.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.item_name'), 'index' => $index]);
                        $itemMessages["itemList.$index.itemName.string"] = __('billing_validation.string', ['attribute' => __('billing_validation.attributes.item_name'), 'index' => $index]);
                        $itemMessages["itemList.$index.itemName.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.item_name'), 'index' => $index, 'max' => 255]);

                        $itemMessages["itemList.$index.quantity.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index]);
                        $itemMessages["itemList.$index.quantity.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index]);
                        $itemMessages["itemList.$index.quantity.gt"] = __('billing_validation.gt', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index, 'gt' => 0]);
                        $itemMessages["itemList.$index.quantity.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index, 'max' => 100000]);

                        $itemMessages["itemList.$index.rate.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index]);
                        $itemMessages["itemList.$index.rate.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index]);
                        $itemMessages["itemList.$index.rate.gt"] = __('billing_validation.gt', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index, 'gt' => 0]);
                        $itemMessages["itemList.$index.rate.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index, 'max' => 100000]);

                        $itemMessages["itemList.$index.amount.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.amount'), 'index' => $index]);
                        $itemMessages["itemList.$index.amount.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.amount'), 'index' => $index]);
                        $itemMessages["itemList.$index.amount.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.amount'), 'index' => $index, 'max' => 10000000000]);



                    }

                    // Merge item-specific and global messages
                    $finalMessages = array_merge($customMessages, $itemMessages);

                    // Perform validation
                    $validate = Validator::make($request->all(), $rules, $finalMessages);

                    // if($validate->fails()){
                    //     return response()->json([
                    //         'status'=>'failed',
                    //         'message'=>'Validation Error!',
                    //         'data'=>$validate->errors(),
                    //     ],403);
                    // }

                    // Custom validation: Check quantity available in DB
                    $validate->after(function ($validator) use ($request, $item_table_name, $preferences) {

                        foreach ($validator->getData()['itemList'] as $index => $item) {
                            $itemId = $item['itemId'];
                            $quantity = $item['quantity'];
                            $selectedUnit = $item['selectedUnit'];

                            $itemFromDB = DB::table($item_table_name)->find((int) $itemId);

                            if (empty($itemFromDB)) {
                                $validator->errors()->add("itemList.$index.itemId", __('billing_validation.Item is not available in the database'));
                            }

                            // if ($itemFromDB && $itemFromDB->quantity != 'NA') {
                            if ($itemFromDB && $preferences->preference_quantity != 0) {
                                if ((int) $quantity > (int) $itemFromDB->quantity) {
                                    $validator->errors()->add("itemList.$index.quantity", __('billing_validation.Quantity not available in the database'));
                                }
                            }

                            if ($itemFromDB && strtolower($itemFromDB->short_unit) != strtolower($selectedUnit)) {
                                $validator->errors()->add("itemList.$index.selectedUnit", __('billing_validation.Item List of index') . ($index + 1) . " ".__('billing_validation.Unit does not matched'));
                            }
                        }

                    });

                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    }
                }

                $shop_id = 0;
                if ($user->isAdmin == 1) {
                    $shop_id = $user->shop->shop_id;
                } else if ($user->isAdmin == 0) {
                    $staff = $user->staff;
                    $shop = Shop::find($staff->addedBy);
                    $shop_id = $shop->shop_id;
                }

                // fetch last invoice
                $invoice_count = DB::table('billing')
                    ->where('shop_id', $shop_id)
                    ->select('invoice_count')->orderBy('created_at', 'desc')->first();

                if ($invoice_count == null) {
                    $invoice_count = 100;
                } else {
                    $invoice_count = $invoice_count->invoice_count;
                }
                // fetch last invoice


                // generate invoice number 
                $invoice_number = BillingHelpher::generateInvoiceNumber($invoice_count + 1);
                // generate invoice number 


                // update stock quantity
                $itemList = $request->itemList;
                $count = 0;
                foreach ($request->itemList as $item) {

                    // dd($item['quantity']);

                    $itemFromDB = DB::table($item_table_name)->find((int) $item['itemId']);
                    // dd($itemFromDB->quantity);
                    if ($preferences->preference_quantity != 0) {
                        if ((float) $item['quantity'] <= (float) $itemFromDB->quantity) {

                            $updatedQuantity = (float) $itemFromDB->quantity - (float) $item['quantity'];
                            DB::table($item_table_name)->where('id', $item['itemId'])->update([
                                'quantity' => $updatedQuantity
                            ]);
                        }

                    }


                    $itemList[$count]['mrp'] = $itemFromDB->mrp;
                    // $itemList[$count]['hsn'] = $itemFromDB->hsn;


                    // calculate tax on each product
                    if (($itemFromDB->tax1 != '') && ($itemFromDB->tax1 != 'NA') && ($itemFromDB->rate1 != 'NA')) {
                        $itemList[$count]['tax1']['name'] = $itemFromDB->tax1;
                        $itemList[$count]['tax1']['percent'] = $itemFromDB->rate1;
                        $itemList[$count]['tax1']['amount'] = BillingHelpher::taxCalculation((float) $itemFromDB->sale_price, (float) $item['quantity'], (float) $itemFromDB->rate1);
                    }
                    // if (($itemFromDB->tax2 != '') && ($itemFromDB->tax2 != 'NA') && ($itemFromDB->rate2 != 'NA')) {
                    //     $itemList[$count]['tax2']['name'] = $itemFromDB->tax2;
                    //     $itemList[$count]['tax2']['percent'] = $itemFromDB->rate2;
                    //     $itemList[$count]['tax2']['amount'] = BillingHelpher::taxCalculation((float) $itemFromDB->sale_price, (float) $item['quantity'], (float) $itemFromDB->rate2);
                    // }

                    $count++;
                    // calculate tax on each product


                    // Delete item from cart
                    $response = ItemHelpher::deleteItemFromCartUsingItemID($itemFromDB->id, $user->user_id, 'sell');
                    // Delete item from cart


                }
                // update stock quantity

                // $user_id = $user->user_id;

                // $shop_id = 0;
                // if ($user->isAdmin == 1) {
                //     $shop_id = $user->shop->shop_id;
                // } else if ($user->isAdmin == 0) {
                //     $staff = $user->staff;
                //     $shop = Shop::find($staff->addedBy);
                //     $shop_id = $shop->shop_id;
                // }


                // Convert array to JSON
                // $item_list = json_encode($request->itemList);
                $item_list = json_encode($itemList);

                $total_price = $request->grand_total;
                // $total_price = round($total_price, ($total_price - floor($total_price)) >= 0.5 ? 0 : 0, ($total_price - floor($total_price)) >= 0.5 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);

                $payment_status = 0;
                if ($preferences->preference_transaction_mark_as_paid) {
                    $payment_status = 1;
                } 
                
                // else if ($preferences->preference_transaction_mark_as_unpaid) {
                //     $payment_status = 0;
                // }

                $data = [
                    'invoice_number' => $invoice_number,
                    'shop_id' => $shop_id,
                    'user_id' => $user->user_id,
                    'item_list' => $item_list,
                    'total_price' => $total_price,
                    'invoice_count' => $invoice_count + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'payment_status' => $payment_status,
                ];


                $result = DB::table('billing')->insert($data);

                // $billing = DB::table('billing')->where('invoice_number', $invoice_number)->first();

                // $billing = Billing::where('invoice_number',$invoice_number)->first();

                $billing = Billing::where('invoice_number', $invoice_number)->first();



                // Delete data from cache
                Cache::forget(env('CACHE_KEY_PREFIX') . 'transactions_' . $user->mobile);

                // Commit the transaction after all successful operations
                DB::commit();

                // Broadcast the sale event
                // broadcast(new \App\Events\SaleCreated($billing))->toOthers();

                // \Log::info('Preparing to broadcast SaleCreated event for billing ID: ' . $billing->shop_id);
                broadcast(new SaleCreated($billing))->toOthers();
                // \Log::info('SaleCreated event broadcasted successfully');


                $data = BillingHelpher::billingResponse($user, $billing->id);

                // print bill if print value is 1
                if (($request->print == "1") || ($request->print == "true")) {

                    // $filename = BillingHelpher::generatePdf($data);

                    $response = [
                        'status' => 'success',
                        'message' => __('validation.New Bill Successfully created'),
                        'data' => $data,
                        'bill_id' => $billing->id,
                        // 'filename' => $filename
                    ];
                    return response()->json($response, 200);
                }
                // print bill if print value is 1


                // Send SMS
                if (($request->isSendSms == "1") || ($request->isSendSms == "true")) {

                    $country_details = CountryHelpher::getCountryJson($request->country_code);

                    $dial_code = $country_details['dial_code'];


                    // GENERATE INVOICE URL
                    $encryptedBillIdResponse = CommonHelpher::encryptBillId($billing->id, $user->user_id);
                    // GENERATE INVOICE URL


                    $data = [
                        'mobiles' => $dial_code . $request->mobile,
                        'var' => $encryptedBillIdResponse['code'],
                        'short_url' => 0,
                    ];

                    if ($encryptedBillIdResponse['status_code'] == 200) {
                        // SEND SMS
                        SendMessageHelper::send('share_invoice', $data);
                    }

                    $response = [
                        'status' => 'success',
                        'message' => __('validation.Bill has been shared successfully'),
                        'data' => $data,
                        'bill_id' => $billing->id,
                        'code' => $encryptedBillIdResponse['code'],
                        'url' => $encryptedBillIdResponse['url'],
                    ];
                    return response()->json($response, 200);
                }
                // Send SMS

                $response = [
                    'status' => 'success',
                    'message' => __('validation.New Bill Successfully created'),
                    'data' => $data
                ];
                return response()->json($response, 200);

            }
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);

        } catch (\Exception $e) {
            // Rollback the transaction on any error
            DB::rollBack();

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => __('validation.unable_to_process'),
                'status code' => '0'
            ], 500);
        }
    }

    // --------------------------------------------------------------------------- QUICK SELL ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- REFUND ---------------------------------------------------------------------------
    public function refund(Request $request)
    {

        // Start the database transaction
        DB::beginTransaction();
        try {

            if (Auth::guard('api')->check()) {


                $user = Auth::guard('api')->user();

                $item_table_name = TableNameHelper::getTableNameOfLoggedInUser();


                // check user preference setting
                // $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();

                $preferences = UserHelper::getUserPreference($user);

                // dd($preferences);

                // Define the validation rules
                $rules = [
                    'itemList' => 'required|array|min:1',
                    // 'print'=> 'required|boolean',
                    'print' => 'required|string|in:0,1,true,false',
                    'isSendSms' => 'required|string|in:0,1,true,false',

                    'mobile' => [
                        function ($attribute, $value, $fail) use ($request) {
                            $shouldSendSms = in_array($request->isSendSms, ['1', 1, true, 'true'], true);

                            if ($shouldSendSms) {
                                $hasItems = is_array($request->itemList) && count($request->itemList) > 0;

                                // ❗ Fail if there are no items
                                if (!$hasItems) {
                                    $fail(__('billing_validation.Please add at least one item before proceeding'));
                                }

                                // ❗ Fail if mobile is empty
                                if (empty($value)) {
                                    $fail(__('validation.The mobile field is required'));
                                }
                            }
                        },
                        new PhoneNumber($request->country_code),
                    ],
                    'country_code' => [
                        'required_if:isSendSms,1,true',
                        new ValidCountryCode()
                    ],

                ];


                $customMessages = [
                    // 'mobile.required_if' => 'Mobile number is required when SMS sending is enabled.',
                    // 'mobile.required' => 'Mobile number is required.',
                    'mobile.numeric' => __('register_validation.numeric', ['attribute' => __('register_validation.attributes.mobile')]),
                    'mobile.digits' => __('register_validation.digits', ['attribute' => __('register_validation.attributes.mobile')]),
                    'country_code.required_if' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),
                ];


                // Perform validation
                $validate = Validator::make($request->all(), $rules, $customMessages);

                if ($validate->fails()) {
                    // Validation failed, return response with errors
                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    }
                }

                // Check if itemList is provided and not null
                if ($request->has('itemList') && is_array($request->itemList)) {
                    $rules = [];
                    foreach ($request->itemList as $index => $item) {
                        $rules["itemList.$index.itemId"] = 'required|numeric';
                        $rules["itemList.$index.itemName"] = 'required|string|max:255';
                        $rules["itemList.$index.quantity"] = 'required|numeric|';
                        $rules["itemList.$index.rate"] = 'required|numeric|';
                        $rules["itemList.$index.selectedUnit"] = 'required|string|max:255';
                        $rules["itemList.$index.amount"] = 'required|numeric|';
                        $rules["itemList.$index.isDelete"] = 'required|boolean';
                        $rules["itemList.$index.isRefund"] = 'required|boolean';
                        // Add more validation rules as needed for other fields of the item

                        // Custom error messages
                        $itemMessages["itemList.$index.itemId.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.item_id'), 'index' => $index]);
                        $itemMessages["itemList.$index.itemId.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.item_id'), 'index' => $index]);

                        $itemMessages["itemList.$index.itemName.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.item_name'), 'index' => $index]);
                        $itemMessages["itemList.$index.itemName.string"] = __('billing_validation.string', ['attribute' => __('billing_validation.attributes.item_name'), 'index' => $index]);
                        $itemMessages["itemList.$index.itemName.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.item_name'), 'index' => $index, 'max' => 255]);

                        $itemMessages["itemList.$index.quantity.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index]);
                        $itemMessages["itemList.$index.quantity.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index]);
                        $itemMessages["itemList.$index.quantity.gt"] = __('billing_validation.gt', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index, 'gt' => 0]);
                        $itemMessages["itemList.$index.quantity.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.quantity'), 'index' => $index, 'max' => 100000]);

                        $itemMessages["itemList.$index.rate.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index]);
                        $itemMessages["itemList.$index.rate.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index]);
                        $itemMessages["itemList.$index.rate.gt"] = __('billing_validation.gt', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index, 'gt' => 0]);
                        $itemMessages["itemList.$index.rate.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.rate'), 'index' => $index, 'max' => 100000]);

                        $itemMessages["itemList.$index.amount.required"] = __('billing_validation.required', ['attribute' => __('billing_validation.attributes.amount'), 'index' => $index]);
                        $itemMessages["itemList.$index.amount.numeric"] = __('billing_validation.numeric', ['attribute' => __('billing_validation.attributes.amount'), 'index' => $index]);
                        $itemMessages["itemList.$index.amount.max"] = __('billing_validation.max', ['attribute' => __('billing_validation.attributes.amount'), 'index' => $index, 'max' => 10000000000]);

                        // $rules = array_merge($rules, $customMessages);
                    }

                    // Merge item-specific and global messages
                    $finalMessages = array_merge($customMessages, $itemMessages);

                    // Perform validation
                    $validate = Validator::make($request->all(), $rules, $finalMessages);
                    
                    // Custom validation: Check quantity available in DB
                    $validate->after(function ($validator) use ($request, $item_table_name, $preferences) {
                        foreach ($validator->getData()['itemList'] as $index => $item) {
                            $itemId = $item['itemId'];
                            $quantity = $item['quantity'];
                            $selectedUnit = $item['selectedUnit'];
                            $isRefund = $item['isRefund'];

                            $itemFromDB = DB::table($item_table_name)->find((int) $itemId);


                            if (empty($itemFromDB)) {
                                $validator->errors()->add("itemList.$index.itemId", __('billing_validation.Item is not available in the database'));
                            }

                            if ($itemFromDB && $isRefund == 0 && $preferences->preference_quantity != 0) {
                                if ((int) $quantity > (int) $itemFromDB->quantity) {
                                    $validator->errors()->add("itemList.$index.quantity", __('billing_validation.Quantity not available in the database'));
                                }
                            }

                            if ($itemFromDB && strtolower($itemFromDB->short_unit) != strtolower($selectedUnit)) {
                                $validator->errors()->add("itemList.$index.selectedUnit", __('billing_validation.Item List of index') . ($index + 1) . " " . __('billing_validation.Unit does not matched'));
                            }
                        }
                    });

                    if ($validate->fails()) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Validation Error'),
                            'data' => $validate->errors(),
                        ], 403);
                    }
                }

                // fetch last invoice
                $invoice_count = DB::table('billing')->select('invoice_count')->orderBy('created_at', 'desc')->first();

                if ($invoice_count == null) {
                    $invoice_count = 100;
                } else {
                    $invoice_count = $invoice_count->invoice_count;
                }
                // fetch last invoice


                // generate invoice number 
                $invoice_number = BillingHelpher::generateInvoiceNumber($invoice_count + 1);
                // generate invoice number 


                // update stock quantity
                $itemList = $request->itemList;
                $count = 0;
                foreach ($request->itemList as $item) {

                    // dd($item['quantity']);

                    $itemFromDB = DB::table($item_table_name)->find((int) $item['itemId']);
                    // dd($itemFromDB->quantity);
                    // if ($itemFromDB->quantity != 'NA') {
                    if ($itemFromDB && $preferences->preference_quantity != 0) {


                        if ($item['isRefund'] == 1) {

                            $updatedQuantity = (float) $itemFromDB->quantity + (float) $item['quantity'];

                            DB::table($item_table_name)->where('id', $item['itemId'])->update([
                                'quantity' => $updatedQuantity
                            ]);


                        } else if ($item['isRefund'] == 0) {
                            if ((float) $item['quantity'] <= (float) $itemFromDB->quantity) {
                                $updatedQuantity = (float) $itemFromDB->quantity - (float) $item['quantity'];

                                DB::table($item_table_name)->where('id', $item['itemId'])->update([
                                    'quantity' => $updatedQuantity
                                ]);

                            }
                        }


                    }


                    $itemList[$count]['mrp'] = $itemFromDB->mrp;
                    // $itemList[$count]['hsn'] = $itemFromDB->hsn;


                    // calculate tax on each product
                    if (($itemFromDB->tax1 != '') && ($itemFromDB->tax1 != 'NA') && ($itemFromDB->rate1 != 'NA')) {
                        $itemList[$count]['tax1']['name'] = $itemFromDB->tax1;
                        $itemList[$count]['tax1']['percent'] = $itemFromDB->rate1;
                        $itemList[$count]['tax1']['amount'] = BillingHelpher::taxCalculation((float) $itemFromDB->sale_price, (float) $item['quantity'], (float) $itemFromDB->rate1);
                    }
                    // if (($itemFromDB->tax2 != '') && ($itemFromDB->tax2 != 'NA') && ($itemFromDB->rate2 != 'NA')) {
                    //     $itemList[$count]['tax2']['name'] = $itemFromDB->tax2;
                    //     $itemList[$count]['tax2']['percent'] = $itemFromDB->rate2;
                    //     $itemList[$count]['tax2']['amount'] = BillingHelpher::taxCalculation((float) $itemFromDB->sale_price, (float) $item['quantity'], (float) $itemFromDB->rate2);
                    // }
                    $count++;
                    // calculate tax on each product


                    // Delete item from cart
                    $response = ItemHelpher::deleteItemFromCartUsingItemID($itemFromDB->id, $user->user_id, 'refund');
                    // Delete item from cart

                }
                // update stock quantity

                $shop_id = 0;
                if ($user->isAdmin == 1) {
                    $shop_id = $user->shop->shop_id;
                } else if ($user->isAdmin == 0) {
                    $staff = $user->staff;
                    $shop = Shop::find($staff->addedBy);

                    $shop_id = $shop->shop_id;
                }

                // Convert array to JSON
                // $item_list = json_encode($request->itemList);
                $item_list = json_encode($itemList);

                $total_price = $request->grand_total;
                // $total_price = round($total_price, ($total_price - floor($total_price)) >= 0.5 ? 0 : 0, ($total_price - floor($total_price)) >= 0.5 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);

                $payment_status = 0;
                if ($preferences->preference_transaction_mark_as_paid) {
                    $payment_status = 1;
                } 
                
                // else if ($preferences->preference_transaction_mark_as_unpaid) {
                //     $payment_status = 0;
                // }

                $data = [
                    'invoice_number' => $invoice_number,
                    'shop_id' => $shop_id,
                    'user_id' => $user->user_id,
                    'item_list' => $item_list,
                    'total_price' => $total_price,
                    'invoice_count' => $invoice_count + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'payment_status' => $payment_status,
                ];

                $result = DB::table('billing')->insert($data);

                // $billing = DB::table('billing')->where('invoice_number', $invoice_number)->first();

                $billing = Billing::where('invoice_number', $invoice_number)->first();

                // Delete data from cache
                Cache::forget(env('CACHE_KEY_PREFIX') . 'transactions_' . $user->mobile);


                // Commit the transaction after all successful operations
                DB::commit();


                // Broadcast the sale event
                // broadcast(new \App\Events\SaleCreated($billing))->toOthers();

                // \Log::info('Preparing to broadcast SaleCreated event for billing ID: ' . $billing->shop_id);
                broadcast(new SaleCreated($billing))->toOthers();
                // \Log::info('SaleCreated event broadcasted successfully');


                $data = BillingHelpher::billingResponse($user, $billing->id);

                // print bill if print value is 1
                if (($request->print == "1") || ($request->print == "true")) {


                    // $filename = BillingHelpher::generatePdf($data);

                    $response = [
                        'status' => 'success',
                        'message' => __('validation.New Bill Successfully created'),
                        'data' => $data,
                        'bill_id' => $billing->id,
                        // 'filename' => $filename
                    ];
                    return response()->json($response, 200);
                }
                // print bill if print value is 1

                // Send SMS
                if (($request->isSendSMS == "1") || ($request->isSendSMS == "true")) {

                    $country_details = CountryHelpher::getCountryJson($request->country_code);

                    $dial_code = $country_details['dial_code'];


                    // GENERATE INVOICE URL
                    $encryptedBillIdResponse = CommonHelpher::encryptBillId($billing->id, $user->user_id);
                    // GENERATE INVOICE URL


                    $data = [
                        'mobiles' => $dial_code . $request->mobile,
                        'var' => $encryptedBillIdResponse['code'],
                        'short_url' => 0,
                    ];

                    if ($encryptedBillIdResponse['status_code'] == 200) {
                        // SEND SMS
                        SendMessageHelper::send('share_invoice', $data);
                    }

                    $response = [
                        'status' => 'success',
                        'message' => __('validation.Bill has been shared successfully'),
                        'data' => $data,
                        'bill_id' => $billing->id,
                        'code' => $encryptedBillIdResponse['code'],
                        'url' => $encryptedBillIdResponse['url'],
                    ];
                    return response()->json($response, 200);
                }
                // Send SMS

                $response = [
                    'status' => 'success',
                    'message' => __('validation.New Bill Successfully created'),
                    'data' => $result
                ];
                return response()->json($response, 200);

            }
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);

        } catch (\Exception $e) {
            // Rollback the transaction on any error
            DB::rollBack();

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => __('validation.unable_to_process'),
                'status code' => '0'
            ], 500);
        }
    }
    // --------------------------------------------------------------------------- REFUND ---------------------------------------------------------------------------

    public function transactions(Request $request)
    {
        if (Auth::guard('api')->check()) {
            try {
                $draw = $request->input('draw', 0);
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);
                $sortColumnIndex = $request->input('order.0.column', 0);
                $sortDirection = $request->input('order.0.dir', 'asc');
                $searchValue = $request->input('search.value', '');
                $filterOption = $request->input('filter_option', 'invoice_number');
                $paymentStatus = $request->input('payment_status', 0);
                $dateFrom = $request->input('date_from'); // Expected format: dd/mm/yyyy
                $dateTo = $request->input('date_to');     // Expected format: dd/mm/yyyy

                // Validate date inputs
                if (!empty($dateFrom) && !empty($dateTo)) {
                    $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
                    $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
                    $currentDate = new DateTime();

                    if (!$dateFromFormatted || !$dateToFormatted) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Invalid date format. Use dd/mm/yyyy')
                        ], 400);
                    }

                    if ($dateFromFormatted > $currentDate || $dateToFormatted > $currentDate) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Future dates are not allowed')
                        ], 400);
                    }

                    $interval = $dateFromFormatted->diff($dateToFormatted);
                    $months = ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);
                    if ($months > 6) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Date range cannot exceed 6 months')
                        ], 400);
                    }
                }

                $user = Auth::guard('api')->user();
                $query = DB::table('billing');

                if ($user->isAdmin == 1) {
                    $shopId = $user->shop->shop_id;

                    $query->select('billing.*')
                        ->selectRaw('COALESCE(staff.name, shops.name) AS user_name')
                        ->leftJoin('shops', 'billing.shop_id', '=', 'shops.shop_id')
                        ->leftJoin('staff', function ($join) use ($shopId) {
                            $join->on('billing.user_id', '=', 'staff.user_id')
                                ->where('staff.addedBy', '=', $shopId);
                        })
                        ->where('billing.shop_id', $shopId);
                } else {
                    $shopId = $user->staff->addedBy;
                    $userId = $user->user_id;

                    $query->select('billing.*', 'staff.name AS user_name')
                        ->leftJoin('shops', 'billing.shop_id', '=', 'shops.shop_id')
                        ->leftJoin('staff', function ($join) use ($shopId, $userId) {
                            $join->where('staff.addedBy', '=', $shopId)
                                ->where('staff.user_id', '=', $userId);
                        })
                        ->where('billing.user_id', $userId)
                        ->where('staff.addedBy', $shopId);
                }

                // dd($paymentStatus);

                // Apply payment status filter
                if (isset($paymentStatus)) {
                    // dd($paymentStatus);
                    $query->where('billing.payment_status', (int) $paymentStatus);
                }


                // Apply search conditions
                if (!empty($searchValue)) {
                    if ($filterOption == 'invoice_number') {
                        $query->where('billing.invoice_number', 'LIKE', '%' . $searchValue . '%');
                    }

                    // elseif ($filterOption == 'date') {
                    //     $searchDate = DateTime::createFromFormat('d/m/Y', str_replace('/', '-', $searchValue));
                    //     if ($searchDate) {
                    //         $query->whereDate('billing.created_at', $searchDate->format('Y-m-d'));
                    //     }
                    // } 
                    elseif ($filterOption == 'date') {
                        $searchValue = str_replace('-', '/', $searchValue);
                        $searchDate = DateTime::createFromFormat('d/m/Y', $searchValue);

                        if ($searchDate) {
                            $query->whereDate('billing.created_at', $searchDate->format('Y-m-d'));
                        }
                    } elseif ($filterOption == 'user') {
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('staff.name', 'LIKE', '%' . $searchValue . '%')
                                ->orWhere('shops.name', 'LIKE', '%' . $searchValue . '%');
                        });
                    } elseif ($filterOption == 'total' && is_numeric($searchValue)) {
                        $query->where('billing.total_price', $searchValue);
                    }
                }

                // Apply date range filter
                if (!empty($dateFrom) && !empty($dateTo)) {
                    $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom)->setTime(0, 0, 0);
                    $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo)->setTime(23, 59, 59);
                    $query->whereBetween('billing.created_at', [
                        $dateFromFormatted->format('Y-m-d H:i:s'),
                        $dateToFormatted->format('Y-m-d H:i:s')
                    ]);
                }

                // Count total records
                $totalRecords = $query->count();

                // Apply sorting
                $columns = ['invoice_number', 'total_price', 'created_at'];
                $sortColumn = $columns[$sortColumnIndex] ?? 'invoice_number';
                $query->orderBy($sortColumn, $sortDirection);

                // Apply pagination
                $items = $query->skip($start)->take($length)->get();


                // Transform the data to match frontend expectations
                $formattedItems = $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'shop_id' => $item->shop_id,
                        'user_id' => $item->user_id,
                        'invoice_number' => $item->invoice_number,
                        'invoice_count' => $item->invoice_count,
                        'item_list' => $item->item_list, // Keep as string or parse with json_decode($item->item_list, true) if needed
                        'total_price' => $item->total_price,
                        // 'payment_status' => $item->payment_status == 0 ? 'unpaid' : 'paid', // Convert 0/1 to unpaid/paid
                        'payment_status' => $item->payment_status,
                        'created_at' => (new DateTime($item->created_at))->format('Y-m-d H:i:s'),
                        'updated_at' => (new DateTime($item->updated_at))->format('Y-m-d H:i:s'),
                        'user_name' => $item->user_name,
                        // 'DT_RowId' => 'row_' . $item->id, // Optional, not needed with rowId: 'id' in frontend
                    ];
                });

                return response()->json([
                    'draw' => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    // 'data' => $items,
                    'data' => $formattedItems,
                    'filter_option' => $filterOption,
                    'searchValue' => $searchValue
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

    // --------------------------------------------------------------------------- ALL TRANSACTIONS ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- TRANSACTION BY ID ---------------------------------------------------------------------------
    public function getTransactionByID($id)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $transactions = DB::table('billing')->find($id);

            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ], 200);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
    // --------------------------------------------------------------------------- TRANSACTION BY ID ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- UPDATE TRANSACTION ---------------------------------------------------------------------------
    public function updateTransaction(Request $request)
    {
        if (Auth::guard('api')->check()) {


            // Define custom validation rules for each item in the itemList array
            $rules = [];
            if ($request->itemList) {
                foreach ($request->itemList as $key => $item) {
                    $rules["itemList.{$key}.itemName"] = 'required|string';
                    $rules["itemList.{$key}.quantity"] = 'required|numeric|gt:0';
                    $rules["itemList.{$key}.rate"] = 'required|numeric|min:0'; // Rate should be numeric and >= 0
                    $rules["itemList.{$key}.amount"] = 'required|numeric|min:0'; // Amount should be numeric and >= 0
                }
            }

            // Validate the request data
            $validate = Validator::make($request->all(), $rules);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }

            $user = Auth::guard('api')->user();
            // $item_table_name = 'item_' . $user->username;

            $item_table_name = TableNameHelper::getTableNameOfLoggedInUser();


            // update stock quantity
            $itemList = $request->itemList;
            $count = 0;
            foreach ($request->itemList as $item) {

                // dd($item['quantity']);

                $itemFromDB = DB::table($item_table_name)->find((int) $item['itemId']);
                // dd($itemFromDB->quantity);
                if (($itemFromDB->quantity != 'NA') && ($item['isDelete'] == 1)) {

                    $updatedQuantity = (float) $itemFromDB->quantity + (float) $item['quantity'];
                    DB::table($item_table_name)->where('id', $item['itemId'])->update([
                        'quantity' => $updatedQuantity
                    ]);
                }


                if ($item['isDelete'] == 1) {
                    // remove item from the list
                    unset($itemList[$count]);
                    // remove item from the list
                }


                // calculate tax on each product
                if (($itemFromDB->tax1 != '') && ($itemFromDB->tax1 != 'NA') && ($itemFromDB->rate1 != 'NA')) {
                    $item[$count]['tax1']['name'] = $itemFromDB->tax1;
                    $item[$count]['tax1']['percent'] = $itemFromDB->rate1;
                    $item[$count]['tax1']['amount'] = BillingHelpher::taxCalculation((float) $itemFromDB->sale_price, (float) $item['quantity'], (float) $itemFromDB->rate1);
                }
                // if (($itemFromDB->tax2 != '') && ($itemFromDB->tax2 != 'NA') && ($itemFromDB->rate2 != 'NA')) {
                //     $item[$count]['tax2']['name'] = $itemFromDB->tax2;
                //     $item[$count]['tax2']['percent'] = $itemFromDB->rate2;
                //     $item[$count]['tax2']['amount'] = BillingHelpher::taxCalculation((float) $itemFromDB->sale_price, (float) $item['quantity'], (float) $itemFromDB->rate2);
                // }

                // calculate tax on each product
                $count++;
            }
            // update stock quantity

            $user_id = $user->user_id;
            // Convert array to JSON
            // $item_list = json_encode($request->itemList);
            $item_list = json_encode($itemList);
            $total_price = $request->grand_total;

            $billingData = DB::table('billing')->find($request->billing_id);

            $data = [
                'invoice_number' => $billingData->invoice_number,
                'user_id' => $user_id,
                'item_list' => $item_list,
                'total_price' => $total_price,
                'invoice_count' => $billingData->invoice_count
            ];


            // $result = DB::table('billing')->insert($data);

            $result = DB::table('billing')->where('id', $request->billing_id)->update($data);

            $billingData = DB::table('billing')->find($request->billing_id);
            if ($billingData->total_price == 0) {
                DB::table('billing')->where('id', $request->billing_id)->delete();
            }


            $response = [
                'status' => 'success',
                'message' => __('validation.Bill Successfully Updated'),
                'data' => $result
            ];
            return response()->json($response, 200);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
    // --------------------------------------------------------------------------- UPDATE TRANSACTION ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- GENERATE PDF ---------------------------------------------------------------------------
    public function billGenerate($id)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $data = BillingHelpher::billingResponse($user, $id);

            $filename = BillingHelpher::generatePdf($data);

            return response()->json([
                'status' => 'success',
                'filename' => $filename,
                'path' => public_path('storage/bill/'),
            ], 200);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
    // --------------------------------------------------------------------------- GENERATE PDF ---------------------------------------------------------------------------

    // --------------------------------------------------------------------------- GENERATE PDF FOR APP ---------------------------------------------------------------------------
    public function generateBillPdf($id)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $data = BillingHelpher::billingResponse($user, $id);

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
    // --------------------------------------------------------------------------- GENERATE PDF FOR APP ---------------------------------------------------------------------------


    // --------------------------------------------------------------------------- MARK AS PAID THE TRANSACTION ---------------------------------------------------------------------------
    public function markAsPaid($id)
    {
        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            if ($user->isAdmin == 1) {

                $billing = Billing::find($id);
                $billing->payment_status = 1;
                $billing->save();

                broadcast(new TransactionUpdated($billing))->toOthers();

                return response()->json([
                    'status' => 'success',
                ], 200);

            } else {
                return response()->json([
                    "error" => __("validation.User dont' have permission to access"),
                    "status" => 0,
                ], 403);
            }

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }
    // --------------------------------------------------------------------------- MARK AS PAID THE TRANSACTION ---------------------------------------------------------------------------

}
