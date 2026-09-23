<?php

namespace Modules\Core\Http\Controllers\API;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\SendMessageHelper;
use Modules\Core\Helpers\ResponseHelper;
use Modules\Core\Helpers\CommonHelpher;
use Modules\Core\Helpers\CountryHelpher;
use Illuminate\Support\Facades\Log;

use Validator;
use Auth;


use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;

class ShareInvoiceController extends Controller
{
    public function sendInvoiceInSMS(Request $request)
    {
        if (Auth::guard('api')->check()) {

            try {

                $validate = Validator::make($request->all(), [
                    'bill_id' => 'required',

                    'mobile' => [
                        'required',
                        new PhoneNumber($request->country_code),
                    ],
                    'country_code' => [
                        'required',
                        new ValidCountryCode()
                    ],

                ], [
                    'bill_id.required' => __('invoice.required', ['attribute' => __('invoice.attributes.bill_id')]),
                    'mobile.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.mobile')]),
                    'country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),
                ]);

                if ($validate->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => __('validation.Validation Error'),
                        'data' => $validate->errors(),
                    ], 403);
                }

                $user = Auth::guard('api')->user();

                $country_details = CountryHelpher::getCountryJson($request->country_code);
                $dial_code = $country_details['dial_code'];


                // GENERATE INVOICE URL
                $response = CommonHelpher::encryptBillId($request->bill_id, $user->user_id);
                // GENERATE INVOICE URL

                $data = [
                    'mobiles' => $dial_code . $request->mobile,
                    'invoice_id' => $response['code'],
                    'short_url'=>0,
                ];


                if($response['status_code'] == 200){
                    // SEND SMS
                    SendMessageHelper::send('share_invoice', $data);   
                }
                else{
                    throw new \Exception('Failed to generate encrypted invoice URL');
                }

                return ResponseHelper::responseFn(1, 200, 'SMS Invoice Successfull Send', $response);

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
    
}
