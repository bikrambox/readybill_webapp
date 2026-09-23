<?php

namespace Modules\CoreWeb\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Auth;

use Modules\Authentication\Entities\User;
use Modules\Core\Entities\OTP;

use Modules\GroceryGermany\Entities\Billing;
use Modules\GroceryGermany\Helpers\BillingHelpher;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

use Modules\Core\Helpers\CommonHelpher;

class IndexController extends Controller
{

    public function index()
    {
        if (Auth::user()) {
            return redirect(locale_route('sell'));
        } else {
            return view('coreweb::index');
        }

    }

    public function privacyPolicy()
    {
        return view('coreweb::privacy_policy');
    }

    public function termsAndConditions()
    {
        return view('coreweb::terms_and_conditions');
    }

    public function about()
    {
        return view('coreweb::about');
    }

    public function forgotPassword($mobile_number)
    {
        $user = User::where('mobile', $mobile_number)->first();
        $user_id = $user->user_id ?? null;

        $otpCheck = OTP::where('mobile', $mobile_number)
            ->first();

        if (isset($user_id) && isset($otpCheck)) {
            return view('coreweb::auth.forgot_password', compact('mobile_number', 'user_id'));
        } else {
            // Set a flash message for the alert
            session()->flash('error', 'Something Wrong. Please try again');
            return redirect()->locale_route('index');
        }

    }

    public function contact()
    {
        return view('coreweb::contact');
    }

    public function agents()
    {
        return view('coreweb::agents');
    }

    // public function shareInvoice($encrypted_bill_id)
    // {

    //     // dd($encrypted_bill_id);

    //     try {
    //         // Decode URL-encoded string to handle special characters
    //         $encrypted_bill_id = urldecode($encrypted_bill_id);
    //         Log::info('Attempting to decrypt bill ID', ['encrypted_bill_id' => $encrypted_bill_id]);

    //         $bill_id = Crypt::decryptString($encrypted_bill_id);
    //         Log::info('Decrypted bill ID', ['bill_id' => $bill_id]);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to decrypt bill ID', [
    //             'encrypted_bill_id' => $encrypted_bill_id,
    //             'error' => $e->getMessage()
    //         ]);
    //         abort(404, 'Invalid or corrupted bill ID');
    //     }

    //     $billing = Billing::find($bill_id);
    //     // $billing = DB::table('billing')->find($bill_id);

    //     dd($bill_id);

    //     if (!$billing) {
    //         Log::warning('Bill not found', ['bill_id' => $bill_id]);
    //         abort(404, 'Bill not found');
    //     }

    //     $user = User::find($billing->user_id);

    //     if (!$user) {
    //         Log::warning('User not found for bill', ['bill_id' => $bill_id, 'user_id' => $billing->user_id]);
    //         abort(404, 'User not found');
    //     }

    //     $bill = BillingHelpher::billingResponse($user, $bill_id);

    //     return view('coreweb::GroceryGermany.invoice.share_invoice', $bill);
    // }

    // public function encryptBillId($bill_id)
    // {
    //     try {
    //         $encrypted_id = Crypt::encryptString($bill_id);

    //         // Ensure the encrypted ID is URL-safe by encoding it
    //         // $url_safe_encrypted_id = urlencode($encrypted_id);

    //         // Convert base64 to URL-safe base62 for shorter output
    //         $url_safe_encrypted_id = substr(rtrim(strtr(base64_encode($encrypted_id), '+/', '-_'), '='), 0, 12);
    //         // 

    //         return response()->json(['encrypted_id' => $url_safe_encrypted_id]);

    //     } catch (\Exception $e) {
    //         Log::error('Failed to encrypt bill ID', [
    //             'bill_id' => $bill_id,
    //             'error' => $e->getMessage()
    //         ]);
    //         return response()->json(['error' => 'Failed to encrypt bill ID'], 500);
    //     }
    // }


    // public function encryptBillId($bill_id)
    // {
    //     try {
    //         // Secret key from .env
    //         $secretKey = config('app.key');

    //         // Generate HMAC hash of the bill ID
    //         $hash = hash_hmac('sha256', $bill_id, $secretKey, true);

