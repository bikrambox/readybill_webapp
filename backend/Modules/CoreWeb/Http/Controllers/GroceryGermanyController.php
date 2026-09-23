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
use Modules\GroceryGermany\Helpers\BillingHelpher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\GroceryGermany\Entities\Billing;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GroceryGermanyController extends Controller
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
        return view('coreweb::GroceryGermany.sell');
    }

    public function refund()
    {
        return view('coreweb::GroceryGermany.refund-page');
    }

    public function addItem()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->locale_route('login'); // Or handle unauthenticated users
        }

        return view('coreweb::GroceryGermany.add-item');

    }

    public function items()
    {
        return view('coreweb::GroceryGermany.items');
    }

    public function transactions()
    {
        return view('coreweb::GroceryGermany.transaction');
    }

    public function addUser()
    {
        return view('coreweb::GroceryGermany.users');
    }

    public function allUsers()
    {
        return view('coreweb::GroceryGermany.all-users');
    }

    public function settings()
    {
        return view('coreweb::GroceryGermany.preferences');
    }

    public function profile()
    {
        return view('coreweb::GroceryGermany.profile');
    }

    public function support()
    {
        return view('coreweb::GroceryGermany.support');
    }


    public function subscription()
    {
        return view('coreweb::GroceryGermany.subscription');
    }

    public function dataUploadInstruction()
    {
        return view('coreweb::GroceryGermany.dataUploadInstruction');
    }


    public function dataset()
    {
        return view('coreweb::GroceryGermany.dataset');
    }


    public function uploadData()
    {
        return view('coreweb::GroceryGermany.uploadData');
    }

    public function viewInvoice($bill_id)
    {
        $user = Auth::user();

        $bill_id = Route::current()->parameter('bill_id');

        // fetch invoice format from prefernece
        DB::setDefaultConnection($user->module_type);
        $preferences = UserHelper::getUserPreference($user);
        $bill = BillingHelpher::billingResponse($user, $bill_id);


        if ($preferences->preference_invoice_format == 0) {
            return view('coreweb::GroceryGermany.invoice.a4_bill', $bill);
        } else if ($preferences->preference_invoice_format == 1) {
            return view('coreweb::GroceryGermany.invoice.80mm_bill', $bill);
        } else if ($preferences->preference_invoice_format == 2) {
            return view('coreweb::GroceryGermany.invoice.50mm_bill', $bill);
        } else {
            abort(404, 'Bill Format Not Found');
        }
    }

    public function generateReport()
    {
        return view('coreweb::GroceryGermany.generate_report');
    }
    
}
