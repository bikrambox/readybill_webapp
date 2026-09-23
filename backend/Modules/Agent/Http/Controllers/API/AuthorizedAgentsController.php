<?php

namespace Modules\Agent\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Authentication\Entities\User;
use Modules\Agent\Helpers\CommonHelpher;

class AuthorizedAgentsController extends Controller
{
    public function authorizedAgentsList(Request $request)
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
                ->where('users.isVerified', 1)
                ->where('users.active', 1)
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
                return $cachedData;
            }

            // Paginate
            $agents = $query->offset($start)->limit($length)->get();

            // Append full photo URL using CommonHelpher
            $agents->transform(function ($agent) use ($photoDir) {
                $agent->photo = CommonHelpher::getImageUrl($agent->photo ?? null, $photoDir);
                return $agent;
            });

            $response = response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $agents,
                'searchValue' => $searchValue,
                'filter_option' => $filter_option,
            ], 200);

            // Cache only default unfiltered/unsorted state
            if ($searchValue === '' && $filter_option === 'name' && $sortColumnIndex === 0) {
                Cache::store('memcached')->put($cacheKey, $response);
            }

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}