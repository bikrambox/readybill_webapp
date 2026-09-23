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
use Modules\Authentication\Entities\User;
use Modules\Core\Entities\Subscription;
use Illuminate\Support\Facades\Cache;

use Validator;
use Illuminate\Support\Facades\Schema;

use Modules\Core\Helpers\CommonHelpher;

use DateTime;


class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        return view('admin::dashboard');
    }

    public function dashboardStatsData()
    {
        $admin = Auth::guard('admin')->user();
        $cacheKey = 'dashboard_counts_' . $admin->mobile;


        return Cache::store('memcached')->remember($cacheKey, now()->addMinutes(30), function () {

            // 🔹 Count shops grouped by country
            $shopCounts = DB::connection('central')
                ->table('users')
                ->where('isAdmin', 1)
                ->whereNotNull('country_details')
                ->selectRaw("
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.code')) AS code,
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.name')) AS name,
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.flag')) AS flag,
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.currency_symbol')) AS currency_symbol,
                    users.module_type AS module_type,
                    COUNT(*) AS count
                ")
                ->groupBy('code', 'name', 'flag', 'currency_symbol','module_type')
                ->get();

            // 🔹 Get admins with tenant (module_type)
            $admins = DB::connection('central')
                ->table('users')
                ->where('isAdmin', 1)
                ->whereNotNull('country_details')
                ->selectRaw("
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.code')) AS code,
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.name')) AS name,
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.flag')) AS flag,
                    JSON_UNQUOTE(JSON_EXTRACT(country_details, '$.currency_symbol')) AS currency_symbol,
                    module_type
                ")
                ->get();

            // 🔹 Prepare totals per country
            $totals = [];

            foreach ($admins as $admin) {
                $code = $admin->code ?: 'UNKNOWN';

                if (!isset($totals[$code])) {
                    $totals[$code] = [
                        'code' => $admin->code,
                        'name' => $admin->name,
                        'flag' => $admin->flag,
                        'total' => 0,
                        'currency' => $admin->currency_symbol,
                        'connections' => [],
                    ];
                }

                $totals[$code]['connections'][$admin->module_type] = true;
            }

            // 🔹 Sum billing totals (CAST to DECIMAL)
            foreach ($totals as $code => &$data) {
                $sum = 0;

                foreach (array_keys($data['connections']) as $conn) {
                    try {
                        $tenantTotal = DB::connection($conn)
                            ->table('billing')
                            ->selectRaw('SUM(CAST(total_price AS DECIMAL(15,2))) AS total')
                            ->value('total');

                        $sum += (float) $tenantTotal;
                    } catch (\Throwable $e) {
                        // Skip invalid tenant connection
                    }
                }

                $data['total'] = round($sum, 2);
                unset($data['connections']);
            }
            unset($data);

            // 🔹 Return final response
            return response()->json([
                'shop_counts_by_country' => $shopCounts,
                'billing_totals_by_country' => array_values($totals),
            ], 200);
        });
    }

    public function shopsList($module_type='grocery_india')
    {
        // $shops = Shop::all();
        // return view('admin::shops', compact('shops'));
        return view('admin::shops',compact('module_type'));
    }

    public function shopListData(Request $request)
    {
        try {
            $draw = $request->input('draw', 0);
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $sortColumnIndex = $request->input('order.0.column', 0);
            $sortDirection = $request->input('order.0.dir', 'asc');
            $searchValue = $request->input('search.value', '');
            $filter_option = $request->input('filter_option', 'name');
            $module_type = $request->input('module_type', 'grocery_india');

            // Get the central database connection name
            $connectionName = $request->attributes->get('central');
            $databaseName = DB::connection($connectionName)->getDatabaseName();


            // Check if module_type is not empty
            if (!$module_type) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ], 200);
            }

            // Switch to the shop type connection
            DB::setDefaultConnection($module_type);

            // Check if 'shops' table exists
            if (!Schema::hasTable('shops')) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ], 200);
            }

            // Define sorting columns
            $columns = [
                'shops.name',
                'shops.email',
                'user_mobile', // Use alias for sorting
                'shops.address'
            ];
            $sortColumn = $columns[$sortColumnIndex] ?? 'shops.name';

            // Base query
            $selectQuery = "
            SELECT shops.*, 
                (SELECT name FROM $databaseName.users WHERE users.user_id = shops.user_id) AS user_name,
                (SELECT mobile FROM $databaseName.users WHERE users.user_id = shops.user_id) AS user_mobile,
                (SELECT active FROM $databaseName.users WHERE users.user_id = shops.user_id) AS user_active
            FROM shops
        ";

            // Initialize WHERE clause
            $whereClause = '';

            // Apply filtering across all fields, including entity_id
            if (!empty($searchValue)) {
                $whereClause .= " WHERE (
                shops.name LIKE '%$searchValue%' 
                OR shops.email LIKE '%$searchValue%' 
                OR shops.address LIKE '%$searchValue%' 
                OR EXISTS (SELECT 1 FROM $databaseName.users WHERE users.user_id = shops.user_id AND users.mobile LIKE '%$searchValue%')
                OR CONCAT(DATE_FORMAT(shops.created_at, '%d%m%Y'), shops.shop_id) LIKE '%$searchValue%'
            )";
            }

            // Append WHERE clause to main query
            $selectQuery .= $whereClause;

            // Sorting logic
            $selectQuery .= " ORDER BY $sortColumn $sortDirection";

            // Add LIMIT
            $selectQuery .= " LIMIT $start, $length";

            // Generate cache key
            $cacheKey = 'shop_data_' . Auth::guard('admin')->user()->mobile;

            // Check if cached data exists
            $cachedData = Cache::store('memcached')->get($cacheKey);
            if ($cachedData && empty($searchValue) && empty($filter_option)) {
                return $cachedData;
            }

            // Count total records
            $totalRecordsQuery = "SELECT COUNT(*) as count FROM shops";
            $totalRecordsResult = DB::select(DB::raw($totalRecordsQuery)->getValue(DB::connection()->getQueryGrammar()));
            $totalRecords = $totalRecordsResult[0]->count;

            // Count filtered records
            $recordsFilteredQuery = "SELECT COUNT(*) as count FROM shops";
            if (!empty($whereClause)) {
                $recordsFilteredQuery .= $whereClause;
            }

            $recordsFilteredResult = DB::select(DB::raw($recordsFilteredQuery)->getValue(DB::connection()->getQueryGrammar()));
            $recordsFiltered = $recordsFilteredResult[0]->count;

            // Execute the main query
            $items = DB::select(DB::raw($selectQuery)->getValue(DB::connection()->getQueryGrammar()));

            // Add `entity_id`
            foreach ($items as &$shop) {
                $shop->entity_id = (new \DateTime($shop->created_at))->format('dmY') . $shop->shop_id;

                $shop->shop_subscription = DB::table('shop_subscriptions')
                    ->where('shop_id', $shop->shop_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            // Apply entity_id filter
            if ($filter_option === 'entity_id' && !empty($searchValue)) {
                $items = array_filter($items, function ($shop) use ($searchValue) {
                    return stripos($shop->entity_id, $searchValue) !== false;
                });
                $recordsFiltered = count($items);
            }

            // Prepare the response
            $response = response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => array_values($items), // Reset array keys after filtering
            ], 200);

            // Cache the response for future requests
            Cache::store('memcached')->put($cacheKey, $response, now()->addMinutes(30));

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function shopStaffList($shop_id)
    {
        $shop = Shop::find($shop_id);
        $business_name = $shop->business_name ?? '';

        return view('admin::staff', compact('shop_id', 'business_name'));
    }


    public function shopStaffListData(Request $request)
    {
        try {
            $draw = isset($request['draw']) ? intval($request['draw']) : 0;
            $start = isset($request['start']) ? intval($request['start']) : 0;
            $length = isset($request['length']) ? intval($request['length']) : 10;
            $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
            $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
            $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';

            $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'name';

            $shop_id = isset($request['shop_id']) ? $request['shop_id'] : 0;

            // Define columns to sort by
            $columns = ['users.name', 'staff.email', 'users.mobile', 'staff.address']; // Adjust with actual column names
            $sortColumn = $columns[$sortColumnIndex] ?? 'users.name'; // Default to 'users.name'

            // Base query
            $selectQuery = "
                    SELECT staff.*, users.*
                    FROM staff 
                    JOIN users ON staff.user_id = users.user_id
                    WHERE staff.addedBy = $shop_id
                ";

            // Apply filtering
            if ($searchValue !== "") {
                if ($filter_option == 'name') {
                    $selectQuery .= " AND users.name LIKE '%$searchValue%'";
                } elseif ($filter_option == 'email') {
                    $selectQuery .= " AND staff.email LIKE '%$searchValue%'";
                } elseif ($filter_option == 'mobile') {
                    $selectQuery .= " AND users.mobile LIKE '%$searchValue%'";
                } elseif ($filter_option == 'address') {
                    $selectQuery .= " AND staff.address LIKE '%$searchValue%'";
                }
            }

            // Sorting and limiting
            $selectQuery .= " ORDER BY $sortColumn $sortDirection";
            $selectQuery .= " LIMIT $start, $length";

            // Generate a unique cache key
            $cacheKey = 'sub_users_' . $shop_id;

            // Check if the data is already cached
            $cachedData = Cache::store('memcached')->get($cacheKey);

            if ($cachedData && $searchValue == "" && $filter_option == "") {
                return $cachedData; // If cached data exists, return it directly
            }

            // Count total records
            $totalRecordsQuery = "
                    SELECT COUNT(*) as count 
                    FROM staff 
                    WHERE addedBy = $shop_id
                ";
            $totalRecordsResult = DB::select(DB::raw($totalRecordsQuery));
            $totalRecords = $totalRecordsResult[0]->count;

            // Count filtered records
            $recordsFilteredQuery = "
                    SELECT COUNT(*) as count 
                    FROM staff 
                    JOIN users ON staff.user_id = users.user_id
                    WHERE staff.addedBy = $shop_id
                ";
            if ($searchValue !== "") {
                if ($filter_option == 'name') {
                    $recordsFilteredQuery .= " AND users.name LIKE '%$searchValue%'";
                } elseif ($filter_option == 'email') {
                    $recordsFilteredQuery .= " AND staff.email LIKE '%$searchValue%'";
                } elseif ($filter_option == 'mobile') {
                    $recordsFilteredQuery .= " AND users.mobile LIKE '%$searchValue%'";
                } elseif ($filter_option == 'address') {
                    $recordsFilteredQuery .= " AND staff.address LIKE '%$searchValue%'";
                }
            }
            $recordsFilteredResult = DB::select(DB::raw($recordsFilteredQuery));
            $recordsFiltered = $recordsFilteredResult[0]->count;

            // Execute the main query
            $items = DB::select(DB::raw($selectQuery));

            $response = response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $items,
            ], 200);

            // Cache the response for future requests
            Cache::store('memcached')->put($cacheKey, $response, now()->addMinutes(30)); // Cache for 30 minutes

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function shopItemList($module_type, $shop_id)
    {

        $module = CommonHelpher::getModuleName($module_type);

        // $shop = Shop::find($shop_id);

        $shop = DB::connection($module_type)
            ->table('shops')
            ->where('shop_id', $shop_id)
            ->first();

        $business_name = $shop->business_name ?? '';

        return view('admin::items', compact('shop_id', 'business_name', 'module_type'));
    }


    // public function shopItemListData(Request $request)
    // {
    //     try {
    //         $draw = isset($request['draw']) ? intval($request['draw']) : 0;
    //         $start = isset($request['start']) ? intval($request['start']) : 0;
    //         $length = isset($request['length']) ? intval($request['length']) : 10;
    //         $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
    //         $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
    //         $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';
    //         $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'item_name';

    //         $shop_id = isset($request['shop_id']) ? $request['shop_id'] : 0;
    //         $module_type = $request->input('module_type', 'grocery');

    //         $module = CommonHelpher::getModuleName($module_type);
    //         $tableNameHelper = "Modules\\{$module}\\Helpers\\TableNameHelper";

    //         $table_name = $tableNameHelper::getTableNameUsingShopId($shop_id);

    //         if ($table_name == "") {
    //             return response()->json([
    //                 'draw' => $draw,
    //                 'recordsTotal' => 0,
    //                 'recordsFiltered' => 0,
    //                 'data' => [],
    //             ], 200);
    //         }

    //         // Get the central database connection name
    //         $connectionName = $request->attributes->get('central');
    //         $databaseName = DB::connection($connectionName)->getDatabaseName();

    //         // Switch to the shop type connection
    //         DB::setDefaultConnection($module_type);
    //         // Build the base query
    //         $selectQuery = "SELECT * FROM $table_name";


    //         // Execute the count query for filtered records
    //         $recordsFilteredQuery = "SELECT COUNT(*) as count FROM $table_name";

    //         if ($searchValue !== "") {
    //             if ($filter_option == 'item_name') {
    //                 $selectQuery .= " WHERE item_name LIKE '%$searchValue%'";
    //                 $recordsFilteredQuery .= " WHERE item_name LIKE '%$searchValue%'";
    //             } elseif ($filter_option == 'quantity') {
    //                 $selectQuery .= " WHERE quantity LIKE '%$searchValue%'";
    //                 $recordsFilteredQuery .= " WHERE quantity LIKE '%$searchValue%'";
    //             } elseif ($filter_option == 'mrp') {
    //                 if (is_numeric($searchValue)) {
    //                     $selectQuery .= " WHERE mrp=$searchValue";
    //                     $recordsFilteredQuery .= " WHERE mrp=$searchValue";
    //                 } else {
    //                     $selectQuery .= " WHERE mrp=-1";
    //                     $recordsFilteredQuery .= " WHERE mrp=-1";
    //                 }
    //             } elseif ($filter_option == 'sale_price') {
    //                 if (is_numeric($searchValue)) {
    //                     $selectQuery .= " WHERE sale_price=$searchValue";
    //                     $recordsFilteredQuery .= " WHERE sale_price=$searchValue";
    //                 } else {
    //                     $selectQuery .= " WHERE sale_price=-1";
    //                     $recordsFilteredQuery .= " WHERE sale_price=-1";
    //                 }
    //             } elseif ($filter_option == 'hsn') {
    //                 $selectQuery .= " WHERE hsn LIKE '%$searchValue%'";
    //                 $recordsFilteredQuery .= " WHERE hsn LIKE '%$searchValue%'";
    //             } elseif ($filter_option == 'gst') {
    //                 if (is_numeric($searchValue)) {
    //                     $selectQuery .= " WHERE rate1=$searchValue";
    //                     $recordsFilteredQuery .= " WHERE rate1=$searchValue";
    //                 } else {
    //                     $selectQuery .= " WHERE rate1=-1";
    //                     $recordsFilteredQuery .= " WHERE rate1=-1";
    //                 }
    //             } elseif ($filter_option == 'cess') {
    //                 if (is_numeric($searchValue)) {
    //                     $selectQuery .= " WHERE rate2=$searchValue";
    //                     $recordsFilteredQuery .= " WHERE rate2=$searchValue";
    //                 } else {
    //                     $selectQuery .= " WHERE rate2=-1";
    //                     $recordsFilteredQuery .= " WHERE rate2=-1";
    //                 }
    //             }
    //         }

    //         // Execute the count query for filtered records
    //         $recordsFilteredResult = DB::select(DB::raw($recordsFilteredQuery)->getValue(DB::connection()->getQueryGrammar()));
    //         $recordsFiltered = $recordsFilteredResult[0]->count;

    //         // Handle sorting
    //         $columns = ['item_name', 'quantity', 'sale_price'];
    //         $sortColumn = $columns[$sortColumnIndex] ?? 'item_name';
    //         $selectQuery .= " ORDER BY $sortColumn $sortDirection";

    //         // Paginate the results
    //         $selectQuery .= " LIMIT $start, $length";

    //         // Execute the count query for total records
    //         $totalRecordsQuery = "SELECT COUNT(*) as count FROM $table_name";
    //         $totalRecordsResult = DB::select(DB::raw($totalRecordsQuery)->getValue(DB::connection()->getQueryGrammar()));
    //         $totalRecords = $totalRecordsResult[0]->count;

    //         // Execute the select query
    //         $items = DB::select(DB::raw($selectQuery)->getValue(DB::connection()->getQueryGrammar()));

    //         // Check if the data is already cached
    //         $cacheKey = 'items_' . $shop_id;
    //         $cachedData = Cache::store('memcached')->get($cacheKey);

    //         if ($cachedData && $searchValue == "" && $filter_option == "") {
    //             return $cachedData; // If cached data exists, return it directly
    //         }

    //         // Cache the response for future requests
    //         Cache::store('memcached')->put($cacheKey, response()->json([
    //             'draw' => $draw,
    //             'recordsTotal' => $totalRecords,
    //             'recordsFiltered' => $recordsFiltered,
    //             'data' => $items,
    //             'selectQuery' => $selectQuery,
    //             'searchValue' => $searchValue,
    //             'filter_option' => $filter_option,
    //         ], 200));

    //         // Return the response
    //         return response()->json([
    //             'draw' => $draw,
    //             'recordsTotal' => $totalRecords,
    //             'recordsFiltered' => $recordsFiltered,
    //             'data' => $items,
    //             'selectQuery' => $selectQuery,
    //             'searchValue' => $searchValue,
    //             'filter_option' => $filter_option,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function shopItemListData(Request $request)
    {
        try {

            $draw = (int) $request->input('draw', 0);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);

            $sortColumnIndex = (int) $request->input('order.0.column', 0);
            $sortDirection = $request->input('order.0.dir', 'asc');
            $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc'])
                ? $sortDirection
                : 'asc';

            $searchValue = $request->input('search.value', '');
            $filter_option = $request->input('filter_option', 'item_name');

            $shop_id = $request->input('shop_id', 0);
            $module_type = $request->input('module_type', 'grocery');

            $module = CommonHelpher::getModuleName($module_type);
            $tableNameHelper = "Modules\\{$module}\\Helpers\\TableNameHelper";

            $table_name = $tableNameHelper::getTableNameUsingShopId($shop_id);

            if (empty($table_name)) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ], 200);
            }

            // Use explicit connection
            $connection = DB::connection($module_type);

            // Check table exists
            if (!Schema::connection($module_type)->hasTable($table_name)) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'message' => 'Table does not exist'
                ], 200);
            }

            // Allowed sortable columns (prevent SQL injection)
            $columns = ['item_name', 'quantity', 'sale_price'];
            $sortColumn = $columns[$sortColumnIndex] ?? 'item_name';

            // Base query
            $query = $connection->table($table_name);

            // Total records before filter
            $totalRecords = $query->count();

            // Apply filter
            if (!empty($searchValue)) {

                switch ($filter_option) {

                    case 'item_name':
                        $query->where('item_name', 'LIKE', "%{$searchValue}%");
                        break;

                    case 'quantity':
                        $query->where('quantity', 'LIKE', "%{$searchValue}%");
                        break;

                    case 'mrp':
                        if (is_numeric($searchValue)) {
                            $query->where('mrp', $searchValue);
                        } else {
                            $query->whereRaw('1=0');
                        }
                        break;

                    case 'sale_price':
                        if (is_numeric($searchValue)) {
                            $query->where('sale_price', $searchValue);
                        } else {
                            $query->whereRaw('1=0');
                        }
                        break;

                    case 'hsn':
                        $query->where('hsn', 'LIKE', "%{$searchValue}%");
                        break;

                    case 'gst':
                        if (is_numeric($searchValue)) {
                            $query->where('rate1', $searchValue);
                        } else {
                            $query->whereRaw('1=0');
                        }
                        break;

                    case 'cess':
                        if (is_numeric($searchValue)) {
                            $query->where('rate2', $searchValue);
                        } else {
                            $query->whereRaw('1=0');
                        }
                        break;

                    default:
                        $query->where('item_name', 'LIKE', "%{$searchValue}%");
                        break;
                }
            }

            // Count after filtering
            $recordsFiltered = $query->count();

            // Sorting + Pagination
            $items = $query
                ->orderBy($sortColumn, $sortDirection)
                ->offset($start)
                ->limit($length)
                ->get();

            // Cache only if no search applied
            if (empty($searchValue)) {

                $cacheKey = "items_{$shop_id}_{$start}_{$length}_{$sortColumn}_{$sortDirection}";

                Cache::store('memcached')->put($cacheKey, [
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $items,
                ], now()->addMinutes(5));
            }

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

    public function getItemTags($module_type, $shop_id, $item_id)
    {


        // $shop = Shop::find($shop_id);

        // $module_type = $shop->user->module_type;

        // Switch to the shop type connection
        DB::setDefaultConnection($module_type);


        $module = CommonHelpher::getModuleName($module_type);
        $tableNameHelper = "Modules\\{$module}\\Helpers\\TableNameHelper";

        $table_name = $tableNameHelper::getTableNameUsingShopId($shop_id);

        if ($table_name == "") {
            return response()->json([
                'data' => [],
            ], 200);
        }

        $item = DB::table($table_name)->find($item_id);

        return response()->json([
            'status' => 'success',
            'data' => $item
        ], 200);

    }

    public function updateItemTag(Request $request)
    {

        $validate = Validator::make($request->all(), [

            'module_type' => [
                'required'
            ],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $module = CommonHelpher::getModuleName($request->module_type);


        $validate = Validator::make($request->all(), [
            // 'shop_id' => 'required|exists:shops,shop_id',

            'shop_id' => [
                'required',
                function ($attribute, $value, $fail) use ($module) {

                    $class = "\\Modules\\{$module}\\Entities\\Shop";

                    if (!class_exists($class)) {
                        $fail('Invalid module provided.');
                        return;
                    }

                    if (!$class::where('shop_id', $value)->exists()) {
                        $fail('The selected shop_id is invalid.');
                    }
                },
            ],

        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $ShopClass = "\\Modules\\{$module}\\Entities\\Shop";
        $shop = $ShopClass::find($request->shop_id);

        $module_type = $shop->user->module_type;

        // Switch to the shop type connection
        DB::setDefaultConnection($module_type);

        $module = CommonHelpher::getModuleName($module_type);
        $tableNameHelper = "Modules\\{$module}\\Helpers\\TableNameHelper";

        // Get the dynamic table name
        $table_name = $tableNameHelper::getTableNameUsingShopId($request->shop_id);

        // Perform a custom validation for item_id in the dynamic table
        if (!DB::table($table_name)->where('id', $request->item_id)->exists()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => ['item_id' => ['The selected item_id does not exist in the specified table.']],
            ], 403);
        }

        // Step 3: Validate tags
        $validateTags = Validator::make($request->all(), [
            'tags' => [
                'required',
                'string', // Ensure it is a string before decoding
                function ($attribute, $value, $fail) {
                    // Decode the JSON string to an array if it's a string
                    if (is_string($value)) {
                        $value = json_decode($value, true);  // Decode JSON to array
                    }

                    // Ensure the value is now an array
                    if (!is_array($value)) {
                        $fail('The ' . $attribute . ' field must be an array.');
                        return;
                    }

                    // Ensure the array is not empty
                    if (empty($value)) {
                        $fail('The ' . $attribute . ' field must have at least one item.');
                        return;
                    }

                    // Ensure the number of tags is not more than 5
                    if (count($value) > 50) {
                        $fail('The ' . $attribute . ' field can have a maximum of 50 tags.');
                        return;
                    }

                    // Check each tag for invalid characters
                    foreach ($value as $tag) {
                        if (!preg_match('/^[a-zA-Z0-9 ]+$/', $tag)) {
                            $fail('Special Characters are not supported in tag. Please remove the special characters and then submit.');
                            break;
                        }

                        // Check if the tag is numeric-only
                        if (is_numeric($tag)) {
                            $fail('Tags cannot contain only numbers. Please provide a valid tag.');
                            break;
                        }

                        // Check for tag word length
                        if (strlen($tag) > 250) {
                            $fail('Each tag must not exceed 250 characters.');
                            break;
                        }
                    }
                },
            ],
        ]);

        if ($validateTags->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validateTags->errors(),
            ], 403);
        }


        DB::table($table_name)->where('id', $request->item_id)->update([
            'tags' => $request->tags
        ]);

        $updatedItem = DB::table($table_name)->where('id', $request->item_id)->first();

        return response()->json([
            'status' => 'success',
            'data' => $updatedItem
        ], 200);

    }


    public function shopTransactions($user_id)
    {
        return view('admin::transactions', compact('user_id'));
    }

    public function shopTransaction($user_id, $transaction_id)
    {

        $user = User::find($user_id);
        $module_type = $user->module_type;

        // Switch to the shop type connection
        DB::setDefaultConnection($module_type);

        $transactions = DB::table('billing')->find($transaction_id);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ], 200);
    }


    public function shopTransactionData(Request $request)
    {
        try {
            // Pagination and sorting parameters
            $draw = isset($request['draw']) ? intval($request['draw']) : 0;
            $start = isset($request['start']) ? intval($request['start']) : 0;
            $length = isset($request['length']) ? intval($request['length']) : 10;
            $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
            $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';

            // Search and filter parameters
            $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';
            $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'invoice_number';

            // Date range parameters
            $dateFrom = $request->input('date_from'); // Expected format: dd/mm/yyyy
            $dateTo = $request->input('date_to');     // Expected format: dd/mm/yyyy

            // User parameters
            $user_id = $request->input('user_id', 0);
            $user = User::find($user_id);
            $module_type = $user->module_type;

            // Switch to the shop type connection
            DB::setDefaultConnection($module_type);

            $table_name = 'billing';

            // Build base query based on user role
            if ($user->isAdmin == 1) {
                $shopId = $user->shop->shop_id;
                $userId = $user->user_id;

                $selectQuery = "
                SELECT 
                    billing.*, 
                    shops.name AS user_name 
                FROM 
                    $table_name AS billing
                LEFT JOIN 
                    shops ON billing.shop_id = shops.shop_id
                WHERE 
                    billing.shop_id = $shopId
            ";
            }

            // Handle date range filter
            if (!empty($dateFrom) && !empty($dateTo)) {
                // Convert from dd/mm/yyyy to Y-m-d H:i:s format
                $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
                $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);

                if ($dateFromFormatted && $dateToFormatted) {
                    // Set time to start of day for from date and end of day for to date
                    $dateFromFormatted->setTime(0, 0, 0);
                    $dateToFormatted->setTime(23, 59, 59);

                    $dateFromStr = $dateFromFormatted->format('Y-m-d H:i:s');
                    $dateToStr = $dateToFormatted->format('Y-m-d H:i:s');

                    $selectQuery .= " AND billing.created_at BETWEEN '$dateFromStr' AND '$dateToStr'";
                }
            }

            // Add search filters
            if ($searchValue !== "") {
                if ($filter_option == 'invoice_number') {
                    $selectQuery .= " AND invoice_number LIKE '%$searchValue%'";
                } else if ($filter_option == 'date') {
                    $searchDate = DateTime::createFromFormat('d/m/Y', $searchValue);
                    if ($searchDate) {
                        $searchDateStr = $searchDate->format('Y-m-d');
                        $selectQuery .= " AND DATE(billing.created_at) = '$searchDateStr'";
                    }
                } else if ($filter_option == 'user') {
                    $selectQuery .= " AND (billing.shop_id LIKE '%$searchValue%' OR shops.name LIKE '%$searchValue%')";
                } else if ($filter_option == 'total') {
                    if (is_numeric($searchValue)) {
                        $selectQuery .= " AND total_price = $searchValue";
                    } else {
                        $selectQuery .= " AND total_price = -1";
                    }
                }
            }

            // Add sorting
            $columns = ['invoice_number', 'total_price', 'created_at'];
            $sortColumn = $columns[$sortColumnIndex] ?? 'invoice_number';
            $selectQuery .= " ORDER BY $sortColumn $sortDirection";

            // Count total records
            $totalRecordsQuery = "SELECT COUNT(*) as count FROM ($selectQuery) AS countTable";
            $totalRecordsResult = DB::select(DB::raw($totalRecordsQuery)->getValue(DB::connection()->getQueryGrammar()));
            $totalRecords = $totalRecordsResult[0]->count;

            // Apply pagination
            $selectQuery .= " LIMIT $start, $length";

            // Execute the main query
            $items = DB::select(DB::raw($selectQuery)->getValue(DB::connection()->getQueryGrammar()));

            // Prepare response
            $response = response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $items,
                'selectQuery' => $selectQuery,
                'searchValue' => $searchValue,
                'filter_option' => $filter_option,
                'date_from_raw' => $dateFrom,
                'date_to_raw' => $dateTo,
                'date_from_formatted' => isset($dateFromStr) ? $dateFromStr : null,
                'date_to_formatted' => isset($dateToStr) ? $dateToStr : null,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'selectQuery' => isset($selectQuery) ? $selectQuery : null,
                'date_from_raw' => $request->input('date_from'),
                'date_to_raw' => $request->input('date_to'),
            ], 500);
        }
    }

    public function deleteTransactionByDateRange(Request $request)
    {
        try {
            $request->validate([
                'date_from' => 'required|date_format:d/m/Y',
                'date_to' => 'required|date_format:d/m/Y',
                'user_id' => 'required|numeric|exists:central.users'
            ]);

            // Convert dates to database format
            $dateFrom = DateTime::createFromFormat('d/m/Y', $request->date_from)->format('Y-m-d 00:00:00');
            $dateTo = DateTime::createFromFormat('d/m/Y', $request->date_to)->format('Y-m-d 23:59:59');


            $user = User::find($request->user_id);
            $shop_id = $user->shop->shop_id;
            $module_type = $user->module_type;

            // Switch to the shop type connection
            DB::setDefaultConnection($module_type);

            // Delete transactions within the date range for the specific user using DB query
            $deletedCount = DB::table('billing')
                ->where('shop_id', $shop_id)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Deleted $deletedCount transactions successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting transactions: ' . $e->getMessage()
            ], 500);
        }
    }

}

