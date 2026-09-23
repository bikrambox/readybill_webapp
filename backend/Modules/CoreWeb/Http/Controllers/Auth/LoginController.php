<?php

namespace Modules\CoreWeb\Http\Controllers\Auth;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Auth;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

use Modules\Authentication\Helpers\LoginHelper;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {
        // return view('coreweb::login');
        if (Auth::user()) {
            return redirect(locale_route('sell'));
        } else {
            return view('coreweb::login');
        }
    }


    // ------------------------------------------------------------- LOGIN FUNCTIONALITY -------------------------------------------------------------
    public function login(Request $request)
    {
        $shopRegistration = new LoginHelper($request);

        $response = $shopRegistration->login();

    
        $status = $response['code'] === 200 ? 1 : 0;
        $message = $status ? __('validation.Shop Login Successfully') : $response['message'];


        // dd($status);

        // Prepare response data based on the response status
        $data = ($response['code'] == 200)
            ? ['user' => $response['user'] ?? []]
            : ['errors' => $response['errors'] ?? []];



        if ($data && ($status == 1)) {
            session(['access_token' => $data['user']['token']]);
            // session(['api_key' => Crypt::encryptString($data['user']['api_key'])]);

            // dd($data['user']['user']['lang']);

            session(['api_key' => $data['user']['api_key']]);
            session(['lang' => $data['user']['user']['lang']]);
            session(['country_code' => $data['user']['user']['country_code']]);


            
            if (isset($data['user']['country_details'])) {
                $data['user']['country_details'] = json_decode($data['user']['country_details']);
            }

            // dd($data['user']);
            
            if (isset($data['user']['isSubscriptionExpired']) && $data['user']['isSubscriptionExpired'] == 1) {
                
                if ($data['user']['user']['isAdmin'] == 1) {
                    return redirect(locale_route('subscription'));
                    // return redirect(route('subscription'));
                } else if ($data['user']['user']['isAdmin'] == 0) {
                    return redirect(locale_route('profile'));
                    // return redirect(route('profile'));
                }

            }

            // return redirect()->route('sell');
            return redirect(locale_route('sell'));
        }

        Auth::logout();
        // return redirect()->route('login');
        return redirect(locale_route('login'));
    }
    // ------------------------------------------------------------- LOGIN FUNCTIONALITY -------------------------------------------------------------


    public function logout(Request $request)
    {

        if (Auth::guard('api')->check() || Auth::user()) {

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
                // Clear the session
                $request->session()->invalidate();

                // Delete the access token from the session
                $request->session()->forget('access_token');
                $request->session()->forget('api_key');

                // Remove the cache associated with the user's mobile number
                $cacheKey = 'user_' . $user->mobile;
                Cache::forget($cacheKey);

            }

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

}
