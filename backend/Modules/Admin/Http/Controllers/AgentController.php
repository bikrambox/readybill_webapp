<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Authentication\Entities\User;
use Modules\Agent\Helpers\CommonHelpher;

use Modules\Agent\Entities\SubscriptionCommission;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{

    public function agentList()
    {
        return view('admin::agents.index');
    }

    public function agentListData(Request $request)
    {
        try {
            $draw = intval($request->input('draw', 0));
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $sortColumnIndex = intval($request->input('order.0.column', 0));
            $sortDirection = $request->input('order.0.dir', 'asc');
            $searchValue = trim($request->input('search.value', ''));
            $filter_option = $request->input('filter_option', 'name');

            // Base URL for images
            $baseUrl = rtrim(env('MEDIA_URL', ''), '/');
            $photoDir = $baseUrl . '/agent/photo/';

            // Base query
            $query = User::query()
                ->leftJoin('agent_details', 'agent_details.user_id', '=', 'users.user_id')
                ->where('users.isAgent', 1)
                ->select(
                    'users.user_id',
                    'users.email',
                    'users.mobile',
                    'users.active',
                    'users.isVerified',
                    'users.country_code',
                    'users.shop_type',
                    'users.module_type',
                    'agent_details.name',
                    'agent_details.address',
                    'agent_details.photo',
                );

            // Apply search filter
            if ($searchValue !== '') {
                if ($filter_option === 'name') {
                    $query->where('agent_details.name', 'LIKE', "%{$searchValue}%");
                } elseif ($filter_option === 'email') {
                    $query->where('users.email', 'LIKE', "%{$searchValue}%");
                } elseif ($filter_option === 'mobile') {
                    $query->where('users.mobile', 'LIKE', "%{$searchValue}%");
                } elseif ($filter_option === 'address') {
                    $query->where('agent_details.address', 'LIKE', "%{$searchValue}%");
                }
            }

            // Get filtered records count
            $recordsFiltered = (clone $query)->count();

            // Column mapping
            $sortColumnData = $request->input("columns.{$sortColumnIndex}.data");
            if ($sortColumnData) {
                $sortColumn = $sortColumnData;
            } else {
                $columns = [
                    1 => 'name',
                    2 => 'email',
                    3 => 'mobile',
                    4 => 'address',
                ];
                $sortColumn = $columns[$sortColumnIndex] ?? 'name';
            }

            // Map data key to actual DB column
            $columnMap = [
                'name' => 'agent_details.name',
                'email' => 'users.email',
                'mobile' => 'users.mobile',
                'address' => 'agent_details.address',
            ];

            $dbSortColumn = $columnMap[$sortColumn] ?? 'agent_details.name';
            $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

            // Apply sorting — NULL/empty last
            $query->orderByRaw("
            CASE
                WHEN {$dbSortColumn} IS NULL OR {$dbSortColumn} = '' THEN 1
                ELSE 0
            END,
            {$dbSortColumn} {$sortDirection}
        ");

            // Total records (unfiltered)
            $totalRecords = User::where('isAgent', 1)
                ->where('isVerified', 1)
                ->where('active', 1)
                ->count();

            // Cache check — only for default state
            $cacheKey = env('CACHE_KEY_PREFIX', '') . 'authorized_agents_all';
            $cachedData = Cache::store('memcached')->get($cacheKey);

            if ($cachedData && $searchValue === '' && $filter_option === 'name' && $sortColumnIndex === 0) {
                return response()->json($cachedData, 200);  // ← was: return $cachedData
            }

            // Paginate
            $agents = $query->offset($start)->limit($length)->get();

            // Append full photo URL using CommonHelpher
            $agents->transform(function ($agent) use ($photoDir) {
                $agent->photo = CommonHelpher::getImageUrl($agent->photo ?? null, $photoDir);
                return $agent;
            });

            $responseData = [                               // ← extract to array
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $agents,
                'searchValue' => $searchValue,
                'filter_option' => $filter_option,
            ];

            // Cache only default unfiltered/unsorted state
            if ($searchValue === '' && $filter_option === 'name' && $sortColumnIndex === 0) {
                Cache::store('memcached')->put($cacheKey, $responseData);  // ← store array, not response object
            }

            return response()->json($responseData, 200);   // ← was: return $response

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function toggleStatus(Request $request, $userId)
    {
        try {
            $agent = User::where('user_id', $userId)
                ->where('isAgent', 1)
                ->firstOrFail();

            // Flip 1 → 0, 0 → 1
            $agent->active = $agent->active ? 0 : 1;
            $agent->save();

            // Bust the default-state cache so the list reflects the change
            $cacheKey = env('CACHE_KEY_PREFIX', '') . 'authorized_agents_all';
            Cache::store('memcached')->forget($cacheKey);

            return response()->json([
                'status' => 'success',
                'message' => 'Agent status updated successfully.',
                'data' => [
                    'user_id' => $agent->user_id,
                    'active' => $agent->active,  // returns new value: 0 or 1
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Agent not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function agentView($userId)
    {
        return view('admin::agents.view', compact('userId'));
    }


    public function show($userId)
    {
        try {
            $agent = User::query()
                ->leftJoin('agent_details', 'agent_details.user_id', '=', 'users.user_id')
                ->where('users.user_id', $userId)
                ->where('users.isAgent', 1)
                ->select(
                    'users.user_id',
                    'users.email',
                    'users.mobile',
                    'users.active',
                    'users.isVerified',
                    'users.country_code',
                    'users.shop_type',
                    'users.module_type',
                    'agent_details.name',
                    'agent_details.address',
                    'agent_details.photo',
                    'agent_details.aadhar_card',
                    'agent_details.qr_code',
                )
                ->first();

            if (!$agent) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Agent not found.',
                ], 404);
            }

            $baseUrl = rtrim(env('MEDIA_URL', ''), '/');
            $photoDir = $baseUrl . '/agent/photo/';
            $aadharDir = $baseUrl . '/agent/aadhar/';
            $qrDir = $baseUrl . '/agent/qr/';

            $agent->photo = CommonHelpher::getImageUrl($agent->photo ?? null, $photoDir);
            $agent->aadhar_card = CommonHelpher::getImageUrl($agent->aadhar_card ?? null, $aadharDir);
            $agent->qr_code = CommonHelpher::getImageUrl($agent->qr_code ?? null, $qrDir);

            return response()->json([
                'status' => 'success',
                'data' => $agent,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function agentTransactions(){
        return view('admin::agents.transactions');
    }

    public function agentTransactionData(Request $request)
    {
        try {
            $draw = intval($request->input('draw', 0));
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $sortColumnIndex = intval($request->input('order.0.column', 0));
            $sortDirection = $request->input('order.0.dir', 'asc');
            $searchValue = trim($request->input('search.value', ''));
            $filter_option = $request->input('filter_option', 'agent_name');

            $baseUrl = rtrim(env('MEDIA_URL', ''), '/');

            // Base query with joins
            $query = SubscriptionCommission::query()
                ->leftJoin('users', 'users.user_id', '=', 'subscription_commissions.user_id')
                ->leftJoin('agent_details', 'agent_details.user_id', '=', 'users.user_id')
                ->select(
                    'subscription_commissions.*',
                    'agent_details.name as agent_name',
                    'agent_details.photo as agent_photo',
                    'users.created_at as user_created_at',
                    'users.user_id as agent_user_id',
                );

            // Apply search filter
            if ($searchValue !== '') {
                if ($filter_option === 'agent_name') {
                    $query->where('agent_details.name', 'LIKE', "%{$searchValue}%");

                } elseif ($filter_option === 'agent_id') {
                    // agent_entity_id = DATE_FORMAT(users.created_at, '%d%m%Y') + user_id
                    // e.g. created_at = 2025-01-15, user_id = 42 → "150120250042" — wait, no concat
                    // actual format: dmY . user_id  e.g. "15012025" . "42" = "1501202542"
                    $query->whereRaw(
                        "CONCAT(DATE_FORMAT(users.created_at, '%d%m%Y'), users.user_id) LIKE ?",
                        ["%{$searchValue}%"]
                    );
                } elseif ($filter_option === 'date') {
                    try {
                        $parsed = \DateTime::createFromFormat('d/m/Y', $searchValue);
                        if ($parsed) {
                            $query->whereDate('subscription_commissions.created_at', $parsed->format('Y-m-d'));
                        }
                    } catch (\Throwable $e) {
                        $query->whereRaw('1 = 0');
                    }

                } elseif ($filter_option === 'status') {
                    if (strtolower($searchValue) === 'paid') {
                        $query->whereNotNull('subscription_commissions.agent_payment_date');
                    } elseif (strtolower($searchValue) === 'pending') {
                        $query->whereNull('subscription_commissions.agent_payment_date');
                    }
                }
            }

            // Total records (unfiltered)
            $totalRecords = SubscriptionCommission::count();

            // Filtered records count
            $recordsFiltered = (clone $query)->count();

            // Column mapping for sort
            $columnMap = [
                'agent_name' => 'agent_details.name',
                'agent_id' => 'users.user_id',
                'date' => 'subscription_commissions.created_at',
                'status' => 'subscription_commissions.agent_payment_date',
            ];

            $sortColumnData = $request->input("columns.{$sortColumnIndex}.data");
            $dbSortColumn = $columnMap[$sortColumnData] ?? 'subscription_commissions.created_at';
            $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

            // NULL last sorting
            $query
                ->orderByRaw("CASE WHEN {$dbSortColumn} IS NULL THEN 1 ELSE 0 END")
                ->orderBy($dbSortColumn, $sortDirection);

            // Paginate
            $commissions = $query->offset($start)->limit($length)->get();

            $data = $commissions->map(function ($row) use ($baseUrl) {

                $shop = DB::connection($row->module_type)
                    ->table('shops')
                    ->where('shop_id', $row->shop_id)
                    ->first();

                $shop_entity_id = $shop
                    ? (new \DateTime($shop->created_at))->format('dmY') . $shop->shop_id
                    : '–';

                $agent_entity_id = $row->user_created_at
                    ? (new \DateTime($row->user_created_at))->format('dmY') . $row->agent_user_id
                    : '–';

                return [
                    'subscription_commission_id' => $row->id,
                    'agent_name' => $row->agent_name ?? '–',
                    'agent_entity_id' => $agent_entity_id,
                    'agent_photo' => CommonHelpher::getImageUrl($row->agent_photo ?? null, $baseUrl . '/agent/photo/'),
                    'shop_name' => $shop?->name ?? '–',
                    'shop_entity_id' => $shop_entity_id,
                    'date_of_subscription' => $row->created_at,
                    'amount' => $row->agent_amount,
                    'agent_payment_date' => $row->agent_payment_date ?? null,
                    'payment_mode' => $row->payment_mode ?? '–',
                ];
            });

            return response()->json([
                'status' => 'success',
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'subscription_commission_id' => 'required|exists:subscription_commissions,id',
                'payment_mode' => 'required|in:cash,online',
                'payment_note' => 'nullable|string|max:500',
            ]);

            $commission = SubscriptionCommission::findOrFail($validated['subscription_commission_id']);

            // Guard against duplicate payment
            if ($commission->agent_payment_date) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment has already been made for this commission.',
                ], 422);
            }

            // ── Generate Transaction ID ──────────────────────────────────────────
            // Format: ddmmYYYY + auto-increment per day
            // e.g. first payment on 04 Apr 2026 → "040420261"
            //      second payment same day       → "040420262"

            $today = now();
            $datePrefix = $today->format('dmY');   // "0442026" wait — format: d=04, m=04, Y=2026

            // Count how many payments were already made TODAY
            $todayCount = SubscriptionCommission::whereNotNull('agent_payment_date')
                ->whereDate('agent_payment_date', $today->toDateString())
                ->count();

            $sequence = $todayCount + 1;           // next sequence number
            $transaction_id = $datePrefix . $sequence;  // e.g. "04042026" . "1" = "040420261"

            // ── Save ─────────────────────────────────────────────────────────────
            $commission->update([
                'payment_mode' => $validated['payment_mode'],
                'payment_status' => 'paid',
                'note' => $validated['payment_note'] ?? null,
                'agent_payment_date' => $today,
                'transaction_id' => $transaction_id,
            ]);

            $cacheKey = env('CACHE_KEY_PREFIX') . 'sub_commissions_' . $commission->user_id;
            Cache::store('memcached')->forget($cacheKey);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment recorded successfully.',
                'transaction_id' => $transaction_id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}