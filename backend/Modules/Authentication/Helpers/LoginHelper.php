<?php

namespace Modules\Authentication\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
// use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Auth;

use Modules\Core\Helpers\ResponseHelper;
use Modules\Core\Helpers\SendMessageHelper;
use Modules\Core\Helpers\OTPHelper;
use Modules\Core\Helpers\CountryHelpher;
use Modules\Core\Helpers\LanguageHelpher;
use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;

use Modules\Core\Entities\OTP;
use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\Shop;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;

class LoginHelper
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function loginValidate()
    {

        return Validator::make(
            $this->request->all(),
            [

                'loginFrom' => [
                    'required',
                    'in:1,2' // 1 - for app, 2 - web 
                ],

                'detected_country_code' => [
                    'required_if:loginFrom,1',
                ],


                'mobile' => [
                    'required',
                    new PhoneNumber($this->request->country_code),
                    'exists:users,mobile'
                ],

                'country_code' => [
                    'required',
                    new ValidCountryCode(),
                    function ($attribute, $value, $fail) {


                        // Fetch user based on mobile number
                        $user = DB::table('users')->where('mobile', $this->request->mobile)->first();

                        if ($user) {

                            if (!$user || empty($user->country_details)) {
                                // return $fail('Invalid Credential');
                                // return $fail(__('validation.login.custom.country_code.invalid_credential'));
                                return $fail(__('login_validation.custom.country_code.invalid_credential'));
                            }

                            // Validate country code
                            $isCheck = CountryHelpher::checkCountryCode($user->country_details, $value);

                            if (!$isCheck) {
                                // $fail('Invalid Credential');
                                // $fail(__('validation.login.custom.country_code.invalid_credential'));
                                $fail(__('login_validation.custom.country_code.invalid_credential'));
                            }

                        }
                    }
                ],

                'password' => 'required|string',
            ],
            [
                // 'mobile.required' => 'Mobile number is required',
                // 'mobile.exists' => 'The Mobile number is not registered in ReadyBill.',
                // 'password.required' => 'Password is required',

                'mobile.required' => __('login_validation.required', ['attribute' => __('login_validation.attributes.mobile')]),
                'mobile.exists' => __('login_validation.exists', ['attribute' => __('login_validation.attributes.mobile')]),
                'mobile.phone_number' => __('validation.custom.mobile.phone_number'),
                'country_code.required' => __('login_validation.required', ['attribute' => __('login_validation.attributes.country_code')]),
                'country_code.valid_country_code' => __('login_validation.custom.country_code.valid_country_code'),
                'password.required' => __('login_validation.required', ['attribute' => __('login_validation.attributes.password')]),
                'loginFrom.required_if' => __('login_validation.required', ['attribute' => __('login_validation.attributes.loginFrom')]),
                // 'detected_country_code.required_if' => __('login_validation.required', ['attribute' => __('login_validation.attributes.country_code')]),
                'detected_country_code.required_if' => __('login_validation.required_if', ['attribute' => __('login_validation.attributes.detected_country_code'), 'other' => __('login_validation.attributes.loginFrom'), 'value' => '1']),
            ]
        );
    }

    public function login()
    {
        $validator = $this->loginValidate();

        if ($validator->fails()) {
            return [
                'status' => 'failed',
                'code' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        try {

            $mobile = $this->request->mobile;
            $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $mobile;


            $response = $this->checkConditions();

            // dd($response);

            if ($response['code'] != 200) {

                return [
                    'status' => $response['status'],
                    'message' => $response['message'],
                    'user' => $response['user'],
                    'code' => $response['code'],
                ];
            }

            $credentials = $this->request->only('mobile', 'password');
            Auth::attempt($credentials);


            $other_details = [];
            $user = $response['user'];

            // dd($response);

            // $token = $user->createToken($user->mobile)->accessToken;
            $token = $user->createToken($user->mobile);

            $other_details = $user->isAdmin === 1 ? $user->shop : $user->staff;

            $user = User::find($user->user_id);


            // Update login timestamps
            $user->last_logged_in = $user->current_logged_in ?? Carbon::now();
            $user->current_logged_in = Carbon::now();
            $user->ip_address = $this->request->ip();

            if ($user->country_code == '') {
                CountryHelpher::updateCountryAndLanguage($user->user_id);
            }

            $user->save();
            $user = User::find($user->user_id);


            $logo_url = env('LOGO_URL', '');
            $imageUrl = '';
            $isLogo = 0;

            if ($user->isAdmin === 1) {
                $api_key = $user->apiKey->key;
                $shop_id = $user->shop->shop_id;

                // $shop = Shop::find($shop_id);
                $shop = DB::connection($user->module_type)->table('shops')->where('shop_id', $shop_id)->first();


                // Initialize variables
                $logo = ($shop->logo && $shop->logo !== 'NA') ? $shop->logo : '';
                $imageUrl = $logo ? rtrim($logo_url, '/') . '/' . ltrim($logo, '/') : '';
                $isLogo = 0;

                // Check if the image is valid (remote or local)
                if ($imageUrl) {
                    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        // Remote URL: Check if the image exists
                        $headers = @get_headers($imageUrl);
                        if ($headers && strpos($headers[0], '200') !== false) {
                            // Optionally verify content-type for images
                            $isLogo = 1;
                        }
                    } else {
                        // Local file: Check if the file exists
                        if (file_exists(public_path($imageUrl))) {
                            $isLogo = 1;
                        }
                    }
                }

                // Fallback to default image if no valid logo
                if (!$isLogo) {
                    $imageUrl = asset('assets/img/user.jpg');
                }



            } else {

                $staff = DB::connection($user->module_type)->table('staff')->where('user_id', $user->user_id)->first();

                $user['staff'] = $staff;

                // $shop     = Shop::find($user->staff->addedBy);
                $shop = DB::connection($user->module_type)->table('shops')->where('shop_id', $staff->addedBy)->first();
                // $api_key = $shop->user->apiKey->key;

                $api_key = DB::table('api_keys')->where('user_id', $shop->user_id)->value('key');

            }



            $shop_subscription = SubscriptionHelper::getShopSubscription($shop->shop_id, $user->module_type);


            if ($shop_subscription['code'] == 200) {


                $all_languages = LanguageHelpher::countryLanuages($user->country_code);

                $user['photo_url'] = $imageUrl;
                $user['is_logo'] = $isLogo;

                $data = [
                    'token' => $token->accessToken,
                    // 'expires_at_utc' => $token->token->expires_at,
                    // 'expires_at_local' => $token->token->expires_at
                    //     ->timezone(config('app.timezone'))
                    //     ->toDateTimeString(),
                    'user' => $user,
                    // 'other_details'=>$other_details,
                    'api_key' => Crypt::encryptString($api_key),
                    'shop_subscription' => $shop_subscription,
                    // 'subscription_expiry_date' => $subscription_expiry_date,
                    // 'isSubscriptionExpired' => $isSubscriptionExpired,
                    'all_languages' => $all_languages,
                ];

                $user = $response['user'];
                Cache::put($cacheKey, $user);

                return [
                    'status' => 'success',
                    'code' => 200,
                    'message' => __('validation.Login successful'),
                    'user' => $data,
                ];

            } else {
                return [
                    'status' => 'failed',
                    'message' => __('validation.An unexpected error occured.'),
                    'code' => 404,
                    'user' => '',
                ];
            }
            
            // // Get shop subscription and expiration date using DB queries
            // $shop_subscription = DB::connection($user->module_type)->table('shop_subscriptions')
            //     ->where('shop_id', $shop->shop_id)
            //     ->orderBy('created_at', 'desc')
            //     ->first();

            // if ($shop_subscription) {
            //     $subscription_expiry_date = Carbon::parse($shop_subscription->end_date)->format('d-m-Y H.i.s');

            //     $isSubscriptionExpired = UserHelper::isSubscriptionExpired($shop_subscription, $subscription_expiry_date);

            //     $all_languages = LanguageHelpher::countryLanuages($user->country_code);


            //     // dd($all_languages);

            //     $user['photo_url'] = $imageUrl;
            //     $user['is_logo'] = $isLogo;

            //     $data = [
            //         'token' => $token,
            //         'user' => $user,
            //         // 'other_details'=>$other_details,
            //         'api_key' => Crypt::encryptString($api_key),
            //         'shop_subscription' => $shop_subscription,
            //         'subscription_expiry_date' => $subscription_expiry_date,
            //         'isSubscriptionExpired' => $isSubscriptionExpired,
            //         'all_languages' => $all_languages,
            //     ];


            //     $user = $response['user'];
            //     Cache::put($cacheKey, $user);


            //     return [
            //         'status' => 'success',
            //         'code' => 200,
            //         'message' => __('validation.Login successful'),
            //         'user' => $data,
            //     ];
            // } else {
            //     return [
            //         'status' => 'failed',
            //         'message' => __('validation.An unexpected error occured.'),
            //         'code' => 404,
            //         'user' => '',
            //     ];
            // }

        } catch (\Exception $e) {

            report($e);

            return [
                'status' => 'failed',
                'code' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [],
                'user' => null,
            ];
        }
    }
    public function checkConditions()
    {
        $mobile = $this->request->mobile;
        $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $mobile;

        // Retrieve user from cache or database
        $user = Cache::remember($cacheKey, now()->addHours(1), function () use ($mobile) {
            return DB::table('users')->where('mobile', $mobile)->first();
        });


        // dd($user);

        // User not found
        if (!$user) {
            return [
                'status' => 'failed',
                'message' => __('validation.Invalid credentials.'),
                'code' => 404,
                'user' => null,
            ];
        }

        // Country code mismatch
        if ($this->request->country_code !== CountryHelpher::getCountryCodeFromCountryJson($user->country_details)) {
            return [
                'status' => 'failed',
                'message' => __('validation.Invalid credentials.'),
                'code' => 400,
                'user' => null,
            ];
        }


        // Password validation
        if (!Hash::check($this->request->password, $user->password)) {
            return [
                'status' => 'failed',
                'message' => __('validation.Invalid credentials.'),
                'code' => 401,
                'user' => null,
            ];
        }

        // Load user model for relationships
        $userModel = User::find($user->user_id);
        if (!$userModel) {
            return [
                'status' => 'failed',
                'message' => __('validation.User data not found.'),
                'code' => 404,
                'user' => null,
            ];
        }


        // check detected country code for app
        if ($this->request->loginFrom == 1) {

            if (strtoupper($user->detected_country_code) != strtoupper($this->request->detected_country_code)) {

                return [
                    'status' => 'failed',
                    'message' => __('validation.Invalid credentials.'),
                    'code' => 401,
                    'user' => null,
                ];

            }

        }

        // Check if profile is incomplete
        $checkUser = UserHelper::checkUserShopDetails($mobile);
        if ($checkUser['checkUser'] == 0 && $checkUser['user_id'] != 0) {
            return [
                'status' => 'success',
                'code' => 201,
                'message' => __('validation.Please complete your registration before using the system.'),
                'user' => $checkUser,
            ];
        }

        // Determine shop ID
        try {
            // $shopId = $userModel->isAdmin
            //     ? $userModel->shop->shop_id
            //     : Shop::find($userModel->staff->addedBy)->shop_id;

            // dd(DB::connection($user->module_type)->table('shops')->where('shop_id', $user->staff->addedBy)->value('shop_id'));

            $staff = DB::connection($user->module_type)->table('staff')->where('user_id', $user->user_id)->first();

            $shopId = $userModel->isAdmin
                ? $userModel->shop->shop_id
                : DB::connection($user->module_type)->table('shops')->where('shop_id', $staff->addedBy)->value('shop_id');

        } catch (\Exception $e) {

            report($e);

            return [
                'status' => 'failed',
                'message' => __('validation.Unable to retrieve shop details.'),
                'code' => 500,
                'user' => null,
            ];
        }

        // Check shop subscription
        $subscriptionStatus = UserHelper::checkShopSubscription($shopId, $userModel->module_type);
        if ($subscriptionStatus['status'] === 0) {
            return [
                'status' => 'subscription-failed',
                'message' => $subscriptionStatus['message'],
                'code' => $subscriptionStatus['code'],
                'user' => $userModel,
            ];
        }

        // if user is not admin and subscription plan is free then restrict it for login
        $shop_subscription = SubscriptionHelper::getShopSubscription($shopId, $userModel->module_type);


        // dd($shop_subscription['isFreeSubscriptionPlan']);

        if ( ($shop_subscription['code'] == 200) && ($userModel->isAdmin == 0) && ($shop_subscription['isFreeSubscriptionPlan'] == 1) ) {

            return [
                'status' => 'free-subscription',
                'message' => __('validation.You are currently on a free subscription plan. Please upgrade to a premium plan to access this feature'),
                'code' => 403,
                'user' => $userModel,
            ];
        }

        // if user is not admin and subscription plan is free then restrict it for login

        // Check if user is active
        if ($userModel->active == 0) {
            return [
                'status' => 'user-deactivate',
                'message' => __('validation.Your account has been deactivated. Please contact the administrator.'),
                'code' => 403,
                'user' => $userModel,
            ];
        }

        // Success response
        return [
            'status' => 'success',
            'message' => __('validation.User logged in successfully.'),
            'code' => 200,
            'user' => $userModel,
        ];
    }

}