<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\DB;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use Modules\Core\Entities\OTP;

use Modules\Core\Helpers\ResponseHelper;
use Modules\Core\Helpers\SendMessageHelper;
use Modules\Core\Helpers\CountryHelpher;

use Modules\Core\Rules\PhoneNumber;

class OTPHelper
{

    public static function generateOtp($request)
    {

        $dial_code = CountryHelpher::getCountryDialCode($request->country_code);

        OTPHelper::byPassPhoneNumber($request->mobile, $dial_code);

        // Constants for OTP expiry and maximum attempts
        $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
        $OTP_Count = env('OTP_COUNT');   // Maximum of 3 attempts

        // Check if an OTP already exists for the phone number
        $otp = Otp::where('mobile', $request->mobile)->first();

        if ($otp) {
            // Check if OTP is still valid (not expired)
            $createdTime = Carbon::parse($otp->created_at);
            $expiryTime = $createdTime->addMinutes($OTP_Expiry);

            if (Carbon::now()->lt($expiryTime)) {
                // OTP is not expired, check if attempts exceeded
                if ($otp->count >= $OTP_Count) {

                    $data = [
                        'retry_after' => $OTP_Expiry . ' '.__('validation.minutes'),
                    ];
                    return ResponseHelper::responseFn(0, 429, __('otp.otp_process_error'), $data);

                }

                // --------------------------------- SEND SMS -----------------------------------
                $data = [
                    'mobiles' => $otp->dial_code . $otp->mobile,
                    'OTP' => $otp->code,
                ];

                SendMessageHelper::send($request->sms_type, $data);
                // --------------------------------- SEND SMS -----------------------------------

                // Increment the count if the OTP is still valid
                $otp->increment('count');
                $data = [
                    'mobile' => $otp->mobile,
                    'status' => 1,
                ];
                return ResponseHelper::responseFn(1, 200, __('otp.successfully_send_otp'), $data);

            } else {
                // If the OTP is expired, delete the previous entry
                $otp->delete();  // Delete the expired OTP entry
            }
        }

        // Generate a new OTP since the previous one is either invalid or deleted
        $newOtpCode = rand(100000, 999999);
        $otp = Otp::create([
            'dial_code' => $dial_code,
            'mobile' => $request->mobile,
            'code' => $newOtpCode,
            'count' => 1, // Initialize count
        ]);

        // --------------------------------- SEND SMS -----------------------------------
        $data = [
            'mobiles' => $otp->dial_code . $otp->mobile,
            'OTP' => $otp->code,
        ];

        SendMessageHelper::send($request->sms_type, $data);
        // --------------------------------- SEND SMS -----------------------------------

        $data = [
            'mobile' => $otp->mobile,
            'status' => 1,
        ];
        return $data;
        // return ResponseHelper::responseFn(1, 200, 'An OTP has been sent to your mobile. Please check.', $data);

    }

    public static function verifyOtp($request)
    {

        // Constants for OTP expiry and maximum attempts
        $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
        $OTP_Count = env('OTP_COUNT');   // Maximum of 3 attempts

        // Retrieve the OTP record for the phone number
        $otp = Otp::where('mobile', $request->mobile)->first();

        // Check if the OTP record exists
        if (!$otp) {
            $data = [];
            return ResponseHelper::responseFn(0, 404, __('otp.no_otp_found'), $data);
        }

        // Check if the OTP is expired
        $createdTime = Carbon::parse($otp->created_at);
        $expiryTime = $createdTime->addMinutes($OTP_Expiry);

        if (Carbon::now()->gt($expiryTime)) {
            $data = [];
            return ResponseHelper::responseFn(0, 410, __('otp.otp_expired'), $data);
        }

        // Check if the OTP matches
        if ($otp->code !== $request->otp) {
            // Increment the attempt count
            $otp->increment('count');

            // Check if maximum attempts have been reached
            if ($otp->count >= $OTP_Count) {
                $data = [
                    'retry_after' => $OTP_Expiry . ' minutes',
                ];
                return ResponseHelper::responseFn(0, 429, __('opt.maximum_limit'), $data);
            }

            $attempts_left = "Invalid OTP. Please try again. Attempt Left - " . ($OTP_Count - $otp->count);
            $data = [
                // 'errors' => [
                //     'otp' => [$attempts_left], // Include your custom error message
                // ],
                $attempts_left
            ];
            return ResponseHelper::responseFn(0, 400, __('otp.invalid_otp'), $data);

        }

        // If OTP is valid, you can perform any action needed (e.g., log the user in)
        $mobile = $otp->mobile;

        $otp->isVerify = 1;
        $otp->save();

        $data = [
            'mobile' => $mobile,
        ];

        return $data;
        // return ResponseHelper::responseFn(1, 200, 'OTP verified successfully.', $data);

    }

