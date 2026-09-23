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

use Modules\Authentication\Entities\Models\Otp;
use Modules\Authentication\Entities\Shop;
use Modules\Authentication\Entities\Staff;
use Modules\Authentication\Entities\User;

use Modules\Core\Helpers\ResponseHelper;
use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\RegisterHelper;


use Modules\Core\Models\Subscription;

class RegisterController extends Controller
{
    // public $registrationData = [];
    // ------------------------------------------------------------- SEND OTP ------------------------------------------------------------- 
    public function sendOTP(Request $request)
    {

        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->sendOTP();

        // dd($response);

        $status = $response['status'] === 200 ? 1 : 0;
        $message = ($status == 0 ) ? $response['message'] : __('validation.OTP Successfully Send');

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['data' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // ------------------------------------------------------------- SEND OTP -------------------------------------------------------------


    // ------------------------------------------------------------- VERIFY OTP -------------------------------------------------------------
    public function verifyOTP(Request $request)
    {

        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->verifyOTP();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = $status ? __('validation.OTP Verified Successfully') : $response['message'];

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['user' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // ------------------------------------------------------------- VERIFY OTP -------------------------------------------------------------


    // ------------------------------------------------------------- CREATE USER -------------------------------------------------------------
    public function createUser(Request $request)
    {
        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->createUser();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = $status ? __('validation.User Registered Successfully') : $response['message'];

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['user' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // ------------------------------------------------------------- CREATE USER -------------------------------------------------------------


    // ------------------------------------------------------------- CREATE SHOP DETAILS -------------------------------------------------------------
    public function createShop(Request $request)
    {
        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->createShop();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = $status ? __('validation.Shop Registered Successfully') : $response['message'];

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['user' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];


        if ($data && ($status == 1)) {
            session(['access_token' => $data['user']['token']]);
            session(['api_key' => Crypt::encryptString($data['user']['api_key'])]);
            // return redirect()->route('sell');
        }
        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // ------------------------------------------------------------- CREATE SHOP DETAILS -------------------------------------------------------------

}
