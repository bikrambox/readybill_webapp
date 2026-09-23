<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;
use Modules\Core\Entities\Subscription;
use Illuminate\Support\Facades\Cache;

class AdminSubscriptionPlanController extends Controller
{
    public function subscriptionPlans()
    {
        return view('admin::subscription_plans');
    }


    // ════════════════════════════════════════════════════════
    // DATATABLE DATA
    // ════════════════════════════════════════════════════════
    public function subscriptionPlanData(Request $request)
    {
        try {
            $draw = intval($request->input('draw', 0));
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $sortColumnIndex = intval($request->input('order.0.column', 0));
            $sortDirection = $request->input('order.0.dir', 'asc');
            $searchValue = $request->input('search.value', '');

            $columns = ['months', 'plan_name', 'price', 'heading', 'subheading', 'description', 'active', 'created_at'];
            $sortColumn = $columns[$sortColumnIndex] ?? 'plan_name';

            $query = DB::connection('central')->table('subscriptions')
                ->where('subscription_id', '!=', 1)
                ->where('plan_name', '!=', 'Free Plan');

            $totalRecords = (clone $query)->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('months', 'like', "%{$searchValue}%")
                        ->orWhere('plan_name', 'like', "%{$searchValue}%")
                        ->orWhere('price', 'like', "%{$searchValue}%")
                        ->orWhere('created_at', 'like', "%{$searchValue}%");
                });
            }

            $recordsFiltered = $query->count();

            $items = $query
                ->orderBy($sortColumn, $sortDirection)
                ->offset($start)
                ->limit($length)
                ->get();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $items,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // ════════════════════════════════════════════════════════
    // GET BY ID
    // ════════════════════════════════════════════════════════
    public function getSubscriptionPlanById($subscription_id)
    {
        $subscription_plan = DB::connection('central')->table('subscriptions')
            ->where('subscription_id', $subscription_id)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $subscription_plan
        ], 200);
    }


    // ════════════════════════════════════════════════════════
    // CREATE
    // ════════════════════════════════════════════════════════
    public function storeSubscriptionPlan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            // Mandatory
            'plan_name' => 'required|string|max:255',
            'months' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('central.subscriptions', 'months'),
            ],
            'price' => 'required|numeric|min:0',
            'active' => 'required|in:1,0',
            // Optional
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_best_value' => 'nullable|in:1,0',
        ], [
            'plan_name.required' => 'Plan name is required.',
            'plan_name.string' => 'Plan name must be a valid string.',
            'plan_name.max' => 'Plan name must not exceed 255 characters.',
            'months.required' => 'Duration is required.',
            'months.integer' => 'Duration must be a whole number.',
            'months.min' => 'Duration must be at least 1 month.',
            'months.max' => 'Duration must not exceed 12 months.',
            'months.unique' => 'A subscription plan with this duration already exists.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least 0.',
            'active.required' => 'Status is required.',
            'active.in' => 'Status must be either Active or Inactive.',
            'heading.max' => 'Heading must not exceed 255 characters.',
            'subheading.max' => 'Subheading must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'is_best_value.in' => 'Best value must be either yes or no.',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $subscription = new Subscription();
        $subscription->setConnection('central');
        $subscription->plan_name = $request->plan_name;
        $subscription->months = $request->months;
        $subscription->price = $request->price;
        $subscription->heading = $request->heading;
        $subscription->subheading = $request->subheading;
        $subscription->description = $request->description;
        $subscription->active = $request->active;
        $subscription->is_best_value = $request->is_best_value ?? 0;
        $subscription->shop_type = 'grocery_india';

        // If this plan is marked as best value, unset all others first
        if ($request->is_best_value == 1) {
            DB::connection('central')
                ->table('subscriptions')
                ->where('is_best_value', 1)
                ->update(['is_best_value' => 0]);
        }

        $subscription->save();

        $cacheKey = env('CACHE_KEY_PREFIX') . 'subscription_plans';
        Cache::store('memcached')->forget($cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription plan created successfully.',
            'data' => $subscription
        ], 201);
    }


    // ════════════════════════════════════════════════════════
    // UPDATE
    // ════════════════════════════════════════════════════════
    public function updateSubscriptionPlan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            // Mandatory
            'subscription_id' => 'required|exists:central.subscriptions,subscription_id',
            'plan_name' => 'required|string|max:255',
            'months' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('central.subscriptions', 'months')
                    ->ignore($request->subscription_id, 'subscription_id'),
            ],
            'price' => 'required|numeric|min:0',
            'active' => 'required|in:1,0',
            // Optional
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_best_value' => 'nullable|in:1,0',
        ], [
            'subscription_id.required' => 'Subscription ID is required.',
            'subscription_id.exists' => 'Selected subscription plan does not exist.',
            'plan_name.required' => 'Plan name is required.',
            'plan_name.string' => 'Plan name must be a valid string.',
            'plan_name.max' => 'Plan name must not exceed 255 characters.',
            'months.required' => 'Duration is required.',
            'months.integer' => 'Duration must be a whole number.',
            'months.min' => 'Duration must be at least 1 month.',
            'months.max' => 'Duration must not exceed 12 months.',
            'months.unique' => 'A subscription plan with this duration already exists.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least 0.',
            'active.required' => 'Status is required.',
            'active.in' => 'Status must be either Active or Inactive.',
            'heading.max' => 'Heading must not exceed 255 characters.',
            'subheading.max' => 'Subheading must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'is_best_value.in' => 'Best value must be either yes or no.',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $subscription = Subscription::find($request->subscription_id);
        $subscription->plan_name = $request->plan_name;
        $subscription->months = $request->months;
        $subscription->price = $request->price;
        $subscription->heading = $request->heading;
        $subscription->subheading = $request->subheading;
        $subscription->description = $request->description;
        $subscription->active = $request->active;
        $subscription->is_best_value = $request->is_best_value ?? 0;

        // If this plan is marked as best value, unset all others first
        if ($request->is_best_value == 1) {
            DB::connection('central')
                ->table('subscriptions')
                ->where('subscription_id', '!=', $request->subscription_id)
                ->where('is_best_value', 1)
                ->update(['is_best_value' => 0]);
        }

        $subscription->save();

        $cacheKey = env('CACHE_KEY_PREFIX') . 'subscription_plans';
        Cache::store('memcached')->forget($cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription plan updated successfully.',
            'data' => $subscription
        ], 200);
    }


    // ════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════
    public function deleteSubscriptionPlan($subscription_id)
    {
        try {
            $subscription = Subscription::find($subscription_id);

            if (!$subscription) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Subscription plan not found.',
                ], 404);
            }

            // $tenantConnections = ['grocery_india', 'grocery_germany'];

            // foreach ($tenantConnections as $connection) {
            //     $hasActiveSubscribers = DB::connection($connection)
            //         ->table('shop_subscriptions')
            //         ->where('subscription_id', $subscription_id)
            //         ->where('payment_status', 'paid')
            //         ->exists();

            //     if ($hasActiveSubscribers) {
            //         return response()->json([
            //             'status' => 'failed',
            //             'message' => 'Cannot delete this plan as it has active subscribers.',
            //         ], 422);
            //     }
            // }

            $subscription->delete();

            $cacheKey = env('CACHE_KEY_PREFIX') . 'subscription_plans';
            Cache::store('memcached')->forget($cacheKey);


            return response()->json([
                'status' => 'success',
                'message' => 'Subscription plan deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // ════════════════════════════════════════════════════════
    // ALL ACTIVE PLANS (shop-side)
    // ════════════════════════════════════════════════════════
    public function allSubscriptionPlans()
    {
        $items = DB::connection('central')->table('subscriptions')
            ->where('subscription_id', '!=', 1)
            ->where('active', 1)
            ->orderBy('months', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items
        ], 200);
    }
}