<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


use Modules\Core\Helpers\ResponseHelper;
use Modules\Core\Helpers\SendMessageHelper;
use Modules\Core\Helpers\OTPHelper;
use Modules\Core\Helpers\CountryHelpher;


use Modules\Authentication\Entities\User;
use Modules\Core\Entities\OTP;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;

class OTPController extends Controller
{
    public function handleOtp(Request $request)
    {
        // Constants for OTP expiry and maximum attempts
        $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
        $OTP_Count = (int) env('OTP_COUNT');   // Maximum of 3 attempts

        // Validate the request
        $validator = Validator::make($request->all(), [

            'user_id' => [
                'required_if:sms_type,change_mobile_number',
                'numeric',
                function ($attribute, $value, $fail) {
                    if (!DB::table('users')->where('user_id', $value)->exists()) {
                        $fail('The selected user ID is invalid.');
                    }
                }
            ],

            'type' => 'required|in:send-otp,verify-otp',
            'sms_type' => [
                'required_if:type,send-otp',
                'in:change_password,forgot_password,change_mobile_number,delete_account',
                Rule::when($request->type === 'verify-otp', 'nullable')
            ],

            // 'mobile' => 'required|exists:users,mobile',

            'mobile' => [
                'required',
                new PhoneNumber($request->country_code),
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->sms_type, ['change_password', 'forgot_password', 'delete_account'])) {
                        // Ensure mobile exists in users table
                        if (!DB::table('users')->where('mobile', $value)->exists()) {
                            $fail('The mobile number does not exist.');
                        }
                    } else if ($request->sms_type === 'change_mobile_number') {

                        // Validate unique mobile number excluding the current user
                        $existsInUsers = DB::table('users')
                            ->where('mobile', $value)
                            // ->where('user_id', '!=', $request->user_id)
                            ->exists();

                        if ($existsInUsers) {
                            $fail(__('validation.The mobile number has already been registered'));
                        }
                    }
                }
            ],

            'country_code' => [
                'required_if:type,send-otp',
                new ValidCountryCode(),
                // function ($attribute, $value, $fail) use ($request) {
                //     // Only apply the ValidCountryCode rule if type is 'send-otp'
                //     if (($request->sms_type != 'change_mobile_number') && ($request->type === 'send-otp') && (!empty($request->mobile))) {
                //         // Apply the ValidCountryCode rule
                //         $validCountryCodeRule = new ValidCountryCode();
                //         $validCountryCodeRule->passes($attribute, $value) || $fail('The country code is invalid.');

                //         // Ensure the country code matches the mobile number's stored country code
                //         $user = DB::table('users')->where('mobile', $request->mobile)->first();

                //         if ($user) {
                //             $isCheck = CountryHelpher::checkCountryCode($user->country_details, $value);
                //             if (!$isCheck) {
                //                 $fail('The provided mobile number does not match our records.');
                //             }
                //         } else {
                //             $fail('User not found.');
                //         }
                //     }
                // }
            ],

