<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use Hash;
use Illuminate\Support\Facades\Cache;

use Modules\Core\Helpers\ResponseHelper;

use Modules\Authentication\Entities\User;
use Modules\Core\Entities\OTP;

class ChangePasswordController extends Controller
{
    public function updatePassword(Request $request)
    {

        // Validate the request
        $validator = Validator::make($request->all(), [
            'mobile' => [
                'required',
                'exists:users,mobile',
                // 'regex:/^[6-9]\d{9}$/',
                function ($attribute, $value, $fail) {
                    // Check if the mobile number exists in the 'opts' table and if 'isVerify' is 1
                    $isVerified = DB::table('o_t_p_s')
                        ->where('mobile', $value)
                        ->where('isVerify', 1)
                        ->exists();

                    if (!$isVerified) {
                        $fail(__('validation.The mobile number is not verified. Please try again'));
                    }
                },
            ],
            // 'password' => 'required|string|min:8|confirmed',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) use ($request) {
                    $user = User::where('mobile', $request->mobile)->first();
                    if ($user && Hash::check($value, $user->password)) {
                        $fail(__('validation.The new password cannot be the same as the current password'));
                    }
                },
            ],
        ], [
            'mobile.required' => __('change_password_validation.required', ['attribute' => __('change_password_validation.attributes.mobile')]),
            'mobile.exists' => __('change_password_validation.exists', ['attribute' => __('change_password_validation.attributes.mobile')]),
            'password.required' => __('change_password_validation.required', ['attribute' => __('change_password_validation.attributes.password')]),
            'password.string' => __('change_password_validation.string', ['attribute' => __('change_password_validation.attributes.password')]),
            'password.min' => __('change_password_validation.min', ['attribute' => __('change_password_validation.attributes.password'), 'min' => 8]),
            'password.confirmed' => __('change_password_validation.confirmed', ['attribute' => __('change_password_validation.attributes.password')]),
        ]);

        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors()
            ];

            return ResponseHelper::responseFn(0, 400, __('validation.Invalid input'), $data);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            $data = [
                'registered' => true
            ];
            return ResponseHelper::responseFn(0, 400, __('validation.Sorry! No User Found'), $data);
        }

        // UPDATE PASSWORD 
        $user = DB::table('users')->where('mobile', $request->mobile)
            ->update(['password' => Hash::make($request->password)]);
        // UPDATE PASSWORD 

        // Check if an OTP already exists for the phone number
        $otp = OTP::where('mobile', $request->mobile)->first();

        // If the OTP is expired, delete the previous OTP
        $otp->delete(); // Delete the previous OTP record

        $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $request->mobile;

        // Delete the cache data
        Cache::forget($cacheKey);

        $data = [];
        return ResponseHelper::responseFn(1, 200, __('validation.Password Successfully Updated'), $data);
    }
}
