<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use Modules\GroceryIndia\Entities\Customer;

class CustomerController extends Controller
{
    public function searchCustomer($mobile)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        if (empty($mobile)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Mobile number is required'
            ], 400);
        }

        $customer = DB::connection('grocery_india')
            ->table('customers')
            ->where('mobile', 'like', $mobile . '%')
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $customer,
        ], 200);
    }
}