            'otp' => 'required_if:type,verify-otp|digits:6',

        ], [
            // 'type.required' => 'The request type is required.',
            // 'type.in' => 'Invalid request type provided.',
            // 'mobile.required' => 'Phone number is required.',
            // 'mobile.digits' => 'Phone number must be 10 digits long.',
            // 'otp.required_if' => 'OTP is required for verification.',
            // 'otp.digits' => 'OTP must be exactly 6 digits long.',

            'user_id.required_if' => __('forgot_password_validation.required_if', ['attribute' => __('forgot_password_validation.attributes.user_id'), 'other' => __('forgot_password_validation.attributes.sms_type'), 'value' => 'change_mobile_number']),
            'user_id.numeric' => __('forgot_password_validation.numeric', ['attribute' => __('forgot_password_validation.attributes.user_id')]),
            'type.required' => __('forgot_password_validation.required', ['attribute' => __('forgot_password_validation.attributes.type')]),
            'type.in' => __('forgot_password_validation.invalid', ['attribute' => __('forgot_password_validation.attributes.type')]),
            'sms_type.required_if' => __('forgot_password_validation.required_if', ['attribute' => __('forgot_password_validation.attributes.sms_type'), 'other' => __('forgot_password_validation.attributes.type'), 'value' => 'send-otp']),
            'sms_type.in' => __('forgot_password_validation.invalid', ['attribute' => __('forgot_password_validation.attributes.sms_type')]),
            'mobile.required' => __('forgot_password_validation.required', ['attribute' => __('forgot_password_validation.attributes.mobile')]),
            'country_code.required_if' => __('forgot_password_validation.required_if', ['attribute' => __('forgot_password_validation.attributes.country_code'), 'other' => __('forgot_password_validation.attributes.type'), 'value' => 'send-otp']),
            'otp.required_if' => __('forgot_password_validation.required_if', ['attribute' => __('forgot_password_validation.attributes.otp'), 'other' => __('forgot_password_validation.attributes.type'), 'value' => 'verify-otp']),
            'otp.digits' => __('forgot_password_validation.digits_otp', ['attribute' => __('forgot_password_validation.attributes.otp')]),

        ]);

        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors()
            ];

            return ResponseHelper::responseFn(0, 400, __('forgot_password.invalid_input'), $data);
        }

        // Check if the phone number is already registered
        $user = User::where('mobile', $request->mobile)->first();

        // Call the appropriate function based on the request type
        if ($request->type === 'send-otp') {

            if (!$user && $request->sms_type != 'change_mobile_number') {
                $data = [
                    'registered' => true
                ];
                return ResponseHelper::responseFn(0, 400, __('validation.Sorry! No User Found'), $data);
            }

            return $this->generateOtp($request, $OTP_Expiry, $OTP_Count);
        } elseif ($request->type === 'verify-otp') {
            return $this->verifyOtp($request, $OTP_Expiry, $OTP_Count);
        }

        $data = [];
        return ResponseHelper::responseFn(0, 400, __('validation.Invalid request type.'), $data);
    }

    private function generateOtp($request, $OTP_Expiry, $OTP_Count)
    {

        OTPHelper::byPassPhoneNumber($request->mobile);

        // Check if an OTP already exists for the phone number
        $otp = OTP::where('mobile', $request->mobile)->first();

        if ($otp) {
            // Check if OTP is still valid (not expired)
            $createdTime = Carbon::parse($otp->created_at);
            $expiryTime = $createdTime->addMinutes($OTP_Expiry);

            if (Carbon::now()->lt($expiryTime)) {
                // OTP is not expired, check if attempts exceeded
                if ($otp->count >= $OTP_Count) {

                    $data = [
                        'retry_after' => $OTP_Expiry . ' '. __('validation.minutes'),
                    ];
                    return ResponseHelper::responseFn(0, 429, __('validation.Sorry! Unable to process the OTP'), $data);

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
                return ResponseHelper::responseFn(1, 200, __('validation.The OTP has already been sent. You can use the same OTP.'), $data);

            } else {
                // If the OTP is expired, delete the previous entry
                $otp->delete();  // Delete the expired OTP entry
            }
        }

        // Generate a new OTP since the previous one is either invalid or deleted
        $newOtpCode = rand(100000, 999999);
        $otp = OTP::create([
            'dial_code' => CountryHelpher::getCountryDialCode($request->country_code),
            'mobile' => $request->mobile,
            'code' => $newOtpCode,
            'count' => 1, // Initialize count
        ]);

        // --------------------------------- SEND SMS -----------------------------------
        $data = [
            'mobiles' => $otp->dial_code . $otp->mobile,
            'OTP' => $otp->code,
        ];


        // $request->sms_type = 'change_mobile_number';
        // SendMessageHelper::send($request->sms_type, $data);
        SendMessageHelper::send($request->sms_type, $data);
        // --------------------------------- SEND SMS -----------------------------------

        $data = [
            'mobile' => $otp->mobile,
            'status' => 1,
        ];
        return ResponseHelper::responseFn(1, 200, __('otp.otp_sent'), $data);
    }


    private function verifyOtp($request, $OTP_Expiry, $OTP_Count)
    {
        // Retrieve the OTP record for the phone number
        $otp = OTP::where('mobile', $request->mobile)->first();

        // Check if the OTP record exists
        if (!$otp) {
            $data = [];
            return ResponseHelper::responseFn(0, 404, __('otp.no_otp_found'), $data);
        }

        // Check if the OTP is expired
        $createdTime = Carbon::parse($otp->created_at);
        $expiryTime = $createdTime->addMinutes($OTP_Expiry);


        // Check if maximum attempts have been reached
        if ($otp->count >= $OTP_Count) {
            $data = [
                'retry_after' => $OTP_Expiry . ' minutes',
            ];
            return ResponseHelper::responseFn(0, 429, __('otp.maximum_limit'), $data);
        }
        

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
                return ResponseHelper::responseFn(0, 429, __('otp.maximum_limit'), $data);
            }

            $attempts_left = "Invalid OTP. Please try again. Attempt Left - " . ($OTP_Count - $otp->count);
            $data = [
                // 'attempts_left' => $OTP_Count - $otp->count
                'errors' => [
                    'otp' => [$attempts_left], // Include your custom error message
                ],
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
        return ResponseHelper::responseFn(1, 200, __('otp.OTP verified successfully'), $data);
    }

    public function resendOtp(Request $request)
    {

        OTPHelper::byPassPhoneNumber($request->mobile, $request->dial_code);

        // Constants for OTP expiry and maximum attempts
        $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
        $OPT_Count = env('OTP_COUNT');  // Maximum of 3 attempts

        // Validate the phone number
        $validator = Validator::make($request->all(), [
            // 'mobile' => 'required|digits:10', // assuming a 10-digit phone number
            'mobile' => ['required', new PhoneNumber()],
            'sms_type' => 'required|in:change_password,forgot_password,change_mobile_number,sign_up',

            'country_code' => [
                'required_if:type,send-otp',
                new ValidCountryCode(),
                function ($attribute, $value, $fail) use ($request) {
                    // Ensure the country code matches the mobile number's stored country code
                    $user = DB::table('users')->where('mobile', $request->mobile)->first();

                    $isCheck = CountryHelpher::checkCountryCode($user->country_details, $value);

                    if (!$isCheck) {
                        $fail(__('validation.The provided mobile number does not match our records.'));
                    }


                }
            ],

        ]);

        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors()
            ];
            return ResponseHelper::responseFn(0, 400, __('validation.Invalid mobile number'), $data);
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
                        'retry_after' => $OTP_Expiry . ' '. __('validation.minutes'),
                    ];
                    return ResponseHelper::responseFn(0, 429, __('validation.Sorry! Unable to process the OTP'), $data);
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
                return ResponseHelper::responseFn(1, 200, __('otp.The OTP has been sent again. You can use the same OTP.'), $data);

            }

            // If the OTP is expired, delete the previous OTP
            $otp->delete(); // Delete the previous OTP record
        }

        // Generate a new OTP
        $newOtp = Otp::create([
            'dial_code' => CountryHelpher::getCountryDialCode($request->country_code),
            'mobile' => $request->mobile,
            'code' => rand(100000, 999999),
            'count' => 1, // Initialize count to 1 since this is a new OTP
        ]);

        // --------------------------------- SEND SMS -----------------------------------
        $data = [
            'mobiles' => $otp->dial_code . $newOtp->mobile,
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
}