    public static function resendOtp($request)
    {

        $dial_code = CountryHelpher::getCountryDialCode($request->country_code);
        OTPHelper::byPassPhoneNumber($request->mobile, $dial_code);

        // Constants for OTP expiry and maximum attempts
        $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
        $OPT_Count = env('OTP_COUNT');  // Maximum of 3 attempts

        // Validate the phone number
        $validator = Validator::make($request->all(), [
            // 'mobile' => 'required|digits:10', // assuming a 10-digit phone number
            'mobile' => ['required', new PhoneNumber()],
            'sms_type' => 'required|in:change_password,forgot_password',
        ], [
            'mobile.required' => __('opt.required', ['attribute' => __('opt.attributes.mobile')]),
            'sms_type.required' => __('opt.required', ['attribute' => __('opt.attributes.sms_type')]),
            'sms_type.in' => __('opt.invalid', ['attribute' => __('opt.attributes.sms_type')]),
        ]);

        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors()
            ];
            return ResponseHelper::responseFn(0, 400, __('opt.invalid_mobile_number'), $data);
        }

        // Check if an OTP already exists for the phone number
        $otp = Otp::where('mobile', $request->mobile)->first();

        if ($otp) {
            // Check if OTP is still valid (not expired)
            $createdTime = Carbon::parse($otp->created_at);
            $expiryTime = $createdTime->addMinutes($OTP_Expiry);

            if (Carbon::now()->lt($expiryTime)) {
                // OTP is not expired, check if attempts exceeded
                if ($otp->count >= $OPT_Count) {
                    $data = [
                        'retry_after' => $OTP_Expiry . ' minutes',
                    ];
                    return ResponseHelper::responseFn(0, 429, __('otp.unprocess_otp'), $data);
                }


                // --------------------------------- SEND SMS -----------------------------------
                $data = [
                    'mobiles' => $otp->dial_code . $otp->mobile,
                    'OTP' => $otp->code,
                ];
                SendMessageHelper::send($request->sms_type, $data);
                // --------------------------------- SEND SMS -----------------------------------


                // Increment the count if the OTP is still valid
                $otp->increment('count');
                // return response()->json([
                //     'message' => 'The OTP has been sent again. You can use the same OTP.',
                //     'mobile' => $otp->mobile,
                //     'status' => 1,
                // ], 200);

                $data = [
                    'mobile' => $otp->mobile,
                ];
                return ResponseHelper::responseFn(1, 200, __('otp.otp_send_again'), $data);

            }

            // If the OTP is expired, delete the previous OTP
            $otp->delete(); // Delete the previous OTP record
        }

        // Generate a new OTP
        $newOtp = Otp::create([
            'dial_code' => $dial_code,
            'mobile' => $request->mobile,
            'code' => rand(100000, 999999),
            'count' => 1, // Initialize count to 1 since this is a new OTP
        ]);

        // --------------------------------- SEND SMS -----------------------------------
        $data = [
            'mobiles' => $newOtp->dial_code . $newOtp->mobile,
            'otp' => $newOtp->code,
        ];
        // $sendRes = $this->send($data);
        SendMessageHelper::send($request->sms_type, $data);
        // --------------------------------- SEND SMS -----------------------------------

        $data = [
            'mobile' => $newOtp->mobile,
        ];
        return ResponseHelper::responseFn(1, 200, __('otp.otp_sent'), $data);

    }


    // BY PASS NUMBER FOR SENDING SMS
    public static function byPassPhoneNumber($mobile, $dial_code = '+91')
    {
        $phoneNumbers = config('sms.phoneNumbers'); // Get the phone numbers from the configuration

        // // Check if the mobile number exists in the phoneNumbers list
        // if (!in_array($mobile, $phoneNumbers)) {
        //     return [
        //         'mobile' => $mobile,
        //         'status' => 0, // Indicating mobile number is not in the bypass list
        //         'message' => 'Mobile number is not allowed to bypass',
        //     ];
        // }

        // dd(in_array($mobile, $phoneNumbers));

        if (in_array($mobile, $phoneNumbers)) {
            // Proceed if the number is in the list
            $otp = Otp::where('mobile', $mobile)->first();

            // if ($otp) {
            //     $otp->delete();
            // }

            if (!$otp) {
                $otp = Otp::create([
                    'dial_code' => $dial_code,
                    'mobile' => $mobile,
                    'code' => '000000', // Set the bypass OTP code
                    'count' => 1,     // Initialize count
                ]);
            }

            return [
                'mobile' => $otp->mobile,
                'status' => 1, // Indicating success
            ];
        }

    }

    // BY PASS NUMBER FOR SENDING SMS

}