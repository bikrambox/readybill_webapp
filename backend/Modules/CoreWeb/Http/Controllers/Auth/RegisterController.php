<?php

namespace Modules\CoreWeb\Http\Controllers\Auth;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Crypt;
use Modules\Core\Helpers\ResponseHelper;
use Modules\Authentication\Helpers\RegisterHelper;
use Auth;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function register(){


        // return view('coreweb::register');

        if (Auth::user()) {
            return redirect(locale_route('sell'));
        } else {
            return view('coreweb::register');
        }
    }

    // public $registrationData = [];
    // // ------------------------------------------------------------- SEND OTP ------------------------------------------------------------- 
    public function sendOTP(Request $request)
    {

        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->sendOTP();

        
        $status = $response['status'] === 200 ? 1 : 0;
        // $message = $status ? $response['message']:__('validation.OTP Successfully Send') ;
        $message = ($status == 0) ? $response['message'] : __('validation.OTP Successfully Send');

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['data' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];


        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }

    // // ------------------------------------------------------------- SEND OTP -------------------------------------------------------------


    // // ------------------------------------------------------------- VERIFY OTP -------------------------------------------------------------

    public function verifyOTP(Request $request)
    {

        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->verifyOTP();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = $status ? __('validation.OTP Verified Successfully') : $response['message'];

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['user'=>$response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // // ------------------------------------------------------------- VERIFY OTP -------------------------------------------------------------
    
    
    // ------------------------------------------------------------- CREATE USER -------------------------------------------------------------
    public function createUser(Request $request){
        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->createUser();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = $status ? __('validation.User Registered Successfully') : $response['message'];

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['user'=>$response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];

        return ResponseHelper::responseFn($status, $response['status'], $message, $data);
    }
    // ------------------------------------------------------------- CREATE USER -------------------------------------------------------------
    
    
    // ------------------------------------------------------------- CREATE SHOP DETAILS -------------------------------------------------------------
    public function createShop(Request $request){
        $shopRegistration = new RegisterHelper($request);

        $response = $shopRegistration->createShop();

        $status = $response['status'] === 200 ? 1 : 0;
        $message = $status ? __('validation.Shop Registered Successfully') : $response['message'];

        // Prepare response data based on the response status
        $data = ($response['status'] == 200)
            ? ['user'=>$response['user'] ?? []]
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
