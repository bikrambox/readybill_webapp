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
use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;

use Modules\Core\Entities\OTP;
use Modules\Authentication\Entities\User;
use Modules\GroceryIndia\Entities\Shop;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;
use Modules\Core\Rules\ValidLanguageCode;
use Modules\Core\Rules\NoScriptTag;
use Modules\Authentication\Rules\GstinRule;
use Modules\Core\Rules\NoSpecialCharacter;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class RegisterHelper
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    // -------------------------------------------------------------------------------------- SEND OTP --------------------------------------------------------------------------------------
    protected function snedOtpValidate()
    {

        return Validator::make(
            $this->request->all(),
            [
                // 'mobile' => 'required|numeric|digits:10|unique:users',

                'country_code' => [
                    'required',
                    new ValidCountryCode()
                ],

                // 'mobile' => [
                //     'required',
                //     // 'unique:users,mobile',
                //     // 'regex:/^[6-9]\d{9}$/',
                //     new PhoneNumber($this->request->country_code),
                //     function ($attribute, $value, $fail) {

                //         // check mobile number exist or not in user table, if exsist in users table check in shops table
                //         $isMobileExistsInUserTable = DB::table('users')
                //             ->where('mobile', $value)
                //             ->first();

                //         if (!$isMobileExistsInUserTable) {
                //             return;
                //         }

                //         // $isMobileNumberExistsInShopTable = DB::table('readybill_grocery.shops')
                //         //     ->where('user_id', $isMobileExistsInUserTable->user_id)
                //         //     ->exists();

                //         $isMobileNumberExistsInShopTable = DB::connection('grocery_india')
                //             ->table('shops')
                //             ->where('user_id', $isMobileExistsInUserTable->user_id)
                //             ->exists();
                //         // check mobile number exist or not in user table, if exsist in users table check in shops table

                //         if ($isMobileExistsInUserTable && $isMobileNumberExistsInShopTable) {
                //             $fail(__('register_validation.mobile_already_registered'));
                //         }

                //     },
                // ],


                'mobile' => [
                    'required',
                    new PhoneNumber($this->request->country_code),
                    function ($attribute, $value, $fail) {

                        $user = DB::table('users')
                            ->where('mobile', $value)
                            ->first();

                        if (!$user) {
                            return;
                        }

                        // Connections to skip
                        $skipConnections = ['central'];

                        // ❗ Only ignore sqlite
                        $ignoredDrivers = ['sqlite'];

                        // Tables to check
                        $tablesToCheck = ['shops', 'staff'];

                        foreach (config('database.connections') as $connectionName => $config) {

                            if (in_array($connectionName, $skipConnections, true)) {
                                continue;
                            }

                            if (in_array($config['driver'] ?? null, $ignoredDrivers, true)) {
                                continue;
                            }

                            foreach ($tablesToCheck as $table) {
                                try {
                                    if (!Schema::connection($connectionName)->hasTable($table)) {
                                        continue;
                                    }

                                    $exists = DB::connection($connectionName)
                                        ->table($table)
                                        ->where('user_id', $user->user_id) // see note below
                                        ->exists();

                                    if ($exists) {
                                        $fail(__('register_validation.mobile_already_registered'));
                                        return;
                                    }

                                } catch (\Throwable $e) {
                                    \Log::warning(
                                        "Validation DB check failed",
                                        ['connection' => $connectionName, 'table' => $table, 'error' => $e->getMessage()]
                                    );
                                }
                            }
                        }
                    },
                ],

            ],
            [
                // 'mobile.required' => 'You must enter your mobile number to continue.',
                // 'mobile.numeric' => 'The mobile number field must be a 10 digit number.',
                // 'mobile.digits' => 'The mobile number field must be a 10 digit number.',
                // 'mobile.unique' => 'This mobile number is already registered.',

                'country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),
                'mobile.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.mobile')]),
                'mobile.numeric' => __('register_validation.numeric', ['attribute' => __('register_validation.attributes.mobile')]),
                'mobile.digits' => __('register_validation.digits', ['attribute' => __('register_validation.attributes.mobile')]),
            ]
        );
    }

    public function sendOTP()
    {
        // Validate the incoming request
        $validator = $this->snedOtpValidate();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        try {


            $this->request->country_details = CountryHelpher::getCountryJson($this->request->country_code);

            $this->request->dial_code = $this->request->country_details['dial_code'];


            // CHECK MOBILE NUMBER IS REGISTERED BUT PROFILE IS NOT COMPLETED
            $checkUser = UserHelper::checkUserShopDetails($this->request->mobile);


            if (($checkUser['checkUser'] == 0) && ($checkUser['user_id'] != 0)) {

                $checkUser['mobile'] = '';
                $checkUser['status'] = 1;

                return [
                    'status' => 200,
                    'message' => __('validation.User Exsits but shop details is not present'),
                    'errors' => '',
                    'user' => $checkUser,
                ];
            }
            // CHECK MOBILE NUMBER IS REGISTERED BUT PROFILE IS NOT COMPLETED


            OTPHelper::byPassPhoneNumber($this->request->mobile, $this->request->dial_code);

            // Constants for OTP expiry and maximum attempts
            $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
            $OTP_Count = env('OTP_COUNT');   // Maximum of 3 attempts

            // Check if an OTP already exists for the phone number
            $otp = Otp::where('mobile', $this->request->mobile)->first();

            if ($otp) {
                // Check if OTP is still valid (not expired)
                $createdTime = Carbon::parse($otp->created_at);
                $expiryTime = $createdTime->addMinutes($OTP_Expiry);

                if (Carbon::now()->lt($expiryTime)) {
                    // OTP is not expired, check if attempts exceeded
                    if ($otp->count >= $OTP_Count) {

                        $data = [
                            'retry_after' => $OTP_Expiry . ' ' . __('validation.minutes'),
                        ];

                        return [
                            'status' => 429,
                            'message' => __('validation.Sorry! Unable to process the OTP'),
                            'errors' => $data,
                            'user' => null,
                        ];

                        // return ResponseHelper::responseFn(0, 429, 'Sorry! Unable to process the OTP', $data);

                    }

                    // --------------------------------- SEND SMS -----------------------------------
                    $data = [
                        'mobiles' => $this->request->dial_code . $otp->mobile,
                        'OTP' => $otp->code,
                    ];

                    SendMessageHelper::send('sign_up', $data);
                    // --------------------------------- SEND SMS -----------------------------------

                    // Increment the count if the OTP is still valid
                    $otp->increment('count');
                    $data = [
                        'mobile' => $otp->mobile,
                        'status' => 1,
                        'user_id' => '',
                        'checkUser' => '',
                    ];

                    return [
                        'status' => 200,
                        'message' => __('validation.The OTP has already been sent. You can use the same OTP.'),
                        'errors' => $data,
                        'user' => null,
                    ];

                    // return ResponseHelper::responseFn(1, 200, 'The OTP has already been sent. You can use the same OTP.', $data);

                } else {
                    // If the OTP is expired, delete the previous entry
                    $otp->delete();  // Delete the expired OTP entry
                }
            }

            // Generate a new OTP since the previous one is either invalid or deleted
            $newOtpCode = rand(100000, 999999);
            $otp = Otp::create([
                'mobile' => $this->request->mobile,
                'dial_code' => $this->request->dial_code,
                'code' => $newOtpCode,
                'count' => 1, // Initialize count
            ]);

            // --------------------------------- SEND SMS -----------------------------------
            $data = [
                'mobiles' => $this->request->dial_code . $otp->mobile,
                'OTP' => $otp->code,
            ];

            SendMessageHelper::send('sign_up', $data);
            // --------------------------------- SEND SMS -----------------------------------

            $data = [
                'mobile' => $otp->mobile,
                'status' => 1,
                'user_id' => '',
                'checkUser' => '',
            ];

            // Return success response
            return [
                'status' => 200,
                'message' => __('validation.Success'),
                'user' => $data,
            ];

        } catch (\Exception $e) {

            report($e);

            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [], // Optional, include additional error details if needed
                'member' => null,
            ];
        }
    }

    // -------------------------------------------------------------------------------------- SEND OTP --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- VERIFY OTP --------------------------------------------------------------------------------------
    
    public function verifyOtpValidate()
    {
        return Validator::make(
            $this->request->all(),
            [
                // 'mobile' => 'required|numeric|digits:10|unique:users',
                'mobile' => [
                    'required',
                    new PhoneNumber(),
                    'unique:users',
                ],
                'otp' => 'required|digits:6',
            ],
            [
                'mobile.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.mobile')]),
                'mobile.numeric' => __('register_validation.numeric', ['attribute' => __('register_validation.attributes.mobile')]),
                'mobile.digits' => __('register_validation.digits', ['attribute' => __('register_validation.attributes.mobile')]),
                'mobile.unique' => __('register_validation.unique', ['attribute' => __('register_validation.attributes.mobile')]),
                'otp.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.otp')]),
                'otp.digits' => __('register_validation.digits_otp', ['attribute' => __('register_validation.attributes.otp')]),
            ]
        );
    }

    public function verifyOTP()
    {
        // Validate the incoming request
        $validator = $this->verifyOtpValidate();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        try {

            // VERIFY OTP
            $response = OTPHelper::verifyOtp($this->request);
            // VERIFY OTP


            // Check if the response is a JsonResponse instance
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true); // Get data as an associative array

                if ($data['status'] === 0) {
                    return [
                        'status' => $data['code'], // HTTP status code
                        'message' => $data['message'], // Error message
                        'errors' => $data['data'], // Additional info (e.g., attempts left)
                    ];
                }
            }

            // Return success response
            return [
                'status' => 200,
                'message' => 'Success',
                // 'user' => $data,
            ];

        } catch (\Exception $e) {
            // Rollback the transaction on any error
            DB::rollBack();

            report($e);

            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [], // Optional, include additional error details if needed
                'user' => null,
            ];
        }
    }
    // -------------------------------------------------------------------------------------- VERIFY OTP --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- CREATE USER --------------------------------------------------------------------------------------
   
    protected function createUserValidation()
    {
        return Validator::make(
            $this->request->all(),
            [
                'mobile' => [
                    'required',
                    'unique:users,mobile',
                    // 'regex:/^[6-9]\d{9}$/',
                    new PhoneNumber($this->request->country_code),
                    function ($attribute, $value, $fail) {
                        // Check if the mobile number exists in the 'opts' table and if 'isVerify' is 1
                        $isVerified = DB::table('o_t_p_s')
                            ->where('mobile', $value)
                            ->where('isVerify', 1)
                            ->exists();

                        if (!$isVerified) {
                            $fail(__('validation.mobile_not_verified'));
                        }
                    },
                ],
                // 'country_code'=>'required',
                'country_code' => [
                    'required',
                    new ValidCountryCode()
                ],

                'detected_country_code' => [
                    'required',
                    new ValidCountryCode()
                ],

                'detected_language' => [
                    'nullable',
                    new ValidLanguageCode()
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    new NoScriptTag
                ],
                'shop_type' => ['required', Rule::in(array_values(config('general.shop_type')))],
            ],
            [
                'mobile.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.mobile')]),
                'mobile.unique' => __('register_validation.unique', ['attribute' => __('register_validation.attributes.mobile')]),
                'country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),
                'detected_country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.detected_country_code')]),
                'password.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.password')]),
                'password.string' => __('register_validation.string', ['attribute' => __('register_validation.attributes.password')]),
                'password.min' => __('register_validation.min', ['attribute' => __('register_validation.attributes.password'), 'min' => 8]),
                'password.confirmed' => __('register_validation.confirmed', ['attribute' => __('register_validation.attributes.password')]),
                'shop_type.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.shop_type')]),
                'shop_type.in' => __('register_validation.invalid', ['attribute' => __('register_validation.attributes.shop_type')]),
                'detected_language.valid_detected_language' => __('reister_validation.custom.detected_language.valid_detected_language'),
            ]
        );
    }

    public function createUser()
    {
        // Validate the incoming request
        $validator = $this->createUserValidation();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        try {

            $this->request->country_details = CountryHelpher::getCountryJson($this->request->country_code);



            $detected_country_name = CountryHelpher::getCountryJson(strtoupper($this->request->detected_country_code));

            // STORE DATA IN TABLE AFTER VERIFICATION
            // Create user
            $user = User::create([
                'name' => $this->request->name,
                'mobile' => $this->request->mobile,
                'password' => Hash::make($this->request->password),
                'ip_address' => $this->request->ip(),
                'isAdmin' => 1,
                'last_logged_in' => now(),
                'active' => 1,
                'shop_type' => $this->request->shop_type,
                // 'country_code' => CountryHelpher::getCountryDialCode($this->request->country_code),
                // 'country_short_code' => $this->request->country_code,
                // 'country' => CountryHelpher::getCountryName($this->request->country_code),

                'country_details' => json_encode($this->request->country_details),
                'country_code' => strtolower($this->request->country_details['code']),

                'detected_country_code' => strtolower($this->request->detected_country_code),

                'lang' => strtolower($this->request->detected_language ?? $this->request->country_details['language_short_code']),

                'module_type' => $this->request->shop_type . '_' . strtolower($detected_country_name['name']),

            ]);
            // STORE DATA IN TABLE AFTER VERIFICATION

            // delete otp
            Otp::where('mobile', $this->request->mobile)
                ->where('isVerify', 1)
                ->first()?->delete();
            // delete otp

            // Return success response
            return [
                'status' => 200,
                'message' => 'Success',
                'user' => $user,
            ];

        } catch (\Exception $e) {
            // Rollback the transaction on any error
            DB::rollBack();
            report($e);
            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [], // Optional, include additional error details if needed
                'user' => null,
            ];
        }
    }
    // -------------------------------------------------------------------------------------- CREATE USER --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- CREATE SHOP --------------------------------------------------------------------------------------    
    
    protected function createShopValidation()
    {
        return Validator::make(
            $this->request->all(),
            [
                'user_id' => 'required|exists:users,user_id',
                'name' => ['required', 'string', 'max:250', new NoScriptTag, new NoSpecialCharacter('name')],
                'business_name' => [
                    'required',
                    'string',
                    'max:250',
                    // 'unique:shops,business_name',
                    'regex:/^[a-zA-Z0-9\s]+$/',
                    new NoScriptTag
                ],
                // 'email' => 'nullable|string|email:rfc,dns|max:250|unique:grocery.shops,email',

                'email' => [
                    'nullable',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoSpecialCharacter('email'),
                    function ($attribute, $value, $fail) {
                        if ($value !== 'NA') {
                            $user = User::find($this->request->user_id);
                            if (!$user) {
                                $fail(__('validation.Invalid user.'));
                                return;
                            }

                            $module_type = $user->module_type;

                            $existsInShops = DB::connection($module_type)
                                ->table('shops')
                                ->where('email', $value)
                                ->exists();

                            $existsInStaff = DB::connection($module_type)
                                ->table('staff')
                                ->where('email', $value)
                                ->exists();

                            if ($existsInShops || $existsInStaff) {
                                $fail(__('validation.The email has already been registered.'));
                            }
                        }
                    },
                ],

                'address' => ['required','max:500', new NoScriptTag, new NoSpecialCharacter('address')],
                'gstin' => [
                    'nullable', 
                    new NoScriptTag, 
                    new GstinRule,

                    function ($attribute, $value, $fail) {
                        if ($value !== 'NA') {
                            $user = User::find($this->request->user_id);
                            if (!$user) {
                                $fail(__('validation.Invalid user.'));
                                return;
                            }

                            $module_type = $user->module_type;

                            $existsInShops = DB::connection($module_type)
                                ->table('shops')
                                ->where('gstin', $value)
                                ->where('user_id', '!=', $this->request->user_id)
                                ->exists();

                            if ($existsInShops) {
                                $fail(__('validation.The GST Number has already registered'));
                            }
                        }
                    },
                ],
                'logo' => 'nullable|image|mimes:jpeg,png,gif,jpg,heic|max:5120', // 5 mb
                'terms_n_conditions' => 'required|accepted',
            ],

            [
                // 'terms_n_conditions.accepted' => 'You must accept the terms and conditions to proceed.',
                // 'terms_n_conditions.required' => 'You must accept the terms and conditions to proceed.',
                // 'business_name.regex' => 'The business name can only contain letters, numbers, and spaces.',

                'user_id.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.user_id')]),
                'user_id.exists' => __('register_validation.exists', ['attribute' => __('register_validation.attributes.user_id')]),
                'name.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.name')]),
                'name.string' => __('register_validation.string', ['attribute' => __('register_validation.attributes.name')]),
                'name.max' => __('register_validation.max', ['attribute' => __('register_validation.attributes.name'), 'max' => 250]),
                'business_name.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.business_name')]),
                'business_name.string' => __('register_validation.string', ['attribute' => __('register_validation.attributes.business_name')]),
                'business_name.max' => __('register_validation.max', ['attribute' => __('register_validation.attributes.business_name'), 'max' => 250]),
                'business_name.regex' => __('register_validation.regex_business_name', ['attribute' => __('register_validation.attributes.business_name')]),
                'email.string' => __('register_validation.string', ['attribute' => __('register_validation.attributes.email')]),
                'email.max' => __('register_validation.max', ['attribute' => __('register_validation.attributes.email'), 'max' => 250]),
                'address.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.address')]),
                'address.max' => __('register_validation.max', ['attribute' => __('register_validation.attributes.address'), 'max' => 500]),
                // 'gstin.unique' => __('register_validation.unique', ['attribute' => __('register_validation.attributes.unique')]),
                'logo.image' => __('register_validation.image', ['attribute' => __('register_validation.attributes.logo')]),
                'logo.mimes' => __('register_validation.mimes', ['attribute' => __('register_validation.attributes.logo'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'logo.max' => __('register_validation.max', ['attribute' => __('register_validation.attributes.logo'), 'max' => '5MB']),
                'terms_n_conditions.required' => __('register_validation.required_accepted', ['attribute' => __('register_validation.attributes.terms_n_conditions')]),
                'terms_n_conditions.accepted' => __('register_validation.required_accepted', ['attribute' => __('register_validation.attributes.terms_n_conditions')]),

            ]
        );
    }

    // public function createShop()
    // {
    //     // Validate the incoming request
    //     $validator = $this->createShopValidation();

    //     if ($validator->fails()) {
    //         return [
    //             'status' => 400,
    //             'message' => __('validation.Invalid input'),
    //             'errors' => $validator->errors(),
    //             'user' => null,
    //         ];
    //     }

    //     $user = User::find($this->request->user_id);


    //     // Start the database transaction
    //     DB::beginTransaction();
    //     try {

    //         $user = User::find($this->request->user_id);

    //         $username = strtolower(str_replace(' ', '_', $this->request->business_name) . "_" . $user->mobile);
    //         $table_name = 'item_' . $username;
    //         $billing_table_name = 'billing_' . $username;

    //         // process photo
    //         $imageName = $this->processPhoto();

    //         UserHelper::createOrUpdateAdminData(
    //             $user->user_id,
    //             $this->request->name,
    //             $this->request->email,
    //             $this->request->business_name,
    //             $this->request->address,
    //             $this->request->gstin,
    //             $imageName,
    //             $user->module_type
    //         );

    //         $this->request->password = $user->password;


    //         $shop = DB::connection($user->module_type)->table('shops')->where('user_id', $user->user_id)->first();

    //         UserHelper::createOrUpdatePreference($user->user_id, $user->module_type);

    //         // Commit the transaction after all successful operations
    //         DB::commit();

    //         // // Attempt authentication
    //         // $credentials = $this->request->only('mobile', 'password');
    //         // if (Auth::attempt($credentials)) {
    //         $token = $user->createToken($user->mobile)->accessToken;

    //         Cache::forget(env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile);
    //         Cache::store('memcached')->forget(env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile);
    //         Cache::put(env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile, $user);

    //         // // Update token and last login time
    //         // DB::table('users')->where('user_id', $user->user_id)->update([
    //         //     'token' => $token,
    //         //     'last_logged_in' => now(),
    //         // ]);

    //         // GENERATE API KEY
    //         $api_key = UserHelper::apiKeyGenerate($user->user_id);
    //         // GENERATE API KEY

    //         $country_details = json_decode($user->country_details);

    //         UserHelper::generateItemTableForAdmin($table_name, $user->module_type, strtolower($country_details->region), strtolower($country_details->name));

    //         // UserHelper::generateBillingTableForShop($billing_table_name, $user->module_type);

    //         // CREATE SUBSCRPTION
    //         SubscriptionHelper::assignSubscription($shop->shop_id, 1, $user->module_type);
    //         // CREATE SUBSCRPTION

    //         // delete otp
    //         Otp::where('mobile', $this->request->mobile)
    //             ->where('isVerify', 1)
    //             ->first()?->delete();
    //         // delete otp


    //         // $country_details = json_decode($user->country_details);



    //         $data = [
    //             'mobiles' => $country_details->dial_code . $user->mobile
    //         ];

    //         // SEND MESSAGE AFTER SUCCESSFULL REGISTRATION
    //         SendMessageHelper::send('registration_sucssess', $data);

    //         DB::commit();

    //         $data = [
    //             'token' => $token,
    //             // 'api_key' => $api_key,
    //             'api_key' => Crypt::encryptString($api_key),
    //         ];

    //         // Return success response
    //         return [
    //             'status' => 200,
    //             'message' => 'Success',
    //             'user' => $data,
    //         ];

    //         // return ResponseHelper::responseFn(1, 200, 'An OTP has been sent to your mobile. Please check.', $data);

    //         // } else {
    //         //     DB::rollBack();
    //         //     return [
    //         //         'status' => 401,
    //         //         'message' => 'Authentication failed!',
    //         //         'errors' => [],
    //         //         'user' => null,
    //         //     ];
    //         // }


    //     } catch (\Exception $e) {
    //         // Rollback the transaction on any error
    //         DB::rollBack();
    //         report($e);
    //         return [
    //             'status' => 500,
    //             'message' => __('validation.unable_to_process'),
    //             'errors' => [], // Optional, include additional error details if needed
    //             'user' => null,
    //         ];
    //     }
    // }

    public function createShop()
    {
        $validator = $this->createShopValidation();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        $tenantStarted = false;
        $centralStarted = false;
        $moduleType = null;

        try {
            $user = User::findOrFail($this->request->user_id);
            $moduleType = $user->module_type;

            DB::connection('mysql')->beginTransaction();
            $centralStarted = true;

            DB::connection($moduleType)->beginTransaction();
            $tenantStarted = true;

            $imageName = $this->processPhoto();

            UserHelper::createOrUpdateAdminData(
                $user->user_id,
                $this->request->name,
                $this->request->email,
                $this->request->business_name,
                $this->request->address,
                $this->request->gstin,
                $imageName,
                $moduleType
            );

            $shop = DB::connection($moduleType)
                ->table('shops')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$shop) {
                throw new \Exception('Shop was not created.');
            }

            UserHelper::createOrUpdatePreference($user->user_id, $moduleType);

            $subscription = SubscriptionHelper::assignSubscription($shop->shop_id, 1, $moduleType);

            if (empty($subscription['success'])) {
                throw new \Exception($subscription['message'] ?? 'Subscription assignment failed.');
            }

            $apiKey = UserHelper::apiKeyGenerate($user->user_id);

            DB::connection($moduleType)->commit();
            $tenantStarted = false;

            DB::connection('mysql')->commit();
            $centralStarted = false;

            $countryDetails = CountryHelpher::getCountryJson(strtoupper($user->detected_country_code));
            $username = strtolower(str_replace(' ', '_', $this->request->business_name) . "_" . $user->mobile);
            $tableName = 'item_' . $username;

            UserHelper::generateItemTableForAdmin(
                $tableName,
                $moduleType,
                strtolower($countryDetails['region']),
                strtolower($countryDetails['name'])
            );

            $token = $user->createToken($user->mobile)->accessToken;

            return [
                'status' => 200,
                'message' => 'Success',
                'user' => [
                    'token' => $token,
                    'api_key' => Crypt::encryptString($apiKey),
                ],
            ];
        } catch (\Throwable $e) {
            if ($tenantStarted && $moduleType) {
                DB::connection($moduleType)->rollBack();
            }

            if ($centralStarted) {
                DB::connection('mysql')->rollBack();
            }

            report($e);

            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [],
                'user' => null,
            ];
        }
    }
    // -------------------------------------------------------------------------------------- CREATE SHOP --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- PROCESS PHOTO --------------------------------------------------------------------------------------
    
    public function processPhoto()
    {
        $imageName = '';
        if ($this->request->hasFile('logo')) {
            $mediaFolder = 'storage/logo';
            if (!file_exists($mediaFolder)) {
                mkdir($mediaFolder, 0777, true);
            }

            $image = $this->request->file('logo');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move($mediaFolder, $imageName);

            $imagePath = $mediaFolder . '/' . $imageName;
            $image = Image::make($imagePath);
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            $newWidth = 200;
            $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));
            $image->resize($newWidth, $newHeight)->save($imagePath);

        }
        return $imageName;
    }

    // -------------------------------------------------------------------------------------- PROCESS PHOTO --------------------------------------------------------------------------------------
}
