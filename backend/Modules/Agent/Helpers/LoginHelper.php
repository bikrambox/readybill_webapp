<?php

namespace Modules\Agent\Helpers;

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
use Modules\Agent\Helpers\CommonHelpher;

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
                // 'detected_country_code' => [
                //     'required',
                // ],

                'email' => [
                    'required',
                    'email',
                    'exists:users,email'
                ],

                'password' => [
                    'required',
                    'string'
                ],
            ],
            [
                // 'detected_country_code.required' => __('validation.required', [
                //     'attribute' => __('validation.attributes.detected_country_code')
                // ]),

                'email.required' => __('validation.required', [
                    'attribute' => __('validation.attributes.email')
                ]),
                'email.email' => __('validation.email', [
                    'attribute' => __('validation.attributes.email')
                ]),
                'email.exists' => __('validation.exists', [
                    'attribute' => __('validation.attributes.email')
                ]),

                'password.required' => __('validation.required', [
                    'attribute' => __('validation.attributes.password')
                ]),
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

            // Normalize email
            $email = strtolower(trim($this->request->email));
            $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $email;

            // Pre-check conditions
            $response = $this->checkConditions();
            if ($response['code'] !== 200) {
                return $response;
            }

            // Authenticate user
            if (
                !Auth::attempt([
                    'email' => $email,
                    'password' => $this->request->password
                ])
            ) {
                return [
                    'status' => 'failed',
                    'code' => 401,
                    'message' => __('login_validation.invalid_credentials'),
                    'user' => null,
                ];
            }

            /** @var User $user */
            $user = Auth::user();

            // Load relation (avoid multiple queries)
            $user->load('agentDetail');
            $agentDetails = $user->agentDetail;

            // Create token
            $token = $user->createToken($user->email);

            // Update login timestamps
            $user->update([
                'last_logged_in' => $user->current_logged_in ?? now(),
                'current_logged_in' => now(),
                'ip_address' => $this->request->ip(),
            ]);

            // Base URL for images
            $baseUrl = rtrim(env('MEDIA_URL', ''), '/');

            // Image URLs (photo, aadhar, qr)
            $photoUrl = CommonHelpher::getImageUrl($agentDetails->photo  ?? null, $baseUrl.'/agent/photo/');
            $aadharUrl = CommonHelpher::getImageUrl($agentDetails->aadhar_card  ?? null, $baseUrl . '/agent/aadhar/');
            $qrCodeUrl = CommonHelpher::getImageUrl($agentDetails->qr_code  ?? null, $baseUrl . '/agent/qr/');

            // API Key
            $api_key = DB::table('api_keys')
                ->where('user_id', $user->user_id)
                ->value('key');

            // Languages
            $all_languages = LanguageHelpher::countryLanuages($user->country_code);

            $data = [
                'token' => $token->accessToken,
                'user' => $user,
                'agentDetails' => $agentDetails,

                'photo_url' => $photoUrl,
                'aadhar_card_url' => $aadharUrl,
                'qr_code_url' => $qrCodeUrl,

                'api_key' => $api_key ? Crypt::encryptString($api_key) : null,
                'all_languages' => $all_languages,
            ];

            // Cache user (1 hour)
            Cache::put($cacheKey, $user, now()->addMinutes(60));

            return [
                'status' => 'success',
                'code' => 200,
                'message' => __('validation.Login successful'),
                'user' => $data,
            ];

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
        $email = $this->request->email;
        $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $email;

        // Retrieve user from cache or database
        $user = Cache::remember($cacheKey, now()->addHours(1), function () use ($email) {
            return DB::table('users')->where('email', $email)->first();
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

        // $user = User::where('email', $this->request->email)->first();
        // // if user found but not found agent details then restirct it 
        // if ($user && !$user->agentDetail()->exists()) {

        //     // CommonHelpher::deletePreviousUserRecord($this->request->email);

        //     return [
        //         'status' => 'failed',
        //         'message' => __('validation.Invalid credentials.'),
        //         'code' => 404,
        //         'user' => null,
        //     ];
        // }
        // // if user found but not found agent details then restirct it 

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


        // CHECK EMAIL ID IS REGISTERED BUT PROFILE IS NOT COMPLETED
        $checkAgent = CommonHelpher::checkUserAgentDetails($this->request->email);

        // if ($checkAgent['user_id'] == 0) {
        //     // User doesn't exist at all — handle separately or let login fail naturally
        // }

        if ($checkAgent['user_id'] != 0) {

            if ($checkAgent['checkAgent'] == 0 || $checkAgent['checkAgentDetails'] == 0) {
                return [
                    'code' => 201,
                    'message' => __('validation.User Exists but agent details is not present'),
                    'errors' => '',
                    'user' => $checkAgent,
                ];
            }

            if ($checkAgent['checkAgentDocuments'] == 0) {
                return [
                    'code' => 201,
                    'message' => __('validation.Agent documents are incomplete'),
                    'errors' => '',
                    'user' => $checkAgent,
                ];
            }
        }
        // CHECK EMAIL ID IS REGISTERED BUT PROFILE IS NOT COMPLETED

        // Check if user is active
        if ($userModel->isVerified == 0) {
            return [
                'status' => 'email-not-verified',
                'message' => __('validation.agent_email_verification'),
                'code' => 403,
                'user' => $userModel,
            ];
        }

        // Check if user is active
        if ($userModel->active == 0) {
            return [
                'status' => 'user-deactivate',
                'message' => __('validation.agent_account_ativation'),
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

    // private function getImageUrl($file, $baseUrl)
    // {
    //     if (!empty($file) && $file !== 'NA') {
    //         return rtrim($baseUrl, '/') . '/' . ltrim($file, '/');
    //     }

    //     return asset('assets/img/user.jpg'); // fallback
    // }

}