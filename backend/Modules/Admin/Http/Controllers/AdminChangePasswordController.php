<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use Modules\Admin\Emails\OTPVerificationMail;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use Validator;

class AdminChangePasswordController extends Controller
{
    public function changePassword()
    {
        return view('admin::change_password');
    }

    public function sendOTP(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }


        $otp = Str::random(6); // Generate a 6-digit OTP or use any other method
        // Store OTP in session or database for verification
        session(['otp' => $otp]);

        // Send OTP to email
        try {
            // Mail::to($request->email)->send(new OTPVerificationMail($otp));
            return response()->json(['success' => true, 'message' => 'OTP sent to your email']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP']);
        }
    }


    public function updatePassword(Request $request)
    {
        
        $validate = Validator::make($request->all(), [
                'password' => 'required|string|min:8|confirmed',
                'otp' => 'required|in:' . session('otp'),
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $user = Auth::user();

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear OTP session
        session()->forget('otp');

        return response()->json(['success' => true, 'message' => 'Password updated successfully']);
    }

}
