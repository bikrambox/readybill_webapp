<?php

namespace Modules\Agent\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use Modules\Authentication\Entities\User;
use Modules\Agent\Entities\AgentDetails;
use Modules\Agent\Entities\UserEmailUpdateRequest;

use Modules\Core\Rules\NoScriptTag;
use Modules\Core\Rules\PhoneNumber;
use Modules\Agent\Rules\ValidPaymentQrCode;
use Modules\Core\Rules\ValidCountryCode;

use Modules\Agent\Helpers\CommonHelpher;
use Modules\Core\Helpers\CountryHelpher;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Mail;
use Modules\Agent\Emails\VerifyUpdatedEmail;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function logout(Request $request)
    {

        if (Auth::guard('api')->check()) {

            $user = Auth::guard('api')->user();

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
            $user = User::where('user_id', $user_id)->first();

            // Load relation (avoid multiple queries)
            $user->load('agentDetail');
            $agentDetails = $user->agentDetail;

            // Base URL for images
            $baseUrl = rtrim(env('MEDIA_URL', ''), '/');

            // Image URLs (photo, aadhar, qr)
            $photoUrl = CommonHelpher::getImageUrl($agentDetails->photo ?? null, $baseUrl . '/agent/photo/');
            $aadharUrl = CommonHelpher::getImageUrl($agentDetails->aadhar_card ?? null, $baseUrl . '/agent/aadhar/');
            $qrCodeUrl = CommonHelpher::getImageUrl($agentDetails->qr_code ?? null, $baseUrl . '/agent/qr/');

            // API Key
            $api_key = DB::table('api_keys')
                ->where('user_id', $user->user_id)
                ->value('key');

            $data = [
                'user' => $user,
                'agentDetails' => $agentDetails,

                'photo_url' => $photoUrl,
                'aadhar_card_url' => $aadharUrl,
                'qr_code_url' => $qrCodeUrl,
                'api_key' => $api_key ? Crypt::encryptString($api_key) : null,

            ];


            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);

        }
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }


    public function updateProfile(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $user_id = $user->user_id;

        $validate = Validator::make($request->all(), [

            'email' => [
                'required',
                'string',
                'max:100',
                new NoScriptTag,
                function ($attribute, $value, $fail) use ($user_id) {
                    if ($value !== 'NA') {
                        $existingUser = DB::connection('central')
                            ->table('users')
                            ->where('email', $value)
                            // ->where('isAgent', 1)
                            ->first();

                        if ($existingUser && $existingUser->user_id != $user_id) {
                            $fail(__('validation.The email has already been registered.'));
                        }
                    }
                },
            ],

            'name' => ['required', 'string', 'max:250', new NoScriptTag],
            'address' => ['required', new NoScriptTag],

            'country_code' => [
                'required',
                new ValidCountryCode()
            ],

            'mobile' => [
                'required',
                new PhoneNumber($request->country_code),
                function ($attribute, $value, $fail) use ($user_id) {

                    $existingUser = DB::table('users')
                        ->where('mobile', $value)
                        ->first();

                    if ($existingUser && $existingUser->user_id == $user_id) {
                        return;
                    }

                    if (!$existingUser) {
                        return;
                    }

                    $skipConnections = ['central'];
                    $ignoredDrivers = ['sqlite'];
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
                                    ->where('user_id', $existingUser->user_id)
                                    ->where('isAgent', 1)
                                    ->exists();

                                if ($exists) {
                                    $fail(__('register_validation.mobile_already_registered'));
                                    return;
                                }

                            } catch (\Throwable $e) {
                                \Log::warning("Validation DB check failed", [
                                    'connection' => $connectionName,
                                    'table' => $table,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                },
            ],

            'pan_number' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            'agent_details_id' => 'required|exists:agent_details,id',

            'photo' => 'nullable|image|mimes:jpeg,png,gif,jpg,heic|max:5120',
            'aadhar_card' => 'nullable|image|mimes:jpeg,png,gif,jpg,heic,webp|max:5120',

            'qr_code' => [
                'nullable',
                'image',
                'mimes:jpeg,png,gif,jpg,heic,webp',
                'max:5120',
                new ValidPaymentQrCode(),
            ],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error'),
                'data' => $validate->errors(),
            ], 403);
        }

        DB::beginTransaction();

        try {
            $countryDetails = CountryHelpher::getCountryJson($request->country_code);
            $emailUpdateRequest = null;
            $verificationUrl = null;

            if ($user->email !== $request->email) {
                UserEmailUpdateRequest::where('user_id', $user->user_id)
                    ->whereNull('verified_at')
                    ->whereNull('cancelled_at')
                    ->update([
                        'cancelled_at' => now(),
                    ]);

                $emailUpdateRequest = new UserEmailUpdateRequest();
                $emailUpdateRequest->user_id = $user->user_id;
                $emailUpdateRequest->old_email = $user->email;
                $emailUpdateRequest->new_email = $request->email;
                $emailUpdateRequest->token = Str::random(64);
                $emailUpdateRequest->expires_at = now()->addHours(24);
                $emailUpdateRequest->save();

                $verificationUrl = URL::temporarySignedRoute(
                    'agent.email-update.verify',
                    $emailUpdateRequest->expires_at,
                    [
                        'request_id' => $emailUpdateRequest->id,
                        'token' => $emailUpdateRequest->token,
                    ]
                );
            }

            $user->update([
                'country_details' => $countryDetails,
                'country_code' => strtolower($countryDetails['code'] ?? ''),
                'mobile' => $request->mobile,
            ]);

            $agentDetails = $user->agentDetail;

            $updateData = [
                'name' => $request->name,
                'address' => $request->address,
                'mobile' => $request->mobile,
                'pan_number' => $request->pan_number,
            ];

            foreach ([
                'photo' => 'agent/photo',
                'aadhar_card' => 'agent/aadhar',
                'qr_code' => 'agent/qr'
            ] as $field => $path) {
                if ($request->file($field)) {
                    $updateData[$field] = CommonHelpher::processPhoto($request, $field, $path);
                }
            }

            $agentDetails->update($updateData);

            DB::commit();

            if ($emailUpdateRequest) {
                Mail::to($emailUpdateRequest->new_email)
                    ->send(new VerifyUpdatedEmail($emailUpdateRequest->token, $verificationUrl));
            }

            return response()->json([
                'status' => 'success',
                'message' => $emailUpdateRequest
                    ? 'Profile updated successfully. Please verify your new email address.'
                    : 'Profile updated successfully',
                'data' => [
                    'email_verification_required' => (bool) $emailUpdateRequest,
                    'new_email' => $emailUpdateRequest?->new_email,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => 'failed',
                'message' => __('validation.unable_to_process'),
                'errors' => [],
            ], 500);
        }
    }

    // public function updateProfile(Request $request)
    // {
    //     // ✅ Unauthorized check first
    //     if (!Auth::guard('api')->check()) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.Unauthorized')
    //         ], 401);
    //     }

    //     $user = Auth::guard('api')->user();
    //     $user_id = $user->user_id;

    //     // ✅ Validation
    //     $validate = Validator::make($request->all(), [

    //         'email' => [
    //             'required',
    //             'string',
    //             'max:100',
    //             new NoScriptTag,
    //             function ($attribute, $value, $fail) use ($user_id) {

    //                 if ($value !== 'NA') {

    //                     $existingUser = DB::connection('central')
    //                         ->table('users')
    //                         ->where('email', $value)
    //                         ->where('isAgent', 1)
    //                         ->first();

    //                     if ($existingUser && $existingUser->user_id != $user_id) {
    //                         $fail(__('validation.The email has already been registered.'));
    //                     }
    //                 }
    //             },
    //         ],

    //         'name' => ['required', 'string', 'max:250', new NoScriptTag],
    //         'address' => ['required', new NoScriptTag],

    //         'country_code' => [
    //             'required',
    //             new ValidCountryCode()
    //         ],

    //         'mobile' => [
    //             'required',
    //             new PhoneNumber($request->country_code),
    //             function ($attribute, $value, $fail) use ($user_id) {

    //                 $existingUser = DB::table('users')
    //                     ->where('mobile', $value)
    //                     ->first();

    //                 // ✅ Allow if same user
    //                 if ($existingUser && $existingUser->user_id == $user_id) {
    //                     return;
    //                 }

    //                 if (!$existingUser) {
    //                     return;
    //                 }

    //                 $skipConnections = ['central'];
    //                 $ignoredDrivers = ['sqlite'];
    //                 $tablesToCheck = ['shops', 'staff'];

    //                 foreach (config('database.connections') as $connectionName => $config) {

    //                     if (in_array($connectionName, $skipConnections, true)) {
    //                         continue;
    //                     }

    //                     if (in_array($config['driver'] ?? null, $ignoredDrivers, true)) {
    //                         continue;
    //                     }

    //                     foreach ($tablesToCheck as $table) {
    //                         try {
    //                             if (!Schema::connection($connectionName)->hasTable($table)) {
    //                                 continue;
    //                             }

    //                             $exists = DB::connection($connectionName)
    //                                 ->table($table)
    //                                 ->where('user_id', $existingUser->user_id)
    //                                 ->where('isAgent', 1)
    //                                 ->exists();

    //                             if ($exists) {
    //                                 $fail(__('register_validation.mobile_already_registered'));
    //                                 return;
    //                             }

    //                         } catch (\Throwable $e) {
    //                             \Log::warning(
    //                                 "Validation DB check failed",
    //                                 [
    //                                     'connection' => $connectionName,
    //                                     'table' => $table,
    //                                     'error' => $e->getMessage()
    //                                 ]
    //                             );
    //                         }
    //                     }
    //                 }
    //             },
    //         ],

    //         'pan_number' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],

    //         'agent_details_id' => 'required|exists:agent_details,id',

    //         // ✅ Make optional for update
    //         'photo' => 'nullable|image|mimes:jpeg,png,gif,jpg,heic|max:5120',
    //         'aadhar_card' => 'nullable|image|mimes:jpeg,png,gif,jpg,heic,webp|max:5120',

    //         'qr_code' => [
    //             'nullable',
    //             'image',
    //             'mimes:jpeg,png,gif,jpg,heic,webp',
    //             'max:5120',
    //             new ValidPaymentQrCode(),
    //         ],

    //     ]);

    //     if ($validate->fails()) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.Validation Error'),
    //             'data' => $validate->errors(),
    //         ], 403);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         // ✅ Get country details
    //         $countryDetails = CountryHelpher::getCountryJson($request->country_code);


    //         // if email is updated
    //         if($user->email != $request->email){
    //             // trigger a mail for email address verification
    //             $userEmailUpdateRequest = new UserEmailUpdateRequest();
    //             $userEmailUpdateRequest->user_id = $user->user_id;
    //             $userEmailUpdateRequest->old_email = $user->email;
    //             $userEmailUpdateRequest->new_email = $request->email;
    //             $userEmailUpdateRequest->token = Str::random(64);
    //             $userEmailUpdateRequest->expires_at = now()->addHours(24);
    //             $userEmailUpdateRequest->save();
    //             // trigger a mail for email address verification
    //         }
    //         // if email is updated


    //         // ✅ Update user
    //         $user->update([
    //             // 'email' => $request->email,
    //             'country_details' => $countryDetails, // if JSON column
    //             'country_code' => strtolower($countryDetails['code'] ?? ''),
    //             'mobile' => $request->mobile,
    //         ]);

    //         // ✅ Get agent details
    //         $agentDetails = $user->agentDetail;

    //         // ✅ Prepare update data
    //         $updateData = [
    //             'name' => $request->name,
    //             'address' => $request->address,
    //             'mobile' => $request->mobile,
    //             'pan_number' => $request->pan_number,
    //         ];

    //         // ✅ File uploads
    //         foreach ([
    //             'photo' => 'agent/photo',
    //             'aadhar_card' => 'agent/aadhar',
    //             'qr_code' => 'agent/qr'
    //         ] as $field => $path) {

    //             if ($request->file($field)) {
    //                 $updateData[$field] = CommonHelpher::processPhoto($request, $field, $path);
    //             }
    //         }

    //         // ✅ Update agent details
    //         $agentDetails->update($updateData);

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Profile updated successfully',
    //             'data' => []
    //         ], 200);

    //     } catch (\Exception $e) {

    //         DB::rollBack();
    //         report($e);

    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.unable_to_process'),
    //             'errors' => [],
    //         ], 500);
    //     }
    // }




    public function updateEmailAddress(Request $request, $token)
    {


        if (!$request->hasValidSignature()) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This verification link is invalid or has been tampered with.'
            ]);
        }

        $emailUpdateRequest = UserEmailUpdateRequest::where('token', $token)->first();

        if (!$emailUpdateRequest) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This verification link is invalid or no longer available.'
            ]);
        }

        if ($emailUpdateRequest->verified_at) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This verification link has already been used.'
            ]);
        }

        if (!empty($emailUpdateRequest->cancelled_at)) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This verification request has been cancelled.'
            ]);
        }

        if (now()->greaterThan($emailUpdateRequest->expires_at)) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This verification link has expired.'
            ]);
        }

        $user = User::where('user_id', $emailUpdateRequest->user_id)->first();

        if (!$user) {
            return view('agent::errors.invalid-signature', [
                'message' => 'The associated user account could not be found.'
            ]);
        }

        $emailAlreadyExists = User::where('email', $emailUpdateRequest->new_email)
            ->where('user_id', '!=', $user->user_id)
            ->exists();

        if ($emailAlreadyExists) {
            return view('agent::errors.invalid-signature', [
                'message' => 'This email address is already in use by another account.'
            ]);
        }

        DB::beginTransaction();

        try {
            $user->email = $emailUpdateRequest->new_email;
            $user->email_verified_at = now();
            $user->save();

            $emailUpdateRequest->verified_at = now();
            $emailUpdateRequest->save();

            UserEmailUpdateRequest::where('user_id', $user->user_id)
                ->where('id', '!=', $emailUpdateRequest->id)
                ->whereNull('verified_at')
                ->whereNull('cancelled_at')
                ->update([
                    'cancelled_at' => now(),
                ]);

            DB::commit();

            return view('agent::new-email-verified', [
                'message' => 'Your email has been successfully updated.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return view('agent::errors.invalid-signature', [
                'message' => 'Unable to process your email verification request right now.'
            ]);
        }
    }


}
