<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Shop;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TableNameHelper;
use Validator;
use Illuminate\Support\Facades\Schema;

class AdminLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->only(['dashboard', 'shopsList']);
    }

    public function index()
    {
        return view('admin::login');
    }

    public function login_form()
    {
        return view('admin::login');
    }

    public function login(Request $request)
    {
        try {

            // ✅ Create validator
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required|string|min:8',
                ]
            );

            // ✅ Handle validation failure
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'code' => 422,
                    'message' => 'Invalid input',
                    'errors' => $validator->errors(),
                    'data' => null,
                ], 422);
            }

            // ✅ Prepare credentials
            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            // ✅ Attempt login
            if (Auth::guard('admin')->attempt($credentials)) {

                $admin = Auth::guard('admin')->user();

                // ✅ Update login timestamps
                $admin->update([
                    'last_logged_in' => $admin->current_logged_in ?? now(),
                    'current_logged_in' => now(),
                    'ip_address' => $request->ip(),
                ]);

                // ✅ Regenerate session (security)
                $request->session()->regenerate();

                return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Logged in successfully',
                    'data' => [
                        'admin' => $admin->only(['id', 'name', 'email']),
                    ],
                ], 200);
            }

            // ❌ Invalid credentials
            return response()->json([
                'status' => 'failed',
                'code' => 401,
                'message' => 'Invalid credentials',
                'data' => null,
            ], 401);

        } catch (\Exception $e) {

            \Log::error('Admin login error: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'code' => 500,
                'message' => 'Something went wrong',
                'data' => null,
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Logout the user
        Auth::guard('admin')->logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the session token to prevent session fixation attacks
        $request->session()->regenerateToken();

        // Return a successful response
        $data = array(
            'success' => true,
            'status' => 200,
            'message' => "Logged out successfully",
        );

        return response()->json($data);
    }
    
}
