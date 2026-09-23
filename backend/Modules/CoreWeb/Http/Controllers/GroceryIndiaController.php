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

use Modules\GroceryIndia\Helpers\BillingHelpher;
use Modules\GroceryGermany\Entities\Billing;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;

class GroceryIndiaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        return redirect(locale_route('sell'));
    }
    
    public function sell()
    {
        return view('coreweb::GroceryIndia.sell');
    }

    public function refund()
    {
        return view('coreweb::GroceryIndia.refund-page');
    }

    public function addItem()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->locale_route('login'); // Or handle unauthenticated users
        }

        return view('coreweb::GroceryIndia.add-item');

    }

    public function items()
    {
        return view('coreweb::GroceryIndia.items');
    }

    public function transactions()
    {
        return view('coreweb::GroceryIndia.transaction');
    }

    public function addUser()
    {
        return view('coreweb::GroceryIndia.users');
    }

    public function allUsers()
    {
        return view('coreweb::GroceryIndia.all-users');
    }

    public function settings()
    {
        return view('coreweb::GroceryIndia.preferences');
    }

    public function profile()
    {
        return view('coreweb::GroceryIndia.profile');
    }

    public function support()
    {
        return view('coreweb::GroceryIndia.support');
    }


    public function subscription()
    {
        return view('coreweb::GroceryIndia.subscription');
    }

    public function dataUploadInstruction()
    {
        return view('coreweb::GroceryIndia.dataUploadInstruction');
    }


    public function dataset()
    {
        return view('coreweb::GroceryIndia.dataset');
    }


    public function uploadData()
    {
        return view('coreweb::GroceryIndia.uploadData');
    }

    public function viewInvoice($bill_id)
    {

        $user = Auth::user();

        // fetch invoice format from prefernece
        DB::setDefaultConnection($user->shop_type);
        $preferences = UserHelper::getUserPreference($user);
        $bill = BillingHelpher::billingResponse($user, $bill_id);

        // dd($bill_id);

        if ($preferences->preference_invoice_format == 0) {
            return view('coreweb::GroceryIndia.invoice.a4_bill', $bill);
        } else if ($preferences->preference_invoice_format == 1) {
            return view('coreweb::GroceryIndia.invoice.80mm_bill', $bill);
        } else if ($preferences->preference_invoice_format == 2) {
            return view('coreweb::GroceryIndia.invoice.80mm_bill', $bill);
        } else {
            abort(404, 'Bill Format Not Found');
        }

    }

    public function generateReport()
    {
        return view('coreweb::GroceryIndia.generate_report');
    }
    
}
