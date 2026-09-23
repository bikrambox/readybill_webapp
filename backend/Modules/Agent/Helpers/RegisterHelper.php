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
use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;

use Modules\Core\Entities\OTP;
use Modules\Authentication\Entities\User;
use Modules\Agent\Entities\AgentDetails;
use Modules\GroceryIndia\Entities\Shop;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;
use Modules\Core\Rules\ValidLanguageCode;
use Modules\Core\Rules\NoScriptTag;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Mail;
use Modules\Agent\Emails\VerifyEmail;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\URL;

use Modules\Agent\Rules\ValidPaymentQrCode;

class RegisterHelper
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    // -------------------------------------------------------------------------------------- REGISTER FORM (EMAIL INPUT) --------------------------------------------------------------------------------------

    protected function registerFormValidation()
    {

        // if ($this->request->email !== 'NA') {
        //     CommonHelpher::deletePreviousUserRecord($this->request->email);
        // }

        return Validator::make(
            $this->request->all(),
            [
                // 'email' => [
                //     'required',
                //     'string',
                //     'max:100',
                //     new NoScriptTag,
                //     function ($attribute, $value, $fail) {
                //         if ($value !== 'NA') {

                //             $user = DB::connection('central')
                //                 ->table('users')
                //                 ->where('email', $value)
                //                 ->where('isAgent', 1)
                //                 ->first();

                //             $checkAgentDetails = $existsInAgents = DB::connection('central')
                //                 ->table('agent_details')
                //                 ->where('user_id', $user->user_id)
                //                 ->exists();

                //             if ($existsInAgents) {
                //                 $fail(__('validation.The email has already been registered.'));
                //             }
                //         }
                //     },
                // ],

                'email' => [
                    'required',
                    'string',
                    'max:100',
                    new NoScriptTag,
                    function ($attribute, $value, $fail) {

                        if ($value === 'NA') {
                            return;
                        }

                        $user = DB::connection('central')
                            ->table('users')
                            ->select('user_id')
                            ->where('email', $value)
                            ->where('isAgent', 1)
                            ->first();

                        // If user not found → no need to proceed
                        if (!$user) {
                            return;
                        }

                        $existsInAgents = DB::connection('central')
                            ->table('agent_details')
                            ->where('user_id', $user->user_id)
                            ->where('photo', '!=','NA')
                            ->where('aadhar_card', '!=','NA')
                            ->where('qr_code', '!=','NA')
                            ->exists();

                        if ($existsInAgents) {
                            $fail(__('validation.The email has already been registered.'));
                        }
                    },
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    new NoScriptTag
                ],

                'detected_country_code' => [
                    'required',
                    new ValidCountryCode()
                ],

                'detected_language' => [
                    'nullable',
                    new ValidLanguageCode()
                ],

            ],
            [

                'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
                'email.string' => __('validation.string', ['attribute' => __('validation.attributes.email')]),
                'email.max' => __('validation.max', ['attribute' => __('validation.attributes.email'), 'min' => 8]),

                'password.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.password')]),
                'password.string' => __('register_validation.string', ['attribute' => __('register_validation.attributes.password')]),
                'password.min' => __('register_validation.min', ['attribute' => __('register_validation.attributes.password'), 'min' => 8]),
                'password.confirmed' => __('register_validation.confirmed', ['attribute' => __('register_validation.attributes.password')]),
                'detected_country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.detected_country_code')]),
                'detected_language.valid_detected_language' => __('reister_validation.custom.detected_language.valid_detected_language'),

            ]
        );
    }


    public function registerForm()
    {

        // Validate the incoming request
        $validator = $this->registerFormValidation();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }
        try {

            $this->request->country_details = CountryHelpher::getCountryJson($this->request->detected_country_code);


            // CHECK EMAIL ID IS REGISTERED BUT PROFILE IS NOT COMPLETED
            $checkAgent = CommonHelpher::checkUserAgentDetails($this->request->email);

            // if ($checkAgent['user_id'] == 0) {
            //     // User doesn't exist at all — handle separately or let login fail naturally
            // }

            if ($checkAgent['user_id'] != 0) {

                if ($checkAgent['checkAgent'] == 0 || $checkAgent['checkAgentDetails'] == 0) {
                    return [
                        'status' => 200,
                        'message' => __('validation.User Exists but agent details is not present'),
                        'errors' => '',
                        'user' => $checkAgent,
                    ];
                }

                if ($checkAgent['checkAgentDocuments'] == 0) {
                    return [
                        'status' => 200,
                        'message' => __('validation.Agent documents are incomplete'),
                        'errors' => '',
                        'user' => $checkAgent,
                    ];
                }
            }
            // CHECK EMAIL ID IS REGISTERED BUT PROFILE IS NOT COMPLETED


            // STORE DATA IN TABLE AFTER VERIFICATION
            // Create user
            $user = User::create([

                'email' => $this->request->email,
                'password' => Hash::make($this->request->password),
                'ip_address' => $this->request->ip(),
                'isAdmin' => 0,
                'isAgent' => 1,
                'last_logged_in' => now(),
                'active' => 0,

                'shop_type' => 'NA',

                'country_details' => json_encode($this->request->country_details),
                'country_code' => strtolower($this->request->country_details['code']),

                'detected_country_code' => strtolower($this->request->detected_country_code),

                'lang' => strtolower($this->request->detected_language ?? $this->request->country_details['language_short_code']),

                'module_type' => 'NA',

            ]);
            // STORE DATA IN TABLE AFTER VERIFICATION


            // Return success response
            return [
                'status' => 200,
                'message' => 'Success',
                'user' => $user,
            ];


        } catch (\Exception $e) {
            report($e);
            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [], // Optional, include additional error details if needed
                'user' => null,
            ];
        }


    }
    // -------------------------------------------------------------------------------------- REGISTER FORM (EMAIL INPUT) --------------------------------------------------------------------------------------



    // -------------------------------------------------------------------------------------- AGENT DETAILS --------------------------------------------------------------------------------------
    protected function agentDetailsValidation()
    {

        return Validator::make(
            $this->request->all(),
            [
                'user_id' => 'required|exists:users,user_id',
                'name' => ['required', 'string', 'max:250', new NoScriptTag],
                'address' => ['required', new NoScriptTag],

                'country_code' => [
                    'required',
                    new ValidCountryCode()
                ],

                'mobile' => [
                    'required',
                    'unique:users,mobile',
                    'regex:/^[6-9]\d{9}$/',
                ],

                'pan_number' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],

                // 'photo' => 'required|image|mimes:jpeg,png,gif,jpg,heic|max:5120', // 5 mb
                // 'aadhar_card' => 'required|image|mimes:jpeg,png,gif,jpg,heic|max:5120', // 5 mb
                // 'qr_code' => 'required|image|mimes:jpeg,png,gif,jpg,heic|max:5120', // 5 mb

            ],
            [

                'user_id.required' => __('validation.required', ['attribute' => __('validation.attributes.user_id')]),
                'user_id.exsits' => __('register_validation.exsits', ['attribute' => __('register_validation.attributes.user_id')]),

                'name.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.name')]),
                'name.string' => __('register_validation.string', ['attribute' => __('register_validation.attributes.name')]),
                'name.max' => __('register_validation.max', ['attribute' => __('register_validation.attributes.name'), 'max' => 250]),
                'address.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.address')]),

                'country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),


                'pan_number.required' => __('validation.required', ['attribute' => __('validation.attributes.address')]),
                'pan_number.regex' => __('validation.regex_pan_number', ['attribute' => __('validation.attributes.pan_number')]),

                // 'photo.image' => __('validation.image', ['attribute' => __('validation.attributes.photo')]),
                // 'photo.mimes' => __('validation.mimes', ['attribute' => __('validation.attributes.photo'), 'values' => 'jpeg, png, gif, jpg, heic']),
                // 'photo.max' => __('validation.max', ['attribute' => __('validation.attributes.photo'), 'max' => '5MB']),


                // 'aadhar_card.image' => __('validation.image', ['attribute' => __('validation.attributes.aadhar_card')]),
                // 'aadhar_card.mimes' => __('validation.mimes', ['attribute' => __('validation.attributes.aadhar_card'), 'values' => 'jpeg, png, gif, jpg, heic']),
                // 'aadhar_card.max' => __('validation.max', ['attribute' => __('validation.attributes.aadhar_card'), 'max' => '5MB']),

                // 'qr_code.image' => __('validation.image', ['attribute' => __('validation.attributes.qr_code')]),
                // 'qr_code.mimes' => __('validation.mimes', ['attribute' => __('validation.attributes.qr_code'), 'values' => 'jpeg, png, gif, jpg, heic']),
                // 'qr_code.max' => __('validation.max', ['attribute' => __('validation.attributes.qr_code'), 'max' => '5MB']),


            ]
        );

    }


    public function agentDetails()
    {
        // Validate request
        $validator = $this->agentDetailsValidation();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        DB::beginTransaction();

        try {
            // Fetch user
            $user = User::find($this->request->user_id);

            if (!$user) {
                return [
                    'status' => 404,
                    'message' => 'User not found',
                    'user' => null,
                ];
            }

            // Get country details
            $countryDetails = CountryHelpher::getCountryJson($this->request->country_code);

            // Update user
            $user->update([
                'country_details' => json_encode($countryDetails),
                'country_code' => strtolower($countryDetails['code'] ?? ''),
                'mobile' => $this->request->mobile,
            ]);

            // Create agent details
            $agentDetails = AgentDetails::create([
                'name' => $this->request->name,
                'user_id' => $this->request->user_id,
                'address' => $this->request->address,
                'pan_number' => $this->request->pan_number,
                'photo' => 'NA',
                'aadhar_card' => 'NA',
                'qr_code' => 'NA',
            ]);

            DB::commit();

            return [
                'status' => 200,
                'message' => 'Success',
                'user' => $agentDetails,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [],
                'user' => null,
            ];
        }
    }
    // -------------------------------------------------------------------------------------- AGENT DETAILS --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- AGENT DOCUMENTS UPLOAD --------------------------------------------------------------------------------------
    protected function agentDocumentsUloadValidation()
    {

        return Validator::make(
            $this->request->all(),
            [
                // 'user_id' => 'required|exists:users,user_id',
                'agent_details_id' => 'required|exists:agent_details,id',

                'photo' => 'required|image|mimes:jpeg,png,gif,jpg,heic|max:5120', // 5 mb
                'aadhar_card' => 'required|image|mimes:jpeg,png,gif,jpg,heic,webp|max:5120', // 5 mb
                'qr_code' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,gif,jpg,heic,webp',
                    'max:5120',
                    new ValidPaymentQrCode(),
                ], // 5 mb

            ],
            [
                // 'user_id.required' => __('validation.required', ['attribute' => __('validation.attributes.user_id')]),
                // 'user_id.exsits' => __('register_validation.exsits', ['attribute' => __('register_validation.attributes.user_id')]),

                'agent_details_id.required' => __('validation.required', ['attribute' => __('validation.attributes.agent_details_id')]),
                'agent_details_id.exists' => __('register_validation.exists', ['attribute' => __('register_validation.attributes.agent_details_id')]),

                'photo.image' => __('validation.image', ['attribute' => __('validation.attributes.photo')]),
                'photo.mimes' => __('validation.mimes', ['attribute' => __('validation.attributes.photo'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'photo.max' => __('validation.max', ['attribute' => __('validation.attributes.photo'), 'max' => '5MB']),


                'aadhar_card.image' => __('validation.image', ['attribute' => __('validation.attributes.aadhar_card')]),
                'aadhar_card.mimes' => __('validation.mimes', ['attribute' => __('validation.attributes.aadhar_card'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'aadhar_card.max' => __('validation.max', ['attribute' => __('validation.attributes.aadhar_card'), 'max' => '5MB']),

                'qr_code.image' => __('validation.image', ['attribute' => __('validation.attributes.qr_code')]),
                'qr_code.mimes' => __('validation.mimes', ['attribute' => __('validation.attributes.qr_code'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'qr_code.max' => __('validation.max', ['attribute' => __('validation.attributes.qr_code'), 'max' => '5MB']),

            ]
        );
    }

    public function agentDocumentsUpload()
    {
        $validator = $this->agentDocumentsUloadValidation();

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => __('validation.Invalid input'),
                'errors' => $validator->errors(),
                'user' => null,
            ];
        }

        try {
            $agentDetails = AgentDetails::find($this->request->agent_details_id);

            if (!$agentDetails) {
                return ['status' => 404, 'message' => 'Agent details not found', 'user' => null];
            }

            $user = User::find($agentDetails->user_id);

            if (!$user) {
                return ['status' => 404, 'message' => 'Associated user not found', 'user' => null];
            }

            $updateData = [];

            foreach (['photo' => 'agent/photo', 'aadhar_card' => 'agent/aadhar', 'qr_code' => 'agent/qr'] as $field => $path) {
                if ($this->request->hasFile($field)) {
                    $updateData[$field] = CommonHelpher::processPhoto($this->request, $field, $path);
                }
            }

            $token = Str::random(64);

            DB::transaction(function () use ($agentDetails, $user, $updateData, $token) {
                if (!empty($updateData)) {
                    $agentDetails->update($updateData);
                }
                $user->email_verification_token = $token;
                $user->save();


                // GENERATE API KEY
                $api_key = UserHelper::apiKeyGenerate($user->user_id);
                // GENERATE API KEY

            });

            $activationUrl = URL::temporarySignedRoute(
                'agent.activate.account',
                now()->addHours(24), // ⏱ 24 hours validity
                // now()->addMinutes(1),
                ['token' => $token]
            );

            Mail::to($user->email)->send(new VerifyEmail($token, $activationUrl));

            return [
                'status' => 200,
                'message' => 'Success',
                'user' => $agentDetails->fresh(),
            ];

        } catch (\Exception $e) {
            report($e);

            return [
                'status' => 500,
                'message' => __('validation.unable_to_process'),
                'errors' => [],
                'user' => null,
            ];
        }
    }

    // -------------------------------------------------------------------------------------- AGENT DOCUMENTS UPLOAD --------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------- PROCESS PHOTO --------------------------------------------------------------------------------------
    // public function processPhoto($fieldName, $folderName)
    // {
    //     $imageName = '';

    //     if ($this->request->hasFile($fieldName)) {

    //         $mediaFolder = storage_path('app/public/' . $folderName);

    //         if (!file_exists($mediaFolder)) {
    //             mkdir($mediaFolder, 0777, true);
    //         }

    //         $image = $this->request->file($fieldName);
    //         $imageName = time() . '_' . $image->getClientOriginalName();

    //         $image->move($mediaFolder, $imageName);

    //         $imagePath = $mediaFolder . '/' . $imageName;

    //         $image = Image::make($imagePath);

    //         $originalWidth = $image->width();
    //         $originalHeight = $image->height();

    //         $newWidth = 200;
    //         $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));

    //         $image->resize($newWidth, $newHeight)->save($imagePath);
    //     }

    //     return $imageName;
    // }

    // -------------------------------------------------------------------------------------- PROCESS PHOTO --------------------------------------------------------------------------------------
    
    

}
