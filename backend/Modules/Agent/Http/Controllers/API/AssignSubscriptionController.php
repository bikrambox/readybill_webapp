<?php

namespace Modules\Agent\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Validator;
use Auth;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Core\Entities\Subscription;
use Modules\Agent\Entities\SubscriptionCommission;
use Modules\Authentication\Entities\User;
use Modules\Core\Helpers\CommonHelpher;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AssignSubscriptionController extends Controller
{

    public function searchShop(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        $request->validate([
            'shop_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'entity_id' => ['nullable', 'string', 'min:1', 'max:255'],
        ]);

        if (!$request->filled('shop_name') && !$request->filled('entity_id')) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.search_term_required'),
            ], 422);
        }

        $shopName = $request->filled('shop_name') ? trim($request->input('shop_name')) : null;
        $rawEntityId = $request->filled('entity_id') ? trim($request->input('entity_id')) : null;

        // ✅ Only treat as entity_id if it matches: 8 digit date prefix + numeric shop_id
        // Valid examples: "1603202669", "3003202670"
        // Invalid: "Supermarket", "ENT-001", "abc"
        $entityId = ($rawEntityId && preg_match('/^\d{8}\d+$/', $rawEntityId))
            ? $rawEntityId
            : null;

        $results = [];

        foreach (config('database.connections') as $connectionName => $config) {

            if (($config['driver'] ?? null) !== 'mysql') {
                continue;
            }

            if (empty($config['host']) || empty($config['database'])) {
                continue;
            }

            try {
                if (!Schema::connection($connectionName)->hasTable('shops')) {
                    continue;
                }

                $query = DB::connection($connectionName)
                    ->table('shops')
                    ->select([
                        'shop_id',
                        'user_id',
                        'name',
                        'email',
                        'business_name',
                        'address',
                        'gstin',
                        'logo',
                        'created_at',
                    ]);

                $query->where(function ($q) use ($shopName, $entityId) {

                    // ✅ Name search — match name or business_name
                    if ($shopName !== null) {
                        $q->orWhere('name', 'LIKE', "%{$shopName}%")
                            ->orWhere('business_name', 'LIKE', "%{$shopName}%");
                    }

                    // ✅ Entity ID search — only runs if valid entity_id format
                    if ($entityId !== null) {
                        $shopIdPart = substr($entityId, 8); // strip 8-char date prefix
                        $q->orWhere('shop_id', $shopIdPart);
                    }
                });

                $shops = $query
                    ->orderByRaw(
                        $shopName
                        ? "CASE WHEN name LIKE ? THEN 0 ELSE 1 END"
                        : "shop_id ASC",
                        $shopName ? ["{$shopName}%"] : []
                    )
                    ->orderBy('name')
                    ->limit(50)
                    ->get();

                foreach ($shops as $shop) {

                    $computedEntityId = (new \DateTime($shop->created_at))
                        ->format('dmY') . $shop->shop_id;

                    // ✅ Only apply post-filter if a valid entity_id was provided
                    if ($entityId !== null && $computedEntityId !== $entityId) {
                        continue;
                    }


                    $user = DB::table('users')->where('user_id', $shop->user_id)->first();

                    $results[] = [
                        'shop_id' => $shop->shop_id,
                        'user_id' => $shop->user_id,
                        'name' => $shop->name,
                        'email' => $shop->email ?? null,
                        'mobile' => $user->mobile ?? null,
                        'business_name' => $shop->business_name ?? null,
                        'address' => $shop->address ?? null,
                        'gstin' => $shop->gstin ?? null,
                        'logo' => $shop->logo ?? null,
                        'created_at' => $shop->created_at,
                        'entity_id' => $computedEntityId,
                        '_connection' => $connectionName,
                    ];
                }

            } catch (\Throwable $e) {
                \Log::warning('Shop search DB query failed', [
                    'connection' => $connectionName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (empty($results)) {
            return response()->json([
                'status' => 'success',
                'message' => __('validation.no_results_found'),
                'data' => [],
            ], 200);
        }

        $unique = collect($results)
            ->unique(fn($shop) => $shop['shop_id'] . '_' . $shop['_connection'])
            ->values()
            ->all();

        return response()->json([
            'status' => 'success',
            'message' => __('validation.shops_found'),
            'data' => $unique,
        ], 200);
    }

    public function assignSubscription(Request $request)
    {
        // ✅ Unauthorized check
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        // ✅ Validation
        $validate = Validator::make($request->all(), [
            'shop_module' => 'required|string',
            'shop_id' => 'required|numeric',
            'subscription_id' => 'required|numeric|exists:subscriptions,subscription_id',
            'payment_mode' => 'required|in:online,cash',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Validation Error'),
                'data' => $validate->errors(),
            ], 403);
        }

        // ✅ Get Shop
        $shop = DB::connection($request->shop_module)
            ->table('shops')
            ->where('shop_id', $request->shop_id)
            ->first();

        if (!$shop) {
            return response()->json([
                'status' => false,
                'message' => 'Shop not found.'
            ], 404);
        }

        // ✅ Get Subscription (before transaction)
        $subscription = Subscription::find($request->subscription_id);

        if (!$subscription) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Subscription not found.'
            ], 404);
        }

        // ✅ Agent can only assign subscription to a shop ONE time
        $alreadyAssigned = SubscriptionCommission::where('user_id', Auth::guard('api')->id())
            ->where('shop_id', $shop->shop_id)
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You have already assigned a subscription to this shop .'
            ], 409);
        }

        // ✅ Resolve dynamic ShopSubscription model class from module
        $module = CommonHelpher::getModuleName($request->shop_module);
        $shopSubscriptionClass = "Modules\\{$module}\\Entities\\ShopSubscriptions";

        // ✅ Begin transactions on BOTH connections
        DB::beginTransaction();
        DB::connection($request->shop_module)->beginTransaction();

        try {

            // ✅ Date Calculation (immutable Carbon — no mutation side effects)
            $startDate = Carbon::now()->startOfDay();
            $endDate = $startDate->copy()->addMonths($subscription->months)->addDay();

            // ✅ Insert Shop Subscription
            $shopSubscriptionData = [
                'shop_id' => $shop->shop_id,
                // 'subscription_id' => $subscription->subscription_id,
                'subscription_data' => json_encode($subscription->toArray()),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_status' => 'paid',
                'payment_mode' => $request->payment_mode,

                // Morph: who assigned
                'assigned_by_id' => Auth::guard('api')->id(),
                'assigned_by_type' => get_class(Auth::guard('api')->user()),

                'renewed_by_type' => 'agent',

                'note' => $request->notes,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $shop_subscription_id = DB::connection($request->shop_module)
                ->table('shop_subscriptions')
                ->insertGetId($shopSubscriptionData);

            // ✅ Commission Calculation — config() instead of env()
            $agent_percentage = config('commission.agent', 84);
            $company_percentage = config('commission.company', 16);

            $company_amount = round($subscription->price * ($company_percentage / 100), 2);
            $agent_amount = round($subscription->price * ($agent_percentage / 100), 2);

            $user = Auth::guard('api')->user();

            // ✅ Store Commission
            SubscriptionCommission::create([
                'user_id' => Auth::guard('api')->id(),
                'shop_id' => $shop->shop_id,
                'module_type' => $request->shop_module,

                'commission_id' => $user->user_id . $user->mobile,

                // ✅ Morph pair — uses getMorphClass() on dynamic model instance
                'shop_subs_id' => $shop_subscription_id,
                'shop_subs_type' => (new $shopSubscriptionClass)->getMorphClass(),

                'company_percentage' => $company_percentage,
                'agent_percentage' => $agent_percentage,
                'company_amount' => $company_amount,
                'agent_amount' => $agent_amount,
            ]);

            // ✅ Commit BOTH connections
            DB::commit();
            DB::connection($request->shop_module)->commit();


            $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_commissions_' . $user->user_id;
            $cached = Cache::store('memcached')->forget($cacheKey);

            return response()->json([
                'status' => 'success',
                'message' => 'Shop Subscription Successfully Upgraded',
                'shop_subscription_id' => $shop_subscription_id,
            ], 201);

        } catch (\Exception $e) {

            // ✅ Rollback BOTH connections
            DB::rollBack();
            DB::connection($request->shop_module)->rollBack();

            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function earnings(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized'),
            ], 401);
        }

        try {
            $draw = isset($request['draw']) ? intval($request['draw']) : 0;
            $start = isset($request['start']) ? intval($request['start']) : 0;
            $length = isset($request['length']) ? intval($request['length']) : 10;
            $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
            $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
            $searchValue = isset($request['search']['value']) ? trim($request['search']['value']) : '';
            $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'shop_name';
            $shop_subs_type = isset($request['shop_subs_type']) ? $request['shop_subs_type'] : null;

            $user = Auth::guard('api')->user();

            // ── Resolve cross-DB names upfront ──────────────────────────────────
            $dbIndia = config('database.connections.grocery_india.database');
            $dbGerman = config('database.connections.grocery_germany.database');

            if (empty($dbIndia) || empty($dbGerman)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Database configuration missing for grocery_india or grocery_germany.',
                ], 500);
            }

            // ── UNION: shops from both DBs ───────────────────────────────────────
            $shopsUnion = DB::raw("(
            SELECT shop_id, name AS shop_name, 'grocery_india' AS source
            FROM `{$dbIndia}`.shops

            UNION ALL

            SELECT shop_id, name AS shop_name, 'grocery_germany' AS source
            FROM `{$dbGerman}`.shops
        ) AS all_shops");

            // ── UNION: shop_subscriptions joined with subscriptions (plan_name) ─
            // subscriptions table lives in central DB
            //     $subsUnion = DB::raw("(
            //     SELECT
            //         ss.id          AS subs_id,
            //         ss.shop_id,
            //         s.plan_name,
            //         ss.payment_status,
            //         'grocery_india' AS source
            //     FROM `{$dbIndia}`.shop_subscriptions ss
            //     INNER JOIN `" . config('database.connections.central.database') . "`.subscriptions s
            //         ON s.subscription_id = ss.subscription_id

            //     UNION ALL

            //     SELECT
            //         ss.id          AS subs_id,
            //         ss.shop_id,
            //         s.plan_name,
            //         ss.payment_status,
            //         'grocery_germany' AS source
            //     FROM `{$dbGerman}`.shop_subscriptions ss
            //     INNER JOIN `" . config('database.connections.central.database') . "`.subscriptions s
            //         ON s.subscription_id = ss.subscription_id
            // ) AS all_subs");


            $subsUnion = DB::raw("(
                SELECT
                    ss.id AS subs_id,
                    ss.shop_id,
                    JSON_UNQUOTE(JSON_EXTRACT(ss.subscription_data, '$.plan_name')) AS plan_name,
                    ss.payment_status,
                    'grocery_india' AS source
                FROM `{$dbIndia}`.shop_subscriptions ss

                UNION ALL

                SELECT
                    ss.id AS subs_id,
                    ss.shop_id,
                    JSON_UNQUOTE(JSON_EXTRACT(ss.subscription_data, '$.plan_name')) AS plan_name,
                    ss.payment_status,
                    'grocery_germany' AS source
                FROM `{$dbGerman}`.shop_subscriptions ss
            ) AS all_subs");

            // ── Base query ───────────────────────────────────────────────────────
            $query = DB::connection('central')
                ->table('subscription_commissions as sc')
                ->leftJoin($shopsUnion, function ($join) {
                    $join->on('all_shops.shop_id', '=', 'sc.shop_id')
                        ->on('all_shops.source', '=', 'sc.shop_subs_type');
                })
                ->leftJoin($subsUnion, function ($join) {
                    $join->on('all_subs.subs_id', '=', 'sc.shop_subs_id')
                        ->on('all_subs.source', '=', 'sc.shop_subs_type');
                })
                ->select([
                    'sc.id',
                    'sc.commission_id',
                    'sc.transaction_id',
                    'all_shops.shop_name',
                    'all_subs.plan_name',
                    'sc.agent_amount',
                    'sc.shop_subs_type',
                    'sc.payment_status',
                    'sc.payment_mode',
                    'sc.created_at'
                ]);

            // ── Optional platform filter ─────────────────────────────────────────
            if ($shop_subs_type && in_array($shop_subs_type, ['grocery_india', 'grocery_germany'])) {
                $query->where('sc.shop_subs_type', $shop_subs_type);
            }

            // ── Search ───────────────────────────────────────────────────────────
            if ($searchValue !== '') {
                switch ($filter_option) {
                    case 'shop_name':
                        $query->where('all_shops.shop_name', 'LIKE', "%{$searchValue}%");
                        break;

                    case 'plan_name':
                        $query->where('all_subs.plan_name', 'LIKE', "%{$searchValue}%");
                        break;

                    case 'agent_amount':
                        if (is_numeric($searchValue)) {
                            $query->where('sc.agent_amount', '=', $searchValue);
                        } else {
                            $query->where('sc.agent_amount', '=', -1);
                        }
                        break;
                }
            }

            // ── Filtered count ───────────────────────────────────────────────────
            $recordsFiltered = (clone $query)->count();

            // ── Column / sort mapping ────────────────────────────────────────────
            $columnMap = [
                0 => 'sc.id',
                1 => 'sc.commission_id',
                2 => 'sc.transaction_id',
                3 => 'all_shops.shop_name',
                4 => 'all_subs.plan_name',
                5 => 'sc.agent_amount',
                6 => 'sc.payment_status',
                7 => 'sc.payment_mode',
            ];

            if (isset($request['columns'][$sortColumnIndex]['data'])) {
                $dataToColumn = [
                    'id' => 'sc.id',
                    'commission_id' => 'sc.commission_id',
                    'transaction_id' => 'sc.transaction_id',
                    'shop_name' => 'all_shops.shop_name',
                    'plan_name' => 'all_subs.plan_name',
                    'agent_amount' => 'sc.agent_amount',
                    'payment_status' => 'sc.payment_status',
                    'payment_mode' => 'sc.payment_mode',
                    'created_at' => 'sc.created_at',
                ];
                $colData = $request['columns'][$sortColumnIndex]['data'];
                $sortColumn = $dataToColumn[$colData] ?? 'sc.id';
            } else {
                $sortColumn = $columnMap[$sortColumnIndex] ?? 'sc.id';
            }

            $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

            // agent_amount is numeric → cast for correct ordering
            if ($sortColumn === 'sc.agent_amount') {
                $query->orderByRaw("
                CASE WHEN sc.agent_amount IS NULL THEN 1 ELSE 0 END,
                CAST(COALESCE(sc.agent_amount, 0) AS DECIMAL(15,4)) {$sortDirection}
            ");
            } else {
                $query->orderByRaw("
                CASE WHEN {$sortColumn} IS NULL OR {$sortColumn} = '' THEN 1 ELSE 0 END,
                {$sortColumn} {$sortDirection}
            ");
            }

            // ── Paginate ─────────────────────────────────────────────────────────
            $query->offset($start)->limit($length);
            $items = $query->get();

            // ── Total records ────────────────────────────────────────────────────
            $totalRecords = DB::connection('central')
                ->table('subscription_commissions')
                ->count();

            // ── Cache ────────────────────────────────────────────────────────────
            $isDefault = $searchValue === '' && !$shop_subs_type && $sortColumnIndex === 0;
            $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_commissions_' . $user->user_id;

            if ($isDefault) {
                $cached = Cache::store('memcached')->get($cacheKey);
                if ($cached)
                    return $cached;
            }

            // ── Total agent earnings (respects filters) ──────────────────────────────
            $totalEarnings = (clone $query)->sum('sc.agent_amount');

            $response = response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'totalEarnings' => round((float) $totalEarnings, 2),
                'data' => $items,
                'filter_option' => $filter_option,
                'shop_subs_type' => $shop_subs_type,
            ], 200);

            if ($isDefault) {
                Cache::store('memcached')->put($cacheKey, $response, now()->addMinutes(10));
            }

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPlans()
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        try {
            $plans = DB::connection('central')
                ->table('subscriptions')
                ->where('subscription_id', '!=', 1)
                ->where('active', 1)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $plans
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAgentDetails($subscription_commission_id)
    {

        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }
        try {
            $subscription_commission = SubscriptionCommission::find($subscription_commission_id);

            $agent = User::find($subscription_commission->user_id);

            $agentDetails = $agent->agentDetail;

            $shop = DB::connection($subscription_commission->module_type)->table('shops')->where('shop_id', $subscription_commission->shop_id)->first();

            $shop_entity_id = (new \DateTime($shop->created_at))->format('dmY') . $shop->shop_id;

            // Base URL for images
            $baseUrl = rtrim(env('MEDIA_URL', ''), '/');

            $data = [
                'agent_name' => $agentDetails->name,
                'agent_photo' => CommonHelpher::getImageUrl($agentDetails->photo ?? null, $baseUrl . '/agent/photo/'),
                'shop_name' => $shop->name,
                'shop_entity_id' => $shop_entity_id,
                'date_of_subscription' => $subscription_commission->created_at,
                'amount' => $subscription_commission->agent_amount,
                'agent_payment_date' => $subscription_commission->agent_payment_date ?? '–',
                'payment_mode' => $subscription_commission->payment_mode ?? '–',
                'transaction_id' => $subscription_commission->transaction_id,
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }


    }

}
