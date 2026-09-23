<?php

namespace Modules\Agent\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Agent\Emails\OTPVerificationMail;
use Validator;

class ChangePasswordController extends Controller
{
    private int $otpExpiry;
    private int $cooldown;
    private int $maxAttempts;
    private int $lockoutMinutes;

    public function __construct()
    {
        $this->otpExpiry = (int) env('AGENT_OTP_EXPIRY_MINUTES', 10);
        $this->cooldown = (int) env('AGENT_OTP_COOLDOWN_SECONDS', 60);
        $this->maxAttempts = (int) env('AGENT_OTP_MAX_ATTEMPTS', 1);
        $this->lockoutMinutes = (int) env('AGENT_OTP_LOCKOUT_MINUTES', 15);
    }

    // ─── Send OTP ─────────────────────────────────────────────────────────────
    public function sendOTP(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return $this->unauthorizedResponse();
        }

        $user = Auth::guard('api')->user();

        // 1. Check lockout
        $lockoutKey = 'agent_otp_lockout_' . $user->id;
        $lockoutUntil = Cache::get($lockoutKey);  // stores Carbon timestamp
        if ($lockoutUntil) {
            $remaining = (int) ceil(now()->diffInSeconds($lockoutUntil, false) / 60);
            if ($remaining > 0) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Too many incorrect OTP attempts. Please try again after {$remaining} minute(s).",
                ], 429);
            }
            // Lockout expired — clean up
            Cache::forget($lockoutKey);
        }

        // 2. Cooldown check
        $cooldownKey = 'agent_otp_cooldown_' . $user->id;
        $cooldownUntil = Cache::get($cooldownKey); // stores Carbon timestamp
        if ($cooldownUntil && now()->lt($cooldownUntil)) {
            $wait = (int) ceil(now()->diffInSeconds($cooldownUntil, false));
            return response()->json([
                'status' => 'failed',
                'message' => "Please wait {$wait} second(s) before requesting a new OTP.",
            ], 429);
        }

        $validate = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $otp = strtoupper(Str::random(6));
        $otpKey = 'agent_otp_' . $user->id;
        $attemptsKey = 'agent_otp_attempts_' . $user->id;

        // Store OTP & reset attempts
        Cache::put($otpKey, $otp, now()->addMinutes($this->otpExpiry));
        Cache::forget($attemptsKey);

        // Store cooldown as a timestamp so remaining seconds stay accurate
        Cache::put($cooldownKey, now()->addSeconds($this->cooldown), now()->addSeconds($this->cooldown));

        try {
            Mail::to($user->email)->send(new OTPVerificationMail($otp));

            return response()->json([
                'success' => true,
                'message' => "OTP sent to your email. Valid for {$this->otpExpiry} minute(s).",
                'cooldown' => $this->cooldown,
            ]);

        } catch (\Exception $e) {
            Cache::forget($otpKey);
            Cache::forget($cooldownKey);

            Log::error('Agent OTP Mail Error | User: ' . $user->id . ' | ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // ─── Update Password ──────────────────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return $this->unauthorizedResponse();
        }

        $user = Auth::guard('api')->user();
        $otpKey = 'agent_otp_' . $user->id;
        $attemptsKey = 'agent_otp_attempts_' . $user->id;
        $lockoutKey = 'agent_otp_lockout_' . $user->id;

        // 1. Check lockout
        $lockoutUntil = Cache::get($lockoutKey);
        if ($lockoutUntil && now()->lt($lockoutUntil)) {
            $remaining = (int) ceil(now()->diffInSeconds($lockoutUntil, false) / 60);
            return response()->json([
                'status' => 'failed',
                'message' => "Too many incorrect attempts. Try again after {$remaining} minute(s).",
            ], 429);
        }
        // Clean up expired lockout
        if ($lockoutUntil)
            Cache::forget($lockoutKey);

        // 2. Check OTP exists
        $cachedOtp = Cache::get($otpKey);
        if (!$cachedOtp) {
            return response()->json([
                'status' => 'failed',
                'message' => 'OTP has expired or was never issued. Please request a new one.',
            ], 422);
        }

        $validate = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'otp' => 'required|string',
        ]);

        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        // 3. Verify OTP
        if (!hash_equals($cachedOtp, strtoupper(trim($request->otp)))) {
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;
            $remaining = $this->maxAttempts - $attempts;

            // Save updated attempt count
            Cache::put($attemptsKey, $attempts, now()->addMinutes($this->otpExpiry));

            // Trigger lockout
            if ($attempts >= $this->maxAttempts) {
                $lockoutUntil = now()->addMinutes($this->lockoutMinutes);
                Cache::put($lockoutKey, $lockoutUntil, $lockoutUntil);
                Cache::forget($otpKey);
                Cache::forget($attemptsKey);

                return response()->json([
                    'status' => 'failed',
                    'message' => "Too many incorrect attempts. Your OTP has been invalidated. Try again after {$this->lockoutMinutes} minute(s).",
                ], 429);
            }

            return response()->json([
                'status' => 'failed',
                'message' => "Incorrect OTP. You have {$remaining} attempt(s) remaining.",
            ], 422);
        }

        // 4. OTP correct — update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear all OTP cache keys
        Cache::forget($otpKey);
        Cache::forget($attemptsKey);
        Cache::forget('agent_otp_cooldown_' . $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function unauthorizedResponse()
    {
        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    private function validationErrorResponse($errors)
    {
        return response()->json([
            'status' => 'failed',
            'message' => 'Validation Error!',
            'data' => $errors,
        ], 403);
    }
}