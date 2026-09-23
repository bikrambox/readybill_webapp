<?php

namespace Modules\CoreWeb\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Auth;

use Modules\Authentication\Entities\User;
use Modules\Core\Entities\OTP;

use Modules\Authentication\Helpers\UserHelper;
use Modules\Core\Helpers\CountryHelpher;
// use Modules\GroceryIndia\Helpers\BillingHelpher;
use Modules\Core\Helpers\CommonHelpher;

use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function printInvoice($bill_id)
    {

        $user = Auth::user();

        // fetch invoice format from prefernece
        DB::setDefaultConnection($user->module_type);

        $module = CommonHelpher::getModuleName($user->module_type);


        // Construct the namespace dynamically based on module name
        $billHelper = "Modules\\{$module}\\Helpers\\BillingHelpher";

        // Check if the class exists to avoid errors
        if (!class_exists($billHelper)) {
            return null;
        }

        $preferences = UserHelper::getUserPreference($user);
        $bill = $billHelper::billingResponse($user, $bill_id);

        // dd($module);

        if($preferences->preference_invoice_format == 0){
            return view("coreweb::{$module}.invoice.a4_bill",$bill);
        }
        else if($preferences->preference_invoice_format == 1){
            return view("coreweb::{$module}.invoice.80mm_bill",$bill);
        }
        else if($preferences->preference_invoice_format == 2){
            return view("coreweb::{$module}.invoice.80mm_bill",$bill);
        }
        else{
            abort(404, 'Bill Format Not Found');
        }

    }


    public function changePassword()
    {
        $mobile_number = Auth::user()->mobile;

        $user = User::where('mobile', $mobile_number)->first();
        $user_id = $user->user_id ?? null;

        $otpCheck = OTP::where('mobile', $mobile_number)
            ->first();

        if (isset($user_id) && isset($otpCheck)) {
            return view('coreweb::change_password', compact('mobile_number', 'user_id'));
        } else {
            // Set a flash message for the alert
            session()->flash('error', 'Something Wrong. Please try again');
            return redirect(locale_route('index'));
        }
    }

}