    //         // Encode in URL-safe base64 and make it 12 chars
    //         $shortCode = substr(strtr(base64_encode($hash), '+/', '-_'), 0, 12);

    //         // Combine shortCode and bill_id for decoding
    //         // Example: abCdEfGh12|123
    //         $final = $shortCode . '|' . $bill_id;

    //         // Encode final string again to keep URL clean
    //         $urlSafe = strtr(base64_encode($final), '+/', '-_');
    //         $urlSafe = rtrim($urlSafe, '=');

    //         $url = url('/invoice/' . $urlSafe);

    //         return response()->json([
    //             'encrypted_id' => $url,
    //             'code' => $urlSafe,
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Encryption failed', ['error' => $e->getMessage()]);
    //         return response()->json(['error' => 'Encryption failed'], 500);
    //     }
    // }


    // public function encryptBillId($bill_id)
    // {
    //     try {
    //         // Additional variable (e.g., prefix, type, or user identifier)
    //         $prefix = 'B'; // You can make this dynamic if needed

    //         // Secret key from .env
    //         $secretKey = config('app.key');

    //         // Generate HMAC hash of the bill ID + prefix to make it more unique
    //         $hash = hash_hmac('sha256', $prefix . '|' . $bill_id, $secretKey, true);

    //         // Encode in URL-safe base64 and shorten to 12 chars
    //         $shortCode = substr(strtr(base64_encode($hash), '+/', '-_'), 0, 12);

    //         // Combine prefix, shortCode, and bill_id for decoding later
    //         // Example: B|abCdEfGh12|123
    //         $final = $prefix . '|' . $shortCode . '|' . $bill_id;

    //         // Encode final string again to keep URL clean
    //         $urlSafe = strtr(base64_encode($final), '+/', '-_');
    //         $urlSafe = rtrim($urlSafe, '=');

    //         $url = url('/invoice/' . $urlSafe);

    //         return response()->json([
    //             'encrypted_id' => $url,
    //             'code' => $urlSafe,
    //             'prefix' => $prefix,
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Encryption failed', ['error' => $e->getMessage()]);
    //         return response()->json(['error' => 'Encryption failed'], 500);
    //     }
    // }



    public function shareInvoice(Request $request)
    {

        try {

            $short_id = $request->getQueryString();

            // Decode URL-safe base64
            $decoded = base64_decode(strtr($short_id, '-_', '+/'));
            if (!$decoded || substr_count($decoded, '|') < 2) {
                abort(404, 'Invalid ID');
            }

            // Extract prefix, shortCode, and bill_id
            [$prefix, $shortCode, $bill_id] = explode('|', $decoded, 3);

            // Optional: verify the hash to ensure it's not tampered
            $secretKey = config('app.key');
            $expectedHash = substr(
                strtr(
                    base64_encode(
                        hash_hmac('sha256', $prefix . '|' . $bill_id, $secretKey, true)
                    ),
                    '+/',
                    '-_'
                ),
                0,
                12
            );

            $user_id = $prefix;

            $user = User::find($user_id);

            $module = CommonHelpher::getModuleName($user->module_type);

            // Construct the namespace dynamically based on module name
            $billingEntity = "Modules\\{$module}\\Entities\\Billing";
            $billingHelper = "Modules\\{$module}\\Helpers\\BillingHelpher";


            if (!hash_equals($expectedHash, $shortCode)) {
                abort(403, 'Invalid or tampered URL');
            }

            // Fetch the bill
            $billing = $billingEntity::find($bill_id);
            if (!$billing) {
                abort(404, 'Bill not found');
            }

            // Get related user to set proper DB connection
            $user = User::find($billing->user_id);
            if (!$user) {
                abort(404, 'User not found');
            }

            // Switch DB connection based on user's module
            DB::setDefaultConnection($user->module_type);

            // Build billing response
            $bill = $billingHelper::billingResponse($user, $bill_id);

            return view('coreweb::GroceryGermany.invoice.share_invoice', $bill);

        } catch (\Exception $e) {
            Log::error('Failed to decode invoice URL', [
                'short_id' => $short_id,
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Invalid link');
        }
    }


}
