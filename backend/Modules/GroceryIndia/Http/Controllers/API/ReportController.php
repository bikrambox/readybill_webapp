<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

use DateTime;
use Carbon\Carbon;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;


use Modules\GroceryIndia\Helpers\BillingHelpher;
use Modules\Authentication\Helpers\SubscriptionHelper;

use Modules\GroceryIndia\Entities\ReportGeneration;
use Modules\Authentication\Entities\User;

use Validator;
use Illuminate\Support\Facades\App;
use Modules\GroceryIndia\Jobs\GenerateReportJob;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{

    ## CODE UPDATE FOR TRASACTION DATA YEAR RESTRICTION BASED ON SUBSCRIPTION PLAN
    // public function transactionReport(Request $request)
    // {
    //     if (!Auth::guard('api')->check()) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.Unauthorized')
    //         ], 401);
    //     }

    //     try {
    //         $draw = isset($request['draw']) ? intval($request['draw']) : 0;
    //         $start = isset($request['start']) ? intval($request['start']) : 0;
    //         $length = isset($request['length']) ? intval($request['length']) : 10;
    //         $sortColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
    //         $sortDirection = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'asc';
    //         $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';
    //         $filter_option = isset($request['filter_option']) ? $request['filter_option'] : 'invoice_number';

    //         // Date range parameters
    //         $dateFrom = $request->input('date_from'); // dd/mm/yyyy
    //         $dateTo = $request->input('date_to');    // dd/mm/yyyy

    //         $user = Auth::guard('api')->user();
    //         $table_name = 'billing';

    //         // Get subscription details
    //         if ($user->isAdmin == 1) {
    //             $shopId = $user->shop->shop_id;
    //         } else {
    //             $shopId = $user->staff->addedBy;
    //         }

    //         $allowedYears = SubscriptionHelper::getReportDataYears($shopId, $user->module_type);

    //         // Calculate minimum allowed date based on subscription
    //         $minAllowedDate = Carbon::now()->subYears($allowedYears)->startOfDay();
    //         $minAllowedDateFormatted = $minAllowedDate->format('d/m/Y');

    //         // Get subscription type for messages
    //         $shopSubscription = DB::connection($user->module_type)
    //             ->table('shop_subscriptions')
    //             ->where('shop_id', $shopId)
    //             ->orderBy('end_date', 'desc')
    //             ->first();

    //         $subscriptionData = SubscriptionHelper::subsriptionDataFormat($shopSubscription->subscription_data);

    //         $isFree = $subscriptionData['subscription_id'] == config('subscription.free_subscription_id', 1);


    //         // Validate date inputs
    //         if (!empty($dateFrom) && !empty($dateTo)) {
    //             $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
    //             $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
    //             $currentDate = new DateTime();

    //             if (!$dateFromFormatted || !$dateToFormatted) {
    //                 return response()->json([
    //                     'status' => 'failed',
    //                     'message' => __('validation.Invalid date format. Use dd/mm/yyyy')
    //                 ], 400);
    //             }

    //             if ($dateFromFormatted > $currentDate || $dateToFormatted > $currentDate) {
    //                 return response()->json([
    //                     'status' => 'failed',
    //                     'message' => __('validation.Future dates are not allowed')
    //                 ], 400);
    //             }

    //             // Check if date is within subscription limit
    //             $dateFromCarbon = Carbon::createFromFormat('d/m/Y', $dateFrom)->startOfDay();
    //             if ($dateFromCarbon->lessThan($minAllowedDate)) {
    //                 if ($isFree) {
    //                     // Message for free plan
    //                     $message = __('validation.free_plan_year_restriction', [
    //                         'years' => $allowedYears,
    //                         'date' => $minAllowedDateFormatted
    //                     ]);
    //                 } else {
    //                     // Message for paid plan
    //                     $message = __('validation.paid_plan_year_restriction', [
    //                         'years' => $allowedYears,
    //                         'date' => $minAllowedDateFormatted
    //                     ]);
    //                 }

    //                 return response()->json([
    //                     'status' => 'failed',
    //                     'message' => $message,
    //                     'data' => [
    //                         'allowed_years' => $allowedYears,
    //                         'minimum_date' => $minAllowedDateFormatted,
    //                         'subscription_type' => $isFree ? 'free' : 'premium',
    //                     ]
    //                 ], 403);
    //             }

    //             $interval = $dateFromFormatted->diff($dateToFormatted);
    //             $months = ($interval->y * 12) + $interval->m;
    //             if ($interval->d > 0) {
    //                 $months += 1;
    //             }
    //             // if ($months > 6) {
    //             //     return response()->json([
    //             //         'status' => 'failed',
    //             //         'message' => __('validation.Date range cannot exceed 6 months')
    //             //     ], 400);
    //             // }
    //         }

    //         if (empty($dateFrom) && empty($dateTo)) {
    //             return response()->json([
    //                 'draw' => $draw,
    //                 'recordsTotal' => 0,
    //                 'recordsFiltered' => 0,
    //                 'data' => [],
    //                 'meta' => [
    //                     'allowed_years' => $allowedYears,
    //                     'minimum_date' => $minAllowedDateFormatted,
    //                     'subscription_type' => $isFree ? 'free' : 'premium',
    //                 ]
    //             ], 200);
    //         }

    //         if ($user->isAdmin == 1) {
    //             $userId = $user->user_id;

    //             $selectQuery = "
    //             SELECT 
    //                 billing.*,
    //                 COALESCE(staff.name, shops.name) AS user_name
    //             FROM 
    //                 {$table_name} AS billing
    //             LEFT JOIN 
    //                 shops ON billing.shop_id = shops.shop_id
    //             LEFT JOIN 
    //                 staff ON billing.user_id = staff.user_id AND staff.addedBy = :shopId1
    //             WHERE 
    //                 billing.shop_id = :shopId2
    //         ";

    //             $parameters = [
    //                 'shopId1' => $shopId,
    //                 'shopId2' => $shopId,
    //             ];

    //             $searchParameters = [];

    //             // Apply subscription-based date limit
    //             $minAllowedDateStr = $minAllowedDate->format('Y-m-d H:i:s');
    //             $selectQuery .= " AND billing.created_at >= :minDate";
    //             $parameters['minDate'] = $minAllowedDateStr;

    //             if ($searchValue !== "") {
    //                 $searchConditions = [];
    //                 if ($filter_option == 'invoice_number') {
    //                     $searchConditions[] = "billing.invoice_number LIKE :searchValue";
    //                     $searchParameters['searchValue'] = '%' . $searchValue . '%';
    //                 } 
                    
                    
    //                 // else if ($filter_option == 'date') {
    //                 //     $sv = str_replace('/', '-', $searchValue);
    //                 //     $svMySQL = date('Y-m-d', strtotime($sv));
    //                 //     $searchConditions[] = "DATE(billing.created_at) = :searchValue";
    //                 //     $searchParameters['searchValue'] = $svMySQL;
    //                 // } 
    //                 else if ($filter_option == 'date') {
    //                     $sv = trim($searchValue);
    //                     $sv = preg_replace('/\s+/', '', $sv);
    //                     $sv = str_replace(['-', '.'], '/', $sv);
    //                     $sv = rtrim($sv, '/');

    //                     $searchDate = false;
    //                     $today = new DateTime();

    //                     // dd/mm/yy or d/m/yy
    //                     if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2}$/', $sv)) {
    //                         $searchDate = DateTime::createFromFormat('!j/n/y', $sv);
    //                     }
    //                     // dd/mm/yyyy or d/m/yyyy
    //                     elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $sv)) {
    //                         $searchDate = DateTime::createFromFormat('!j/n/Y', $sv);
    //                     }
    //                     // dd/mm or d/m  => assume current year
    //                     elseif (preg_match('/^\d{1,2}\/\d{1,2}$/', $sv)) {
    //                         $svFull = $sv . '/' . $today->format('Y');
    //                         $searchDate = DateTime::createFromFormat('!j/n/Y', $svFull);
    //                     }
    //                     // dd => assume current month and year
    //                     elseif (preg_match('/^\d{1,2}$/', $sv)) {
    //                         $svFull = $sv . '/' . $today->format('n/Y');
    //                         $searchDate = DateTime::createFromFormat('!j/n/Y', $svFull);
    //                     }

    //                     if ($searchDate instanceof DateTime) {
    //                         $errors = DateTime::getLastErrors();

    //                         if (
    //                             $errors === false ||
    //                             (
    //                                 ($errors['warning_count'] ?? 0) === 0 &&
    //                                 ($errors['error_count'] ?? 0) === 0
    //                             )
    //                         ) {
    //                             $svMySQL = $searchDate->format('Y-m-d');
    //                             $searchConditions[] = "DATE(billing.created_at) = :searchValue";
    //                             $searchParameters['searchValue'] = $svMySQL;
    //                         } else {
    //                             $searchConditions[] = "1 = 0";
    //                         }
    //                     } else {
    //                         $searchConditions[] = "1 = 0";
    //                     }
    //                 }
                    
    //                 else if ($filter_option == 'user') {
    //                     $searchConditions[] = "COALESCE(staff.name, shops.name) LIKE :searchValue";
    //                     $searchParameters['searchValue'] = '%' . $searchValue . '%';
    //                 } else if ($filter_option == 'total') {
    //                     if (is_numeric($searchValue)) {
    //                         $searchConditions[] = "billing.total_price = :searchValue";
    //                         $searchParameters['searchValue'] = $searchValue;
    //                     } else {
    //                         $searchConditions[] = "billing.total_price = -1";
    //                     }
    //                 }

    //                 if (!empty($searchConditions)) {
    //                     $selectQuery .= " AND " . implode(' AND ', $searchConditions);
    //                 }
    //             }

    //             // Date range binding with placeholders
    //             if (!empty($dateFrom) && !empty($dateTo)) {
    //                 $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
    //                 $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
    //                 if ($dateFromFormatted && $dateToFormatted) {
    //                     $dateFromFormatted->setTime(0, 0, 0);
    //                     $dateToFormatted->setTime(23, 59, 59);
    //                     $dateFromStr = $dateFromFormatted->format('Y-m-d H:i:s');
    //                     $dateToStr = $dateToFormatted->format('Y-m-d H:i:s');

    //                     $selectQuery .= " AND billing.created_at BETWEEN :dateFrom AND :dateTo";
    //                     $parameters['dateFrom'] = $dateFromStr;
    //                     $parameters['dateTo'] = $dateToStr;
    //                 }
    //             }

    //             // Count
    //             $totalRecordsQuery = "SELECT COUNT(*) as count FROM ({$selectQuery}) AS countTable";
    //             $countParameters = array_merge($parameters, $searchParameters);
    //             $totalRecordsResult = DB::select($totalRecordsQuery, $countParameters);
    //             $totalRecords = $totalRecordsResult ? (int) $totalRecordsResult[0]->count : 0;

    //             // Sorting
    //             $columns = ['invoice_number', 'total_price', 'created_at'];
    //             $sortColumn = $columns[$sortColumnIndex] ?? 'invoice_number';
    //             $selectQuery .= " ORDER BY {$sortColumn} {$sortDirection}";

    //             // Pagination with bound integers
    //             $selectQuery .= " LIMIT :start, :length";
    //             $finalParameters = array_merge($countParameters, [
    //                 'start' => (int) $start,
    //                 'length' => (int) $length,
    //             ]);

    //             $items = DB::select($selectQuery, $finalParameters);

    //             return response()->json([
    //                 'draw' => $draw,
    //                 'recordsTotal' => $totalRecords,
    //                 'recordsFiltered' => $totalRecords,
    //                 'data' => $items,
    //                 'meta' => [
    //                     'allowed_years' => $allowedYears,
    //                     'minimum_date' => $minAllowedDateFormatted,
    //                     'subscription_type' => $isFree ? 'free' : 'premium',
    //                 ]
    //             ], 200);

    //         } else if ($user->isAdmin == 0) {
    //             $userId = $user->user_id;

    //             $selectQuery = "
    //             SELECT
    //                 billing.*, 
    //                 staff.name AS user_name 
    //             FROM 
    //                 {$table_name} AS billing
    //             LEFT JOIN 
    //                 shops ON billing.shop_id = shops.shop_id
    //             LEFT JOIN 
    //                 staff ON staff.addedBy = :shopId AND staff.user_id = :userId
    //             WHERE 
    //                 staff.addedBy = :shopId2 AND billing.user_id = staff.user_id
    //         ";

    //             $parameters = [
    //                 'shopId' => $shopId,
    //                 'userId' => $userId,
    //                 'shopId2' => $shopId,
    //             ];

    //             $searchParameters = [];

    //             // Apply subscription-based date limit
    //             $minAllowedDateStr = $minAllowedDate->format('Y-m-d H:i:s');
    //             $selectQuery .= " AND billing.created_at >= :minDate";
    //             $parameters['minDate'] = $minAllowedDateStr;

    //             if ($searchValue !== "") {
    //                 $searchConditions = [];
    //                 if ($filter_option == 'invoice_number') {
    //                     $searchConditions[] = "billing.invoice_number LIKE :searchValue";
    //                     $searchParameters['searchValue'] = '%' . $searchValue . '%';
    //                 } else if ($filter_option == 'date') {
    //                     $sv = str_replace('/', '-', $searchValue);
    //                     $svMySQL = date('Y-m-d', strtotime($sv));
    //                     $searchConditions[] = "DATE(billing.created_at) = :searchValue";
    //                     $searchParameters['searchValue'] = $svMySQL;
    //                 } else if ($filter_option == 'user') {
    //                     $searchConditions[] = "staff.name LIKE :searchValue";
    //                     $searchParameters['searchValue'] = '%' . $searchValue . '%';
    //                 } else if ($filter_option == 'total') {
    //                     if (is_numeric($searchValue)) {
    //                         $searchConditions[] = "billing.total_price = :searchValue";
    //                         $searchParameters['searchValue'] = $searchValue;
    //                     } else {
    //                         $searchConditions[] = "billing.total_price = -1";
    //                     }
    //                 }

    //                 if (!empty($searchConditions)) {
    //                     $selectQuery .= " AND " . implode(' AND ', $searchConditions);
    //                 }
    //             }

    //             // Date range binding
    //             if (!empty($dateFrom) && !empty($dateTo)) {
    //                 $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
    //                 $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
    //                 if ($dateFromFormatted && $dateToFormatted) {
    //                     $dateFromFormatted->setTime(0, 0, 0);
    //                     $dateToFormatted->setTime(23, 59, 59);
    //                     $dateFromStr = $dateFromFormatted->format('Y-m-d H:i:s');
    //                     $dateToStr = $dateToFormatted->format('Y-m-d H:i:s');

    //                     $selectQuery .= " AND billing.created_at BETWEEN :dateFrom AND :dateTo";
    //                     $parameters['dateFrom'] = $dateFromStr;
    //                     $parameters['dateTo'] = $dateToStr;
    //                 }
    //             }

    //             // Count
    //             $totalRecordsQuery = "SELECT COUNT(*) as count FROM ({$selectQuery}) AS countTable";
    //             $countParameters = array_merge($parameters, $searchParameters);
    //             $totalRecordsResult = DB::select($totalRecordsQuery, $countParameters);
    //             $totalRecords = $totalRecordsResult ? (int) $totalRecordsResult[0]->count : 0;

    //             // Sorting
    //             $columns = ['invoice_number', 'total_price', 'created_at'];
    //             $sortColumn = $columns[$sortColumnIndex] ?? 'invoice_number';
    //             $selectQuery .= " ORDER BY {$sortColumn} {$sortDirection}";

    //             // Pagination with bound integers
    //             $selectQuery .= " LIMIT :start, :length";
    //             $finalParameters = array_merge($countParameters, [
    //                 'start' => (int) $start,
    //                 'length' => (int) $length,
    //             ]);

    //             $items = DB::select($selectQuery, $finalParameters);

    //             return response()->json([
    //                 'draw' => $draw,
    //                 'recordsTotal' => $totalRecords,
    //                 'recordsFiltered' => $totalRecords,
    //                 'data' => $items,
    //                 'meta' => [
    //                     'allowed_years' => $allowedYears,
    //                     'minimum_date' => $minAllowedDateFormatted,
    //                     'subscription_type' => $isFree ? 'free' : 'premium',
    //                 ]
    //             ], 200);
    //         }
    //     } catch (\Exception $e) {
    //         report($e);
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => __('validation.unable_to_process'),
    //         ], 500);
    //     }

    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => __('validation.Unauthorized')
    //     ], 401);
    // }


    public function transactionReport(Request $request)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'failed',
                'message' => __('validation.Unauthorized')
            ], 401);
        }

        try {
            $validator = Validator::make($request->all(), [
                'draw' => ['nullable', 'integer', 'min:0'],
                'start' => ['nullable', 'integer', 'min:0'],
                'length' => ['nullable', 'integer', 'min:1', 'max:100'],
                'order.0.column' => ['nullable', 'integer', 'min:0'],
                'order.0.dir' => ['nullable', Rule::in(['asc', 'desc'])],
                'search.value' => ['nullable', 'string'],
                'filter_option' => ['nullable', Rule::in(['invoice_number', 'date', 'user', 'total'])],
                'date_from' => ['nullable', 'date_format:d/m/Y'],
                'date_to' => ['nullable', 'date_format:d/m/Y'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'draw' => (int) $request->input('draw', 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ], 200);
            }

            $draw = (int) $request->input('draw', 0);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $sortColumnIndex = (int) $request->input('order.0.column', 0);
            $sortDirection = $request->input('order.0.dir', 'desc');
            $searchValue = trim((string) $request->input('search.value', ''));
            $filterOption = $request->input('filter_option', 'invoice_number');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $user = Auth::guard('api')->user();
            $shopId = $user->isAdmin == 1 ? $user->shop->shop_id : $user->staff->addedBy;
            $userId = $user->user_id;

            $allowedYears = SubscriptionHelper::getReportDataYears($shopId, $user->module_type);
            $minAllowedDate = Carbon::now()->subYears($allowedYears)->startOfDay();
            $minAllowedDateFormatted = $minAllowedDate->format('d/m/Y');

            $shopSubscription = DB::connection($user->module_type)
                ->table('shop_subscriptions')
                ->where('shop_id', $shopId)
                ->orderByDesc('end_date')
                ->first();

            $subscriptionData = $shopSubscription
                ? SubscriptionHelper::subsriptionDataFormat($shopSubscription->subscription_data)
                : ['subscription_id' => config('subscription.free_subscription_id', 1)];

            $isFree = (int) $subscriptionData['subscription_id'] === (int) config('subscription.free_subscription_id', 1);

            if (empty($dateFrom) && empty($dateTo)) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'meta' => [
                        'allowed_years' => $allowedYears,
                        'minimum_date' => $minAllowedDateFormatted,
                        'subscription_type' => $isFree ? 'free' : 'premium',
                    ]
                ], 200);
            }

            if (empty($dateFrom) || empty($dateTo)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.date_range_both_required'),
                ], 400);
            }

            $dateFromObj = Carbon::createFromFormat('d/m/Y', $dateFrom)->startOfDay();
            $dateToObj = Carbon::createFromFormat('d/m/Y', $dateTo)->endOfDay();
            $today = Carbon::now()->endOfDay();

            if ($dateFromObj->gt($today) || $dateToObj->gt($today)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.Future dates are not allowed')
                ], 400);
            }

            if ($dateFromObj->gt($dateToObj)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.date_from_must_be_before_date_to')
                ], 400);
            }

            if ($dateFromObj->lt($minAllowedDate)) {
                $message = $isFree
                    ? __('validation.free_plan_year_restriction', [
                        'years' => $allowedYears,
                        'date' => $minAllowedDateFormatted
                    ])
                    : __('validation.paid_plan_year_restriction', [
                        'years' => $allowedYears,
                        'date' => $minAllowedDateFormatted
                    ]);

                return response()->json([
                    'status' => 'failed',
                    'message' => $message,
                    'data' => [
                        'allowed_years' => $allowedYears,
                        'minimum_date' => $minAllowedDateFormatted,
                        'subscription_type' => $isFree ? 'free' : 'premium',
                    ]
                ], 403);
            }

            $baseQuery = DB::table('billing')
                ->leftJoin('customers', 'billing.customer_id', '=', 'customers.customer_id')
                ->where('billing.shop_id', $shopId)
                ->where('billing.created_at', '>=', $minAllowedDate->format('Y-m-d H:i:s'));

            if ($user->isAdmin == 1) {
                $baseQuery
                    ->leftJoin('shops', 'billing.shop_id', '=', 'shops.shop_id')
                    ->leftJoin('staff', function ($join) use ($shopId) {
                        $join->on('billing.user_id', '=', 'staff.user_id')
                            ->where('staff.addedBy', '=', $shopId);
                    })
                    ->select([
                        'billing.id',
                        'billing.shop_id',
                        'billing.user_id',
                        'billing.customer_id',
                        'billing.invoice_number',
                        'billing.invoice_count',
                        'billing.item_list',
                        'billing.total_price',
                        'billing.payment_status',
                        'billing.created_at',
                        'billing.updated_at',
                        'customers.name as customer_name',
                        'customers.mobile as customer_mobile',
                        DB::raw('COALESCE(staff.name, shops.name) as user_name'),
                    ]);
            } else {
                $baseQuery
                    ->leftJoin('staff', function ($join) use ($shopId) {
                        $join->on('billing.user_id', '=', 'staff.user_id')
                            ->where('staff.addedBy', '=', $shopId);
                    })
                    ->where('billing.user_id', $userId)
                    ->select([
                        'billing.id',
                        'billing.shop_id',
                        'billing.user_id',
                        'billing.customer_id',
                        'billing.invoice_number',
                        'billing.invoice_count',
                        'billing.item_list',
                        'billing.total_price',
                        'billing.payment_status',
                        'billing.created_at',
                        'billing.updated_at',
                        'customers.name as customer_name',
                        'customers.mobile as customer_mobile',
                        'staff.name as user_name',
                    ]);
            }

            $totalRecords = (clone $baseQuery)->count('billing.id');

            $filteredQuery = clone $baseQuery;

            $filteredQuery->whereBetween('billing.created_at', [
                $dateFromObj->format('Y-m-d H:i:s'),
                $dateToObj->format('Y-m-d H:i:s')
            ]);

            if ($searchValue !== '') {
                if ($filterOption === 'invoice_number') {
                    $raw = $searchValue;
                    // $normalized = preg_replace('/[^A-Za-z0-9]/', '', $searchValue);
                    $normalized = preg_replace('/\s+/', '', $searchValue);

                    $filteredQuery->where(function ($q) use ($raw, $normalized) {
                        $q->where('billing.invoice_number', 'LIKE', '%' . $raw . '%');

                        if ($normalized !== '') {
                            // $q->orWhereRaw(
                            //     'REPLACE(REPLACE(REPLACE(LOWER(billing.invoice_number), " ", ""), "-", ""), "/", "") LIKE ?',
                            //     ['%' . strtolower($normalized) . '%']
                            // );
                            
                            $q->orWhereRaw('REPLACE(billing.invoice_number, " ", "") LIKE ?', ['%' . $normalized . '%']);

                        }
                    });
                } 
                
                // elseif ($filterOption === 'date') {
                //     $value = trim($searchValue);
                //     $value = preg_replace('/\s+/', '', $value);
                //     $value = str_replace(['-', '.'], '/', $value);
                //     $value = rtrim($value, '/');

                //     $searchDate = null;
                //     $now = Carbon::now();

                //     if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2}$/', $value)) {
                //         $searchDate = Carbon::createFromFormat('j/n/y', $value);
                //     } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                //         $searchDate = Carbon::createFromFormat('j/n/Y', $value);
                //     } elseif (preg_match('/^\d{1,2}\/\d{1,2}$/', $value)) {
                //         $searchDate = Carbon::createFromFormat('j/n/Y', $value . '/' . $now->year);
                //     } elseif (preg_match('/^\d{1,2}$/', $value)) {
                //         $searchDate = Carbon::createFromFormat('j/n/Y', $value . '/' . $now->month . '/' . $now->year);
                //     }

                //     if ($searchDate) {
                //         $filteredQuery->whereBetween('billing.created_at', [
                //             $searchDate->copy()->startOfDay()->format('Y-m-d H:i:s'),
                //             $searchDate->copy()->endOfDay()->format('Y-m-d H:i:s'),
                //         ]);
                //     } else {
                //         $filteredQuery->whereRaw('1 = 0');
                //     }
                // } 

                elseif ($filterOption === 'date') {
                    $value = trim($searchValue);
                    $value = preg_replace('/\s+/', '', $value);
                    $value = str_replace(['-', '.'], '/', $value);
                    $value = trim($value, '/');

                    if ($value === '') {
                        $filteredQuery->whereRaw('1 = 0');
                    } else {
                        $parts = explode('/', $value);
                        $count = count($parts);

                        if ($count === 1 && preg_match('/^\d{1,2}$/', $parts[0])) {
                            // 16
                            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);

                            $filteredQuery->whereRaw(
                                "DATE_FORMAT(billing.created_at, '%d/%m/%Y') LIKE ?",
                                [$day . '/%']
                            );
                        } elseif (
                            $count === 2 &&
                            preg_match('/^\d{1,2}$/', $parts[0]) &&
                            preg_match('/^\d{1,2}$/', $parts[1])
                        ) {
                            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                            $month = $parts[1];

                            if (strlen($month) === 1) {
                                // partial month search: 16/0 should match 16/01 ... 16/09
                                $filteredQuery->whereRaw(
                                    "DATE_FORMAT(billing.created_at, '%d/%m/%Y') LIKE ?",
                                    [$day . '/' . $month . '%']
                                );
                            } else {
                                // exact month search: 16/05 should match 16/05/....
                                $filteredQuery->whereRaw(
                                    "DATE_FORMAT(billing.created_at, '%d/%m/%Y') LIKE ?",
                                    [$day . '/' . $month . '/%']
                                );
                            }
                        }
                        elseif (
                            $count === 3 &&
                            preg_match('/^\d{1,2}$/', $parts[0]) &&
                            preg_match('/^\d{1,2}$/', $parts[1]) &&
                            preg_match('/^\d{1,4}$/', $parts[2])
                        ) {
                            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                            $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                            $year = $parts[2];

                            if (strlen($year) === 4) {
                                // 16/05/2026 => exact full date
                                $normalized = $day . '/' . $month . '/' . $year;
                                $searchDate = Carbon::createFromFormat('!d/m/Y', $normalized);

                                if ($searchDate && $searchDate->format('d/m/Y') === $normalized) {
                                    $filteredQuery->whereBetween('billing.created_at', [
                                        $searchDate->copy()->startOfDay()->format('Y-m-d H:i:s'),
                                        $searchDate->copy()->endOfDay()->format('Y-m-d H:i:s'),
                                    ]);
                                } else {
                                    $filteredQuery->whereRaw('1 = 0');
                                }
                            } elseif (strlen($year) === 3) {
                                // 16/05/202 => matches 16/05/2020-2029
                                $filteredQuery->whereRaw(
                                    "DATE_FORMAT(billing.created_at, '%d/%m/%Y') LIKE ?",
                                    [$day . '/' . $month . '/' . $year . '%']
                                );
                            } elseif (strlen($year) === 2) {
                                // 16/05/20 or 16/05/26
                                $filteredQuery->where(function ($query) use ($day, $month, $year) {
                                    $query->whereRaw(
                                        "DATE_FORMAT(billing.created_at, '%d/%m/%Y') LIKE ?",
                                        [$day . '/' . $month . '/' . $year . '%']
                                    )->orWhereRaw(
                                            "DATE_FORMAT(billing.created_at, '%d/%m') = ? AND RIGHT(DATE_FORMAT(billing.created_at, '%Y'), 2) = ?",
                                            [$day . '/' . $month, $year]
                                        );
                                });
                            } else {
                                // 16/05/2 => matches 16/05/2...
                                $filteredQuery->whereRaw(
                                    "DATE_FORMAT(billing.created_at, '%d/%m/%Y') LIKE ?",
                                    [$day . '/' . $month . '/' . $year . '%']
                                );
                            }
                        } else {
                            $filteredQuery->whereRaw('1 = 0');
                        }
                    }
                }
                
                elseif ($filterOption === 'user') {
                    if ($user->isAdmin == 1) {
                        $filteredQuery->where(function ($q) use ($searchValue) {
                            $q->where('staff.name', 'LIKE', '%' . $searchValue . '%')
                                ->orWhere('shops.name', 'LIKE', '%' . $searchValue . '%');
                        });
                    } else {
                        $filteredQuery->where('staff.name', 'LIKE', '%' . $searchValue . '%');
                    }
                } elseif ($filterOption === 'total') {
                    if (is_numeric($searchValue)) {
                        $filteredQuery->where('billing.total_price', $searchValue);
                    } else {
                        $filteredQuery->whereRaw('1 = 0');
                    }
                }
            }

            $recordsFiltered = (clone $filteredQuery)->count('billing.id');

            $columns = [
                0 => 'billing.invoice_number',
                1 => 'billing.total_price',
                2 => 'billing.created_at',
            ];

            $sortColumn = $columns[$sortColumnIndex] ?? 'billing.created_at';

            $items = $filteredQuery
                ->orderBy($sortColumn, $sortDirection)
                ->offset($start)
                ->limit($length)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'shop_id' => $item->shop_id,
                        'user_id' => $item->user_id,
                        'customer_id' => $item->customer_id,
                        'invoice_number' => $item->invoice_number,
                        'invoice_count' => $item->invoice_count,
                        'item_list' => $item->item_list,
                        'total_price' => $item->total_price,
                        'payment_status' => $item->payment_status,
                        'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d H:i:s'),
                        'user_name' => $item->user_name,
                        'customer_name' => $item->customer_name ?? null,
                        'customer_mobile' => $item->customer_mobile ?? null,
                    ];
                });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $items,
                'meta' => [
                    'allowed_years' => $allowedYears,
                    'minimum_date' => $minAllowedDateFormatted,
                    'subscription_type' => $isFree ? 'free' : 'premium',
                ]
            ], 200);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'failed',
                'message' => __('validation.unable_to_process'),
            ], 500);
        }
    }

    public function requestReport(Request $request)
    {
        if (Auth::guard('api')->check()) {
            try {
                $user = Auth::guard('api')->user();
                $shopId = $user->isAdmin ? $user->shop->shop_id : $user->staff->addedBy;

                // Validate inputs
                $validator = Validator::make($request->all(), [
                    'date_from' => 'required|date_format:d/m/Y',
                    'date_to' => 'required|date_format:d/m/Y',
                    'report_type' => 'required|in:pdf,excel,csv',
                    'filter_option' => 'nullable|string',
                    'search_value' => 'nullable|string',
                ], [
                    'date_from.required' => __('report_validation.required', ['attribute' => __('report_validation.attributes.date_from')]),
                    'date_from.date_format' => __('report_validation.date_format', ['attribute' => __('report_validation.attributes.date_from'), 'format' => 'd/m/Y']),
                    'date_to.required' => __('report_validation.required', ['attribute' => __('report_validation.attributes.date_to')]),
                    'date_to.date_format' => __('report_validation.date_format', ['attribute' => __('report_validation.attributes.date_to'), 'format' => 'd/m/Y']),
                    'report_type.required' => __('report_validation.required', ['attribute' => __('report_validation.attributes.report_type')]),
                    'report_type.in' => __('report_validation.invalid', ['attribute' => __('report_validation.attributes.report_type')]),
                    'filter_option.string' => __('report_validation.string', ['attribute' => __('report_validation.attributes.filter_option')]),
                    'search_value.string' => __('report_validation.string', ['attribute' => __('report_validation.attributes.search_value')]),
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $validator->errors()->first()
                    ], 400);
                }

                $dateFrom = $request->input('date_from');
                $dateTo = $request->input('date_to');
                $filterOption = $request->input('filter_option');
                $searchValue = $request->input('search_value');
                $reportType = $request->input('report_type');

                if (!empty($dateFrom) && !empty($dateTo)) {
                    $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
                    $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
                    $currentDate = new DateTime();

                    if (!$dateFromFormatted || !$dateToFormatted) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Invalid date format. Use dd/mm/yyyy')
                        ], 400);
                    }

                    if ($dateFromFormatted > $currentDate || $dateToFormatted > $currentDate) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Future dates are not allowed')
                        ], 400);
                    }

                    $interval = $dateFromFormatted->diff($dateToFormatted);
                    $months = ($interval->y * 12) + $interval->m;
                    if ($interval->d > 0) {
                        $months += 1;
                    }
                    if ($months > 6) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => __('validation.Date range cannot exceed 6 months')
                        ], 400);
                    }
                }

                $checkItemCount = BillingHelpher::checkDataISPresentBetweenDates(
                    $user->user_id,
                    $dateFrom,
                    $dateTo
                );

                if ($checkItemCount == 0) {
                    return response()->json([
                        'status' => 'empty',
                        'message' => __('validation.No transactions found for the selected date range. Please choose a different one')
                    ], 400);
                }

                // CHECK REQUEST IS IN PROCESSING OR NOT
                $checkReport = ReportGeneration::where('user_id', $user->user_id)
                    ->whereIn('status', [0, 1])
                    ->count();

                if ($checkReport > 0) {
                    return response()->json([
                        'status' => 'queue',
                        'message' => __('validation.A previous Report request is in queue, please wait for completion')
                    ], 200);
                }

                // If there is previous generated report found then delete the record
                $previousReport = ReportGeneration::where('user_id', $user->user_id)
                    ->where('status', 2)
                    ->first();

                if ($previousReport) {
                    BillingHelpher::deleteReportRecord($previousReport->id);
                }

                // Store report request
                $report = new ReportGeneration();
                $report->user_id = $user->user_id; // Fixed: was $user->id first, then $user->user_id
                $report->shop_id = $shopId;
                $report->environment = env('APP_ENVIRONMENT', 'local');
                $report->parameters = json_encode([
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'filter_option' => $filterOption,
                    'search_value' => $searchValue, // Uncommented as it may be needed
                ]);
                $report->status = 0; // Requested
                $report->report_type = $reportType;
                $report->save();

                // Dispatch job immediately
                GenerateReportJob::dispatch($report->id)
                    ->onQueue(env('APP_ENVIRONMENT') . '_reports');

                return response()->json([
                    'status' => 'success',
                    'message' => __('validation.Report generation requested. You will be notified once it is ready')
                ], 200);

            } catch (\Exception $e) {
                \Log::error('Report generation error: ' . $e->getMessage(), [
                    'user_id' => $user->user_id ?? null,
                    'trace' => $e->getTraceAsString()
                ]);
                report($e);
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.An error occurred while processing your request')
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function reInititateFailedReport($report_id)
    {
        if (Auth::guard('api')->check()) {
            try {

                $user = Auth::guard('api')->user();
                $shopId = $user->isAdmin ? $user->shop->shop_id : $user->staff->addedBy;

                // Validate report_id
                $validator = Validator::make(['report_id' => $report_id], [
                    'report_id' => 'required|exists:report_generations,id',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $validator->errors()->first()
                    ], 400);
                }

                // CHECK REQUEST IS IN PROCESSING OR NOT
                $checkReport = ReportGeneration::where('user_id', $user->user_id)
                    ->whereIn('status', [0, 1])->count();

                if ($checkReport > 0) {
                    return response()->json([
                        'status' => 'queue',
                        'message' => __('validation.A previous Report request is in queue, please wait for completion')
                    ], 200);
                }
                // CHECK REQUEST IS IN PROCESSING OR NOT


                // If there is previous generated report found then delete the record
                $previousReport = ReportGeneration::where('user_id', $user->user_id)
                    ->where('status', 2)->first();

                if ($previousReport) {
                    BillingHelpher::deleteReportRecord($previousReport->id);
                }


                // If there is previous generated report found then delete the record

                // Store report request
                $report = ReportGeneration::find($report_id);
                $report->status = 0;
                $report->retry_count = 0;
                $report->save();

                // Dispatch job immediately
                // GenerateReportJob::dispatch($report->id)->onQueue('reports');
                GenerateReportJob::dispatch($report->id)
                    ->onQueue(env('APP_ENVIRONMENT') . '_reports');

                return response()->json([
                    'status' => 'success',
                    'message' => __('validation.Report re-generation requested. You will be notified once it is ready')
                ], 200);
            } catch (\Exception $e) {
                report($e);
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process')
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function generateReport($reportId)
    {
        $report = ReportGeneration::find($reportId);

        if (!$report || $report->status != 1) {
            return false; // Invalid report or not in requested status
        }


        $disk = Storage::disk('public');
        $reportDir = 'reports';

        if (!$disk->exists($reportDir)) {
            $disk->makeDirectory($reportDir);
        }

        // // Update status to processing
        // $report->status = 1;
        // $report->save();

        $parameters = json_decode($report->parameters, true);
        $dateFrom = $parameters['date_from'] ?? null;
        $dateTo = $parameters['date_to'] ?? null;
        $filterOption = $parameters['filter_option'] ?? 'invoice_number';
        $searchValue = $parameters['search_value'] ?? '';

        $user = User::find($report->user_id);
        if (!$user) {
            $report->status = 3;
            $report->save();
            return false;
        }
        $shopId = $user->isAdmin ? $user->shop->shop_id : $user->staff->addedBy;

        // ✅ SET THE LOCALE BASED ON USER'S PREFERENCE
        $userLocale = $user->lang ?? 'en'; // Adjust based on your user locale field
        App::setLocale($userLocale);


        // Switch to the shop type connection
        DB::setDefaultConnection($user->module_type);

        $table_name = 'billing';

        // Build query
        $selectQuery = $user->isAdmin ?
            "SELECT billing.*, COALESCE(staff.name, shops.name) AS user_name
         FROM $table_name AS billing
         LEFT JOIN shops ON billing.shop_id = shops.shop_id
         LEFT JOIN staff ON billing.user_id = staff.user_id AND staff.addedBy = :shopId1
         WHERE billing.shop_id = :shopId2" :
            "SELECT billing.*, staff.name AS user_name
         FROM $table_name AS billing
         LEFT JOIN shops ON billing.shop_id = shops.shop_id
         LEFT JOIN staff ON staff.addedBy = :shopId AND staff.user_id = :userId
         WHERE staff.addedBy = :shopId2 AND billing.user_id = staff.user_id";

        $parameters = $user->isAdmin ? [
            'shopId1' => $shopId,
            'shopId2' => $shopId
        ] : [
            'shopId' => $shopId,
            'userId' => $user->id,
            'shopId2' => $shopId
        ];

        $searchParameters = [];
        if ($searchValue !== "") {
            $searchConditions = [];
            if ($filterOption == 'invoice_number') {
                $searchConditions[] = "billing.invoice_number LIKE :searchValue";
                $searchParameters['searchValue'] = '%' . $searchValue . '%';
            } else if ($filterOption == 'date') {
                $searchValue = str_replace('/', '-', $searchValue);
                $searchValueMySQLFormat = date('Y-m-d', strtotime($searchValue));
                $searchConditions[] = "DATE(billing.created_at) = :searchValue";
                $searchParameters['searchValue'] = $searchValueMySQLFormat;
            } else if ($filterOption == 'user') {
                $searchConditions[] = ($user->isAdmin ? "COALESCE(staff.name, shops.name)" : "staff.name") . " LIKE :searchValue";
                $searchParameters['searchValue'] = '%' . $searchValue . '%';
            } else if ($filterOption == 'total') {
                if (is_numeric($searchValue)) {
                    $searchConditions[] = "billing.total_price = :searchValue";
                    $searchParameters['searchValue'] = $searchValue;
                } else {
                    $searchConditions[] = "billing.total_price = -1";
                }
            }
            if (!empty($searchConditions)) {
                $selectQuery .= " AND " . implode(' AND ', $searchConditions);
            }
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
            $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
            if ($dateFromFormatted && $dateToFormatted) {
                $dateFromFormatted->setTime(0, 0, 0);
                $dateToFormatted->setTime(23, 59, 59);
                $dateFromStr = $dateFromFormatted->format('Y-m-d H:i:s');
                $dateToStr = $dateToFormatted->format('Y-m-d H:i:s');
                // $selectQuery .= " AND billing.created_at BETWEEN '$dateFromStr' AND '$dateToStr'";
                $selectQuery .= " AND billing.created_at BETWEEN :dateFrom AND :dateTo";
                $searchParameters['dateFrom'] = $dateFromStr;
                $searchParameters['dateTo'] = $dateToStr;
            }
        }

        $selectQuery .= " ORDER BY created_at DESC";
        // $items = DB::select(DB::raw($selectQuery), array_merge($parameters, $searchParameters));
        $items = DB::select($selectQuery, array_merge($parameters, $searchParameters));

        // \Log::error('Report generation items:' , [
        //     'items' => $items ?? null,
        // ]);


        // // Generate report based on type
        // $fileName = 'transaction_report_' . time() . '.' . ($report->report_type == 'pdf' ? 'pdf' : ($report->report_type == 'excel' ? 'xlsx' : 'csv'));
        // $filePath = 'public/reports/' . $fileName;


        $extension = $report->report_type == 'pdf' ? 'pdf' : ($report->report_type == 'excel' ? 'xlsx' : 'csv');
        $fileName = 'transaction_report_' . $report->id . '_' . time() . '.' . $extension;
        $relativePath = 'reports/' . $fileName;
        $absolutePath = $disk->path($relativePath);

        $country_details = json_decode($user->country_details);
        $currency = $country_details->currency_symbol ?? '₹';
        $decimal_separator = $country_details->decimal_separator ?? '.';

        if ($report->report_type == 'pdf') {
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('dpi', 150);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isPhpEnabled', true);

            $pdf = new \Dompdf\Dompdf($options);
            $html = view('coreweb::GroceryIndia.reports.transaction_report', [
                'items' => $items,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'business_name' => ucwords($user->shop->business_name),
                'address' => $user->shop->address,
                'current_date' => now()->format('d/m/Y'),
                'currency' => $currency,
                'decimal_separator' => $decimal_separator,
            ])->render();
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            // Storage::put($filePath, $pdf->output());

            $pdfOutput = $pdf->output();

            $written = $disk->put($relativePath, $pdfOutput);

            if (!$written || !$disk->exists($relativePath) || $disk->size($relativePath) === 0) {
                Log::error('PDF report file was not written', [
                    'report_id' => $report->id,
                    'relative_path' => $relativePath,
                ]);
                return false;
            }

        } elseif ($report->report_type == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header section
            $sheet->setCellValue('A1', __('profile_page.Business Name') . ': ' . ucwords($user->shop->business_name));
            $sheet->setCellValue('A2', __('common.Address') . ': ' . $user->shop->address);
            $sheet->setCellValue('A3', __('transaction_report_page.Transaction Report'));
            // $sheet->setCellValue('A4', ($dateFrom && $dateTo) ? `__('common.From'): $dateFrom __('common.To'): $dateTo` : __('transaction_report_page.All Transactions'));
            $sheet->setCellValue(
                'A4',
                ($dateFrom && $dateTo)
                ? __('common.From') . ": $dateFrom " . __('common.To') . ": $dateTo"
                : __('transaction_report_page.All Transactions')
            );

            $sheet->setCellValue('A5', __('transaction_report_page.Report Generated') . ': ' . now()->format('d/m/Y'));
            $sheet->mergeCells('A1:N1');
            $sheet->mergeCells('A2:N2');
            $sheet->mergeCells('A3:N3');
            $sheet->mergeCells('A4:N4');
            $sheet->mergeCells('A5:N5');

            $row = 7;

            // Calculate monthly totals
            $monthlyTotals = [];
            foreach ($items as $item) {
                $date = \Carbon\Carbon::parse($item->created_at);
                $monthYear = $date->format('n-Y');
                if (!isset($monthlyTotals[$monthYear])) {
                    $monthlyTotals[$monthYear] = 0;
                }
                $monthlyTotals[$monthYear] += $item->total_price;
            }

            $lastMonthYear = null;
            foreach ($items as $item) {
                $date = \Carbon\Carbon::parse($item->created_at);
                $currentMonthYear = $date->format('n-Y');
                $monthName = strtoupper($date->format('F'));

                $payment_status = 'Unpaid';
                // payment status
                if ($item->payment_status == 1) {
                    $payment_status = 'Paid';
                }


                if ($lastMonthYear !== $currentMonthYear) {
                    if ($lastMonthYear !== null) {
                        $row++;
                    }
                    $sheet->setCellValue('A' . $row, "$monthName, {$date->year}");
                    $total = $monthlyTotals[$currentMonthYear];
                    // $sheet->setCellValue('C' . $row, 'Total: ' . ($total < 0 ? "-{$currency}" : "{$currency}") . number_format(abs($total), 2, $decimal_separator, '.'));
                    $sheet->setCellValue('C' . $row, __('common.Total') . " ({$currency}) : " . ($total < 0 ? "-" : "") . number_format(abs($total), 2, $decimal_separator, '.'));
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $sheet->mergeCells('C' . $row . ':N' . $row);
                    $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->setBold(true);
                    $row++;
                    $sheet->setCellValue('A' . $row, __('common.Invoice') . ' #');
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $sheet->setCellValue('C' . $row, __('common.Products'));
                    $sheet->mergeCells('C' . $row . ':I' . $row);
                    $sheet->setCellValue('J' . $row, __('common.Total') . " ({$currency}) ");
                    $sheet->setCellValue('K' . $row, __('common.Payment Status'));
                    $sheet->setCellValue('L' . $row, __('common.User'));
                    $sheet->mergeCells('L' . $row . ':M' . $row);
                    $sheet->setCellValue('N' . $row, __('common.Date'));
                    $sheet->mergeCells('N' . $row . ':O' . $row);
                    $sheet->getStyle('A' . $row . ':O' . $row)->getFont()->setBold(true);
                    $row++;
                    $lastMonthYear = $currentMonthYear;
                }

                $itemList = json_decode($item->item_list, true) ?: [];
                $products = array_map(function ($i) {
                    return "{$i['itemName']} {$i['quantity']} {$i['selectedUnit']}";
                }, $itemList);
                $productsString = implode(', ', $products);

                // Split long product lists
                $maxLength = 100;
                if (strlen($productsString) > $maxLength) {
                    $words = explode(', ', $productsString);
                    $currentLine = '';
                    $lines = [];
                    foreach ($words as $word) {
                        if (strlen($currentLine . $word) <= $maxLength) {
                            $currentLine .= ($currentLine ? ', ' : '') . $word;
                        } else {
                            $lines[] = $currentLine;
                            $currentLine = $word;
                        }
                    }
                    if ($currentLine) {
                        $lines[] = $currentLine;
                    }
                } else {
                    $lines = [$productsString];
                }

                // Write first row of transaction
                $sheet->setCellValue('A' . $row, $item->invoice_number);
                $sheet->mergeCells('A' . $row . ':B' . $row);
                $sheet->setCellValue('C' . $row, $lines[0]);
                $sheet->mergeCells('C' . $row . ':I' . $row);
                // $sheet->setCellValue('J' . $row, ($item->total_price < 0 ? "-{$currency}" : "{$currency}") . number_format(abs($item->total_price), 2, $decimal_separator, '.'));
                $sheet->setCellValue('J' . $row, ($item->total_price < 0 ? "-" : "") . number_format(abs($item->total_price), 2, $decimal_separator, '.'));
                $sheet->setCellValue('K' . $row, $payment_status);
                $sheet->setCellValue('L' . $row, $item->user_name);
                $sheet->setCellValue('N' . $row, \Carbon\Carbon::parse($item->created_at)->format('d/m/Y h:i:s'));
                $sheet->mergeCells('N' . $row . ':O' . $row);
                $row++;

                // Write additional rows for products
                for ($i = 1; $i < count($lines); $i++) {
                    $sheet->setCellValue('C' . $row, $lines[$i]);
                    $sheet->mergeCells('C' . $row . ':I' . $row);
                    $row++;
                }
            }

            if (empty($items)) {
                $sheet->setCellValue('A' . $row, __('transaction_report_page.No transactions found for the selected period'));
                $sheet->mergeCells('A' . $row . ':N' . $row);
            }

            // Styling for Excel
            $sheet->getStyle('A1:N5')->getFont()->setBold(true);
            $sheet->getStyle('A1:N' . ($row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('C1:I' . ($row - 1))->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(10);
            for ($col = 'C'; $col <= 'I'; $col++) {
                $sheet->getColumnDimension($col)->setWidth(15);
            }
            $sheet->getColumnDimension('J')->setWidth(12);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(10);
            $sheet->getColumnDimension('M')->setWidth(10);
            $sheet->getColumnDimension('N')->setWidth(10);

            // $tempPath = storage_path('app/' . $filePath);
            // if (!is_dir(dirname($tempPath))) {
            //     mkdir(dirname($tempPath), 0755, true);
            // }

            // $writer = new Xlsx($spreadsheet);
            // // $writer->save(storage_path('app/' . $filePath));
            // $writer->save($tempPath);

            // @chmod($tempPath, 0644);
            // Storage::setVisibility($filePath, 'public');

            $writer = new Xlsx($spreadsheet);
            $writer->save($absolutePath);

            clearstatcache(true, $absolutePath);

            if (!file_exists($absolutePath) || filesize($absolutePath) === 0) {
                Log::error('Excel report file was not written', [
                    'report_id' => $report->id,
                    'absolute_path' => $absolutePath,
                    'relative_path' => $relativePath,
                ]);
                return false;
            }

            $disk->setVisibility($relativePath, 'public');

        } elseif ($report->report_type == 'csv') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header section
            $sheet->setCellValue('A1', __('profile_page.Business Name') . ': ' . ucwords($user->shop->business_name));
            $sheet->setCellValue('A2', __('common.Address') . ': ' . $user->shop->address);
            $sheet->setCellValue('A3', __('transaction_report_page.Transaction Report'));
            // $sheet->setCellValue('A4', ($dateFrom && $dateTo) ? "From: $dateFrom To: $dateTo" : __('transaction_report_page.All Transactions'));
            $sheet->setCellValue(
                'A4',
                ($dateFrom && $dateTo)
                ? __('common.From') . ": $dateFrom " . __('common.To') . ": $dateTo"
                : __('transaction_report_page.All Transactions')
            );

            $sheet->setCellValue('A5', __('transaction_report_page.Report Generated') . ': ' . now()->format('d/m/Y'));
            $sheet->mergeCells('A1:N1');
            $sheet->mergeCells('A2:N2');
            $sheet->mergeCells('A3:N3');
            $sheet->mergeCells('A4:N4');
            $sheet->mergeCells('A5:N5');

            $row = 7;

            // Calculate monthly totals
            $monthlyTotals = [];
            foreach ($items as $item) {
                $date = \Carbon\Carbon::parse($item->created_at);
                $monthYear = $date->format('n-Y');
                if (!isset($monthlyTotals[$monthYear])) {
                    $monthlyTotals[$monthYear] = 0;
                }
                $monthlyTotals[$monthYear] += $item->total_price;
            }

            $lastMonthYear = null;
            foreach ($items as $item) {
                $date = \Carbon\Carbon::parse($item->created_at);
                $currentMonthYear = $date->format('n-Y');
                $monthName = strtoupper($date->format('F'));


                $payment_status = 'Unpaid';
                // payment status
                if ($item->payment_status == 1) {
                    $payment_status = 'Paid';
                }

                if ($lastMonthYear !== $currentMonthYear) {
                    if ($lastMonthYear !== null) {
                        $row++;
                    }
                    $sheet->setCellValue('A' . $row, "$monthName, {$date->year}");
                    $total = $monthlyTotals[$currentMonthYear];
                    // $sheet->setCellValue('C' . $row, 'Total: ' . ($total < 0 ? "-{$currency}" : "{$currency}") . number_format(abs($total), 2, $decimal_separator, '.'));
                    $sheet->setCellValue('C' . $row, __('common.Total') . " ({$currency}) :  " . ($total < 0 ? "-" : "") . number_format(abs($total), 2, $decimal_separator, '.'));
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $sheet->mergeCells('C' . $row . ':N' . $row);
                    $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->setBold(true);
                    $row++;
                    $sheet->setCellValue('A' . $row, __('common.Invoice') . ' #');
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $sheet->setCellValue('C' . $row, __('common.Products'));
                    $sheet->mergeCells('C' . $row . ':I' . $row);
                    $sheet->setCellValue('J' . $row, __('common.Total') . ' (' . $currency . ')');
                    $sheet->setCellValue('K' . $row, __('common.Payment Status'));
                    $sheet->setCellValue('L' . $row, __('common.User'));
                    $sheet->setCellValue('N' . $row, __('common.Date'));
                    $sheet->mergeCells('N' . $row . ':O' . $row);
                    $sheet->getStyle('A' . $row . ':O' . $row)->getFont()->setBold(true);
                    $row++;
                    $lastMonthYear = $currentMonthYear;
                }

                $itemList = json_decode($item->item_list, true) ?: [];
                $products = array_map(function ($i) {
                    return "{$i['itemName']} {$i['quantity']} {$i['selectedUnit']}";
                }, $itemList);
                $productsString = implode(', ', $products);

                // Split long product lists
                $maxLength = 100;
                if (strlen($productsString) > $maxLength) {
                    $words = explode(', ', $productsString);
                    $currentLine = '';
                    $lines = [];
                    foreach ($words as $word) {
                        if (strlen($currentLine . $word) <= $maxLength) {
                            $currentLine .= ($currentLine ? ', ' : '') . $word;
                        } else {
                            $lines[] = $currentLine;
                            $currentLine = $word;
                        }
                    }
                    if ($currentLine) {
                        $lines[] = $currentLine;
                    }
                } else {
                    $lines = [$productsString];
                }


                // Write first row of transaction
                $sheet->setCellValue('A' . $row, $item->invoice_number);
                $sheet->mergeCells('A' . $row . ':B' . $row);
                $sheet->setCellValue('C' . $row, $lines[0]);
                $sheet->mergeCells('C' . $row . ':I' . $row);
                // $sheet->setCellValue('J' . $row, ($item->total_price < 0 ? "-{$currency}" : "{$currency}") . number_format(abs($item->total_price), 2, $decimal_separator, '.'));
                $sheet->setCellValue('J' . $row, ($item->total_price < 0 ? "-" : "") . number_format(abs($item->total_price), 2, $decimal_separator, '.'));
                $sheet->setCellValue('K' . $row, $payment_status);
                $sheet->setCellValue('L' . $row, $item->user_name);
                $sheet->setCellValue('N' . $row, \Carbon\Carbon::parse($item->created_at)->format('d/m/Y h:i:s'));
                $sheet->mergeCells('N' . $row . ':O' . $row);
                $row++;

                // Write additional rows for products
                for ($i = 1; $i < count($lines); $i++) {
                    $sheet->setCellValue('C' . $row, $lines[$i]);
                    $sheet->mergeCells('C' . $row . ':I' . $row);
                    $row++;
                }
            }

            if (empty($items)) {
                $sheet->setCellValue('A' . $row, __('transaction_report_page.No transactions found for the selected period'));
                $sheet->mergeCells('A1:N1');
            }

            // $tempPath = storage_path('app/' . $filePath);
            // if (!is_dir(dirname($tempPath))) {
            //     mkdir(dirname($tempPath), 0755, true);
            // }

            // $writer = new Csv($spreadsheet);
            // $writer->setUseBOM(true);
            // // $writer->save(storage_path('app/' . $filePath));
            // $writer->save($tempPath);

            // @chmod($tempPath, 0644);
            // Storage::setVisibility($filePath, 'public');

            $writer = new Csv($spreadsheet);
            $writer->setUseBOM(true);
            $writer->save($absolutePath);

            clearstatcache(true, $absolutePath);

            if (!file_exists($absolutePath) || filesize($absolutePath) === 0) {
                Log::error('CSV report file was not written', [
                    'report_id' => $report->id,
                    'absolute_path' => $absolutePath,
                    'relative_path' => $relativePath,
                ]);
                return false;
            }

            $disk->setVisibility($relativePath, 'public');
        }

        // // Update report status and file path
        // $report->status = 2;
        // $report->file_path = $fileName;
        // $report->save();

        // return true;

        if (!$disk->exists($relativePath)) {
            Log::error('Report marked complete but file missing', [
                'report_id' => $report->id,
                'relative_path' => $relativePath,
                'absolute_path' => $absolutePath,
            ]);
            return false;
        }

        $report->file_path = $fileName;
        $report->save();

        return $fileName;

    }

    public function getReports(Request $request)
    {
        if (Auth::guard('api')->check()) {
            try {
                $user = Auth::guard('api')->user();
                $shopId = $user->isAdmin ? $user->shop->shop_id : $user->staff->addedBy;

                // DataTables parameters
                $draw = $request->input('draw', 0);
                $start = $request->input('start', 0);
                $length = $request->input('length', 1);
                $sortColumnIndex = $request->input('order.0.column', 0);
                $sortDirection = $request->input('order.0.dir', 'asc');
                $searchValue = $request->input('search.value', '');

                // Base query
                $query = ReportGeneration::select('id', 'report_type', 'parameters', 'created_at', 'status')
                    ->where('shop_id', $shopId);
                // ->where('status', 2); // Only ready for download

                // Total records
                $totalRecords = $query->count();

                // Apply search
                if (!empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('report_type', 'like', '%' . $searchValue . '%')
                            ->orWhere('parameters', 'like', '%' . $searchValue . '%')
                            ->orWhere('created_at', 'like', '%' . $searchValue . '%');
                    });
                }

                // Filtered records count
                $filteredRecords = $query->count();

                // Apply sorting
                $columns = ['id', 'report_type', 'parameters', 'created_at'];
                $sortColumn = $columns[$sortColumnIndex] ?? 'created_at';
                $query->orderBy($sortColumn, $sortDirection);

                // Apply pagination
                $reports = $query->skip($start)->take($length)->get();

                // Format data for DataTables

                $slno = 1;

                // 0: requested, 1: processing, 2: ready, 3: failed
                $STATUS_MAP = [
                    0 => __('transaction_report_page.Queue'),
                    1 => __('transaction_report_page.Processing'),
                    2 => __('transaction_report_page.Ready for download'),
                    3 => __('transaction_report_page.Failed to process'),
                ];

                $data = $reports->map(function ($report) use (&$slno, &$STATUS_MAP) {
                    $parameters = json_decode($report->parameters, true);


                    return [
                        'id' => $report->id,
                        'slno' => $slno++,
                        'report_type' => $report->report_type,
                        'parameters' => $parameters,
                        'date_range' => ($parameters['date_from'] ?? 'N/A') . ' - ' . ($parameters['date_to'] ?? 'N/A'),
                        'created_at' => $report->created_at->format('d/m/Y'),
                        'statusMessage' => $STATUS_MAP[$report->status],
                        'status' => $report->status,
                    ];
                });

                return response()->json([
                    'draw' => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data,
                    'status' => 'success'
                ], 200);
            } catch (\Exception $e) {
                report($e);
                return response()->json([
                    'status' => 'failed',
                    'message' => __('validation.unable_to_process')
                ], 500);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized')
        ], 401);
    }

    public function downloadReport($report_id)
    {
        $report = ReportGeneration::find($report_id);
        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }

        $fileName = $report->file_path;
        $folderPath = storage_path('app/public/reports');
        $filePath = $folderPath . '/' . $fileName;

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Determine MIME type based on file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Generate a signed URL for a new route, valid for 5 minutes
        $temporaryUrl = URL::temporarySignedRoute(
            'india.download.signed',
            now()->addMinutes(5),
            //  now()->addHour(),
            ['report_id' => $report_id]
        );

        // Return JSON response with the temporary URL and metadata
        return response()->json([
            'url' => $temporaryUrl,
            'filename' => basename($filePath),
            'contentType' => $mimeType,
            'api_secret_key' => env('ENCRYPTED_API_SECRET_KEY'), // Send API key separately
        ]);
    }

    public function downloadSigned($report_id)
    {

        $report = ReportGeneration::find($report_id);
        if (!$report) {
            abort(404, 'Report not found');
        }

        $fileName = $report->file_path;
        $folderPath = storage_path('app/public/reports');
        $filePath = $folderPath . '/' . $fileName;

        if (!File::exists($filePath)) {
            abort(404, 'File not found');
        }

        // Determine MIME type
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Set headers to force download
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
            'Access-Control-Expose-Headers' => 'Content-Disposition,Content-Type',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'X-API-Secret, Content-Type',
        ];

        // Return the file as a download response
        return response()->download($filePath, basename($filePath), $headers);
    }

}
