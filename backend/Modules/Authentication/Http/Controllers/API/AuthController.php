<?php

namespace Modules\Authentication\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

use Modules\GroceryIndia\Entities\Staff;
use Modules\GroceryIndia\Entities\Shop;
use Modules\Core\Entities\OTP;
use Modules\Authentication\Entities\User;
use Modules\Core\Entities\Subscription;

use Modules\Core\Helpers\ResponseHelper;
use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;
use Modules\Core\Helpers\LanguageHelpher;
use Modules\Core\Helpers\CountryHelpher;

use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\ValidCountryCode;
use Modules\Core\Rules\NoScriptTag;
use Modules\Authentication\Rules\GstinRule;
use Modules\Core\Rules\NoSpecialCharacter;

class AuthController extends Controller
{
    public function logout(Request $request)
    {

        if (Auth::guard('api')->check()) {


            // Validate request data
            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'errors' => $validator->errors()
                ], 422);
            }


            $user = Auth::guard('api')->user();


            $deviceToken = $request->device_token;


            // Use DB query builder with 'central' connection
            $exists = DB::connection('central')
                ->table('push_notification_tokens')
                ->where('device_token', $deviceToken)
                ->exists();

            if ($exists) {
                DB::connection('central')
                    ->table('push_notification_tokens')
                    ->where('device_token', $deviceToken)
                    ->where('user_id', $user->user_id)
                    ->delete();
            }

            $all_tokens = $user->tokens;

            // Iterate through each token and delete expired ones
            foreach ($all_tokens as $token) {
                if ($token->expires_at && Carbon::now()->gt($token->expires_at)) {
                    $token->delete();
                }
            }

            // Delete the current token
            $current_token = $user->token();
            if ($current_token) {
                $current_token->delete();
            }

            Auth::logout();
            // Clear the session
            // $request->session()->invalidate();

            // // Delete the access token from the session
            // $request->session()->forget('access_token');
            // $request->session()->forget('api_key');

            // Remove the cache associated with the user's mobile number
            $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile;
            Cache::forget($cacheKey);

            return response()->json([
                'status' => 'success',
                'message' => __('validation.Successfully logout'),
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);

    }


    public function getUserDetails()
    {
        if (Auth::guard('api')->check()) {

            $user_id = Auth::guard('api')->user()->user_id;

            // $user = User::find($user_id);
            $user = User::where('user_id', $user_id)->first();

            $isPhoto = 0;
            $staffImageUrl = '';

            $entity_id = '';
            $shopId = 0;

            $subscription_expiry_date = '';

            $preferences = [];

            if ($user->isAdmin == 1) {

                $user['details'] = $user->shop;
                $user['logo'] = $user->shop->logo;

                $api_key = $user['apiKey']['key'];

                $entity_id = (new \DateTime($user->shop->created_at))->format('dmY') . $user->shop->shop_id;

                $user['api_key'] = Crypt::encryptString($api_key);
                $shopId = $user->shop->shop_id;

                $shop = DB::table('shops')->where('user_id', $user->user_id)->first();

                $preferences = $user->preference;

            } else if ($user->isAdmin == 0) {

                $user['details'] = $user->staff;
                $shop = DB::table('shops')->where('shop_id', $user->staff->addedBy)->first();


                $shopId = $shop->shop_id;

                $user['details']['business_name'] = $shop->business_name;
                $user['details']['gstin'] = $shop->gstin;
                $user['logo'] = $shop->logo;

                $user['photo'] = $user->staff->photo;

                if (isset($user->staff->photo) && $user->staff->photo != 'NA') {

                    // staff photo
                    $media_url = env('MEDIA_URL');
                    $photo = ($user->staff->photo != 'NA' && $user->staff->photo != '') ? $user->staff->photo : '';
                    $staffImageUrl = $media_url . '/photo/' . $photo;
                    $isPhoto = 1;

                    if (!$photo && !@getimagesize($staffImageUrl)) {
                        $staffImageUrl = asset("assets/img/user.jpg");
                        $isPhoto = 0;
                    }
                    // staff photo
                } else {
                    $staffImageUrl = asset("assets/img/user.jpg");
                    $isPhoto = 0;
                }

                $entity_id = (new \DateTime('2024-12-26 12:17:17'))->format('dmY') . $shop->shop_id;

                $preferences = DB::table('preferences')->where('user_id', $shop->user_id)->first();

            }

            // Retrieve base URL from environment, with a fallback
            $logo_url = env('LOGO_URL', '');

            // Initialize variables
            $logo = ($user->logo && $user->logo !== 'NA') ? $user->logo : '';
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

            // logo

            $user->makeHidden(['apiKey']);


            $shop_subscription = SubscriptionHelper::getShopSubscription($shop->shop_id, $user->module_type);


            if (isset($user['country_details'])) {
                $user['country_details'] = json_decode($user['country_details']);
            }

            $all_languages = LanguageHelpher::countryLanuages($user->country_code);

            $item_categories = UserHelper::getCategories($user->user_id);

            if ($shop_subscription['code'] == 200) {
                return response()->json([
                    'status' => 'success',
                    'isAdmin' => $user->isAdmin,
                    'shop_id' => $shopId,
                    'data' => $user,
                    'logo' => $imageUrl,
                    'isLogo' => $isLogo,
                    'staffPhoto' => $staffImageUrl,
                    'isPhoto' => $isPhoto,
                    'entity_id' => $entity_id,
                    'preferences' => $preferences,
                    'shop_subscription' => $shop_subscription,
                    'all_languages' => $all_languages,
                    'item_categories' => $item_categories,

                ], 200);
            } else {

                return [
                    'status' => 'failed',
                    'message' => __('validation.An unexpected error occured.'),
                    'code' => 404,
                    'user' => '',
                ];

            }


        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function updateUserProfile(Request $request)
    {

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $validate = Validator::make($request->all(), [
                'user_id' => 'required|numeric|exists:central.users',
                'name' => [
                    'required',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoSpecialCharacter('name')
                ],

                'email' => [
                    'nullable',
                    'string',
                    'max:250',
                    new NoScriptTag,
                    new NoSpecialCharacter('email'),
                    function ($attribute, $value, $fail) use ($request, $user) {
                        if ($value !== 'NA') {
                            $validator = Validator::make(['email' => $value], [
                                'email' => [
                                    'email:rfc,dns',
                                    function ($attribute, $value, $fail) use ($request, $user) {

                                        $groceryDb = "{$user->module_type}";

                                        $existsInShops = DB::connection($groceryDb)->table("shops")
                                            ->where('email', $value)
                                            ->where('user_id', '!=', $request->user_id)
                                            ->exists();

                                        $existsInStaff = DB::connection($groceryDb)->table("staff")
                                            ->where('email', $value)
                                            ->exists();

                                        if ($existsInShops || $existsInStaff) {
                                            $fail(__('validation.The email has already registered.'));
                                        }
                                    }
                                ],
                            ]);

                            if ($validator->fails()) {
                                $fail($validator->errors()->first('email'));
                            }
                        }
                    },
                ],

                'address' => ['required', 'max:500', new NoScriptTag, new NoSpecialCharacter('address')],

                'shop_type' => ['required', Rule::in(array_values(config('general.shop_type')))],
                
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
                            ->where('user_id', '!=', $request->user_id)
                            ->exists();

                        if ($existsInShops) {
                            $fail(__('validation.The GST Number has already registered'));
                        }
                    },
                ],

                'logo' => 'nullable|image|mimes:jpeg,png,gif,jpg|max:5120',
                
                // 'logo' => [
                //     'nullable',
                //     'file',
                //     'mimetypes:image/jpeg,image/png,image/gif,image/heic',
                //     'max:5120'
                // ],

                'isLogoDelete' => 'required|boolean',
                'photo' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,gif,jpg,heic',
                    'max:5120',
                    function ($attribute, $value, $fail) use ($request) {
                        $user = User::find($request->user_id);
                        if ($user && $user->isAdmin == 0 && !$value) {
                            $fail('The ' . $attribute . ' field is required for non-admin users.');
                        }
                    },
                ],

                'isPhotoDelete' => [
                    'nullable',
                    'boolean',
                    function ($attribute, $value, $fail) use ($request) {
                        $user = User::find($request->user_id);
                        if ($user && $user->isAdmin == 0 && !$value) {
                            $fail('The ' . $attribute . ' field is required for non-admin users.');
                        }
                    },
                ]

            ], [
                'user_id.required' => __('profile.required', ['attribute' => __('profile.attributes.user_id')]),
                'user_id.numeric' => __('profile.numeric', ['attribute' => __('profile.attributes.user_id')]),
                'user_id.exists' => __('profile.exists', ['attribute' => __('profile.attributes.user_id')]),
                'name.required' => __('profile.required', ['attribute' => __('profile.attributes.name')]),
                'name.string' => __('profile.string', ['attribute' => __('profile.attributes.name')]),
                'name.max' => __('profile.max', ['attribute' => __('profile.attributes.name'), 'max' => 250]),
                'email.string' => __('profile.string', ['attribute' => __('profile.attributes.email')]),
                'email.max' => __('profile.max', ['attribute' => __('profile.attributes.email'), 'max' => 250]),
                'email.email' => __('profile.email', ['attribute' => __('profile.attributes.email')]),
                'address.required' => __('profile.required', ['attribute' => __('profile.attributes.address')]),
                'address.max' => __('profile.max', ['attribute' => __('profile.attributes.address'), 'max' => 500]),
                'shop_type.required' => __('profile.required', ['attribute' => __('profile.attributes.shop_type')]),
                'shop_type.in' => __('profile.invalid', ['attribute' => __('profile.attributes.shop_type')]),
                'gstin.required' => __('profile.required', ['attribute' => __('profile.attributes.gstin')]),
                'logo.image' => __('profile.image', ['attribute' => __('profile.attributes.logo')]),
                'logo.mimes' => __('profile.mimes', ['attribute' => __('profile.attributes.logo'), 'values' => 'jpeg, png, gif, jpg']),
                'logo.max' => __('profile.max', ['attribute' => __('profile.attributes.logo'), 'max' => '5MB']),
                'isLogoDelete.required' => __('profile.required', ['attribute' => __('profile.attributes.isLogoDelete')]),
                'isLogoDelete.boolean' => __('profile.boolean', ['attribute' => __('profile.attributes.isLogoDelete')]),
                'photo.image' => __('profile.image', ['attribute' => __('profile.attributes.photo')]),
                'photo.mimes' => __('profile.mimes', ['attribute' => __('profile.attributes.photo'), 'values' => 'jpeg, png, gif, jpg, heic']),
                'photo.max' => __('profile.max', ['attribute' => __('profile.attributes.photo'), 'max' => '5MB']),
                'isPhotoDelete.boolean' => __('profile.boolean', ['attribute' => __('profile.attributes.isPhotoDelete')]),
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Validation Error'),
                    'data' => $validate->errors(),
                ], 403);
            }


            $user = User::find($request->user_id);

            if ($user->isAdmin == 1) {

                // store logo 
                $imageName = 'NA';
                if ($request->hasFile('logo')) {

                    // $logo = $request->file('logo');
                    // // Validate real image content
                    // try {
                    //     $img = Image::make($logo->getRealPath());
                    // } catch (\Exception $e) {
                    //     throw \Illuminate\Validation\ValidationException::withMessages([
                    //         'logo' => ['Invalid image file. Please upload a valid image.']
                    //     ]);
                    // }

                    $mediaFolder = 'storage/logo';
                    if (!file_exists($mediaFolder)) {
                        mkdir($mediaFolder, 0777, true);
                    }

                    $image = $request->file('logo');
                    $imageName = time() . '_' . $image->getClientOriginalName();

                    // Store the image in the public/logo directory
                    $image->move('storage/logo', $imageName);

                    // Resize and optimize the image
                    $imagePath = 'storage/logo/' . $imageName;
                    $image = Image::make($imagePath);

                    // Get original image dimensions
                    $originalWidth = $image->width();
                    $originalHeight = $image->height();

                    // Calculate new height while preserving aspect ratio
                    $newWidth = 200;
                    $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));

                    // Resize the image
                    $resizedImage = $image->resize($newWidth, $newHeight)
                        ->save('storage/logo/' . $imageName);

                } else if ($request->isLogoDelete == 1) {
                    $imageName = 'NA';
                } else {
                    $imageName = $user->shop->logo;
                }
                // store logo 

                $shop = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'address' => $request->address,
                    'gstin' => $request->gstin,
                    'logo' => $imageName,
                ];
                DB::table('shops')->where('user_id', $request->user_id)->update($shop);


            } else if ($user->isAdmin == 0) {


                // store photo
                $photoName = 'NA';
                if ($request->hasFile('photo')) {

                    $mediaFolder = 'storage/photo';
                    if (!file_exists($mediaFolder)) {
                        mkdir($mediaFolder, 0777, true);
                    }

                    $image = $request->file('photo');
                    $photoName = time() . '_' . $image->getClientOriginalName();

                    // Store the image in the public/logo directory
                    $image->move('storage/photo', $photoName);

                    // Resize and optimize the image
                    $imagePath = 'storage/photo/' . $photoName;
                    $image = Image::make($imagePath);

                    // Get original image dimensions
                    $originalWidth = $image->width();
                    $originalHeight = $image->height();

                    // Calculate new height while preserving aspect ratio
                    $newWidth = 200;
                    $newHeight = ceil($originalHeight * ($newWidth / $originalWidth));

                    // Resize the image
                    $resizedImage = $image->resize($newWidth, $newHeight)
                        ->save('storage/photo/' . $photoName);
                } else if ($request->isPhotoDelete == 1) {
                    $photoName = 'NA';
                } else {
                    $photoName = $user->staff->photo;
                }
                // store photo


                $staff = [
                    'name' => $request->name,
                    'address' => $request->address,
                    'photo' => $photoName,
                ];

                DB::table(table: 'staff')->where('user_id', $request->user_id)->update($staff);

            }

            // $user = DB::table('users')->find($request->id);
            // $user = User::with(['shop', 'staff'])->find($request->user_id);

            // Cache::store('memcached')->forget('sub_users_' . $loggedInUser->mobile);

            // Store updated user data in cache
            $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile;
            Cache::put($cacheKey, $user);

            $response = [
                'status' => 'success',
                'message' => __('validation.User data is updated successfully.'),
                'data' => $user
            ];
            return response()->json($response, 201);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function checkApiKey(Request $request)
    {

        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

            $userAssignedApiKey = $user->apiKey->key;

            $apiKeyFromHeader = Crypt::decryptString($request->key);
            // $apiKeyFromHeader = $request->key;

            if ($userAssignedApiKey == $apiKeyFromHeader) {
                $response = [
                    'status' => 'success',
                    'message' => __('validation.Valid API Key'),
                ];
                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.API Key Not Found'),
                ], 200);
            }

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function updateMobileNumber(Request $request)
    {

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();


            $validate = Validator::make($request->all(), [
                'user_id' => 'required|numeric|exists:central.users',

                'mobile' => [
                    'nullable',
                    new PhoneNumber($request->country_code),
                    Rule::unique('central.users')->ignore($request->user_id, 'user_id'),
                    function ($attribute, $value, $fail) {
                        // Check if the mobile number exists in the 'opts' table and if 'isVerify' is 1
                        // $isVerified = DB::table('readybill_central.o_t_p_s')
                        $isVerified = DB::connection('central')->table('o_t_p_s')
                            ->where('mobile', $value)
                            ->where('isVerify', 0)
                            ->exists();

                        if (!$isVerified) {
                            $fail(__('profile.The mobile number is not verified. Please try again'));
                        }
                    },
                ],

                'country_code' => [
                    'required',
                    new ValidCountryCode()
                ],

                'otp' => 'required|digits:6',

            ], [
                'user_id.required' => __('profile.required', ['attribute' => __('profile.attributes.user_id')]),
                'user_id.numeric' => __('profile.numeric', ['attribute' => __('profile.attributes.user_id')]),
                'user_id.exists' => __('profile.exists', ['attribute' => __('profile.attributes.user_id')]),

                'mobile.unique' => __('register_validation.mobile_already_registered'),
                'country_code.required' => __('register_validation.required', ['attribute' => __('register_validation.attributes.country_code')]),
                'otp.required' => __('profile.required', ['attribute' => __('profile.attributes.otp')]),
            ]);


            if ($validate->fails()) {
                $data = [
                    'errors' => $validate->errors()
                ];

                return ResponseHelper::responseFn(0, 400, __('validation.Validation Error'), $data);
            }

            // Constants for OTP expiry and maximum attempts
            $OTP_Expiry = env('OTP_EXPIRY'); // OTP expires in 5 minutes
            $OTP_Count = (int) env('OTP_COUNT');   // Maximum of 3 attempts


            // Retrieve the OTP record for the phone number
            $otp = Otp::where('mobile', $request->mobile)->first();

            // Check if the OTP record exists
            if (!$otp) {
                $data = [];
                return ResponseHelper::responseFn(0, 404, __('validation.No OTP record found for this phone number'), $data);
            }

            // Check if the OTP is expired
            $createdTime = Carbon::parse($otp->created_at);
            $expiryTime = $createdTime->addMinutes($OTP_Expiry);

            if (Carbon::now()->gt($expiryTime)) {
                $data = [];
                return ResponseHelper::responseFn(0, 410, __('validation.OTP has expired. Please request a new OTP'), $data);
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
                    return ResponseHelper::responseFn(0, 429, __('validation.Maximum attempts reached'), $data);
                }

                // $attempts_left = "Invalid OTP. Please try again. Attempt Left - " . ($OTP_Count - $otp->count);
                $attempts_left = __('validation.Invalid OTP. Please try again. Attempt Left') . " - " . ($OTP_Count - $otp->count);
                $data = [
                    // 'attempts_left' => $OTP_Count - $otp->count
                    'errors' => [
                        'otp' => [$attempts_left], // Include your custom error message
                    ],
                ];
                return ResponseHelper::responseFn(0, 400, __('validation.Invalid OTP. Please try again'), $data);
            }

            // If OTP is valid, you can perform any action needed (e.g., log the user in)
            $mobile = $otp->mobile;

            // $otp->isVerify = 1;
            // $otp->save();
            $otp->delete();



            $user = User::find($request->user_id);

            $old_mobile_number = $user->mobile;
            Cache::forget(env('CACHE_KEY_PREFIX') . 'user_' . $old_mobile_number);


            $user->mobile = $request->mobile;

            $country_details = CountryHelpher::getCountryJson($request->country_code);
            $user->country_details = json_encode($country_details);


            $user->save();

            $cacheKey = env('CACHE_KEY_PREFIX') . 'user_' . $user->mobile;
            Cache::put($cacheKey, $user);



            // UPDATE ITEM TABLE NAME
            $renameTable = UserHelper::renameTable($user->shop->business_name, $old_mobile_number, $user->mobile);
            // UPDATE ITEM TABLE NAME


            // $response = [
            //     'status' => 'success',
            //     'message' => 'User data is update successfully.',
            //     'data' => $user
            // ];
            // return response()->json($response, 201);


            $data = [
                'user' => $user,
            ];
            return ResponseHelper::responseFn(1, 200, __('validation.Mobile Number Successfully Updated'), $data);

        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);

    }

}
