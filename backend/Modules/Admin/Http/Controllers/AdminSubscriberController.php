<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use App\Models\Shop;
use Modules\Authentication\Entities\User;
use App\Models\Staff;
use Modules\Core\Entities\Subscription;

use Carbon\Carbon;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Validator;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Validation\Rule;

use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;

use Modules\Core\Rules\NoScriptTag;
use Modules\Core\Rules\PhoneNumber;

use Auth;

class AdminSubscriberController extends Controller
{
    public function shopSubscriberList()
    {
        $shops = Shop::all();
        return view('admin.shop_subscriber_list', compact('shops'));
    }

    public function shopDetails($module_type = 'grocery_india', $user_id)
    {
        return view('admin::shopDetails', compact('user_id','module_type'));
    }

    public function shopDetailsData( $module_type = 'grocery_india', $user_id)
    {

        // $user = User::where('user_id', $user_id)->first();

        $user = User::find($user_id);

        $isPhoto = 0;
        $staffImageUrl = '';

        $entity_id = '';


        $shop_type = $user->shop_type;

        // Switch to the shop type connection
        DB::setDefaultConnection($module_type);

        if ($user->isAdmin == 1) {

            $user['details'] = $user->shop;
            $user['logo'] = $user->shop->logo;

            $api_key = $user['apiKey']['key'];

            $entity_id = (new \DateTime($user->shop->created_at))->format('dmY') . $user->shop->shop_id;

            $user['api_key'] = Crypt::encryptString($api_key);


            // EXPIRY DATE
            // $shop_subscription = $user->shop->subscriptions()
            //     ->orderBy('pivot_start_date', 'desc')
            //     ->first();

            $shop = DB::table('shops')->where('user_id', $user->user_id)->first();



            $shop_subscription = DB::table('shop_subscriptions')
                ->where('shop_id', $shop->shop_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($shop_subscription) {
                // $subscription_expiry_date = $shop_subscription->pivot->end_date;
                $subscription_expiry_date = $shop_subscription->end_date;

                $subscription_expiry_date = Carbon::parse($subscription_expiry_date)->format('d-m-Y H.i.s');
            }
            // EXPIRY DATE

            // dd($shop_subscription);

        } 
        
        // else if ($user->isAdmin == 0) {

        //     $user['details'] = $user->staff;
        //     $shop = Shop::find($user->staff->addedBy);
        //     $user['details']['business_name'] = $shop->business_name;
        //     $user['details']['gstin'] = $shop->gstin;
        //     $user['logo'] = $shop->logo;
        //     // $user['api_key'] = Crypt::encryptString($shop->user->apiKey->key);

        //     $user['photo'] = $user->staff->photo;

        //     if (isset($user->staff->photo) && $user->staff->photo != 'NA') {

        //         // staff photo
        //         $media_url = env('MEDIA_URL');
        //         $photo = ($user->staff->photo != 'NA' && $user->staff->photo != '') ? $user->staff->photo : '';
        //         $staffImageUrl = $media_url . '/photo/' . $photo;
        //         $isPhoto = 1;

        //         if (!$photo && !@getimagesize($staffImageUrl)) {
        //             $staffImageUrl = "assets/img/user.jpg";
        //             $isPhoto = 0;
        //         }
        //         // staff photo
        //     } else {
        //         $staffImageUrl = "assets/img/user.jpg";
        //         $isPhoto = 0;
        //     }

        //     // dd($user->staff->photo);

        //     $entity_id = (new \DateTime('2024-12-26 12:17:17'))->format('dmY') . $shop->shop_id;

        //     // EXPIRY DATE
        //     // $shop_subscription = $shop->subscriptions()->latest()->first();

        //     $shop_subscription = DB::table('shop_subscriptions')
        //         ->where('shop_id', $shop->shop_id)
        //         ->orderBy('created_at', 'desc')
        //         ->first();

        //     if ($shop_subscription) {
        //         // $subscription_expiry_date = $shop_subscription->pivot->end_date;
        //         $subscription_expiry_date = $shop_subscription->end_date;

        //         $subscription_expiry_date = Carbon::parse($subscription_expiry_date)->format('d-m-Y H.i.s');
        //     }
        //     // EXPIRY DATE
        // }


        // logo
        $logo_url = env('LOGO_URL');
        $logo = ($user->logo != 'NA' && $user->logo != '') ? $user->logo : '';
        $imageUrl = $logo_url . $logo;
        $isLogo = 1;

        if (!$logo && !@getimagesize($imageUrl)) {
            $imageUrl = env('BASE_URL') . "/" . "assets/img/user.jpg";
            $isLogo = 0;
        }
        // logo

        $user->makeHidden(['apiKey']);

        $subscriptionData = SubscriptionHelper::subsriptionDataFormat($shop_subscription->subscription_data);

    
        if ($shop_subscription) {
            $isSubscriptionExpired = UserHelper::isSubscriptionExpired($shop_subscription, $subscription_expiry_date);

            $subscription_expiry_date = $subscription_expiry_date ? Carbon::parse($subscription_expiry_date)->format('d-m-Y') : null;
            $subscription = Subscription::find($subscriptionData['subscription_id']);
        }


        $shop_subscription = DB::table('shop_subscriptions')
            ->where('shop_id', $shop->shop_id)
            ->orderBy('created_at', 'desc')
            ->first();


        // $all_shop_subscription = DB::table('shop_subscriptions')
        //     // ->leftJoin('readybill_central.subscriptions', 'shop_subscriptions.subscription_id', '=', 'subscriptions.subscription_id')
        //     ->where('shop_subscriptions.shop_id', $shop->shop_id)
        //     ->orderBy('shop_subscriptions.created_at', 'desc')
        //     ->select('shop_subscriptions.*')
        //     ->get();

        $all_shop_subscription = DB::table('shop_subscriptions')
            ->where('shop_id', $shop->shop_id)
            ->orderBy('created_at', 'desc')
            ->select(
                'shop_subscriptions.*',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(subscription_data, '$.plan_name')) as plan_name")
            )
            ->get();


        // dd($subscriptionData);

        return response()->json([
            'status' => 'success',
            'data' => $user,
            'logo' => $imageUrl,
            'isLogo' => $isLogo,
            'photo' => $staffImageUrl,
            'isPhoto' => $isPhoto,
            'entity_id' => $entity_id,
            'subscription_expiry_date' => $subscription_expiry_date ?? "",
            'subscription_current_plan' => $subscriptionData['plan_name'] ?? "",
            'subscription_current_plan_id' => $subscriptionData['subscription_id']?? "",
            'shop_subscription' => $shop_subscription ?? "",
            'all_shop_subscription' => $all_shop_subscription,
        ], 200);

    }


    public function shopDataUpdate(Request $request)
    {

        $validate = Validator::make($request->all(), [

            // 'module_type' => 'required',
            'user_id' => 'required|numeric|exists:users',
            // 'shop_id' => 'required|numeric|exists:shops',

            'mobile' => [
                'required',
                'numeric',
                'digits:10',
                Rule::unique('users', 'mobile')->ignore($request->user_id, 'user_id'), // Use the value of user_id here
            ],


            // 'mobile' => [
            //     'required',
            //     'unique:users,mobile',
            //     // 'regex:/^[6-9]\d{9}$/',
            //     new PhoneNumber($request->country_code),
            // ],

            // 'email' => [
            //     'required',
            //     'string',
            //     'email:rfc,dns',
            //     'max:250',
            //     Rule::unique('shops', 'email')->ignore($request->shop_id, 'shop_id'), // Use the value of shop_id here
            // ],


            'email' => [
                'nullable',
                'string',
                'max:250',
                new NoScriptTag,
                function ($attribute, $value, $fail) use ($request) {

                    if ($value !== 'NA') {

                        $user = User::find($request->user_id);

                        if (!$user) {
                            $fail(__('validation.Invalid user.'));
                            return;
                        }

                        $module_type = $user->module_type;

                        // Get current shop id (adjust if relation exists)
                        $shop = DB::connection($module_type)
                            ->table('shops')
                            ->where('user_id', $request->user_id)
                            ->first();

                        $currentShopId = $shop?->shop_id ?? null;

                        // ✅ Check shops table (ignore current shop)
                        $existsInShops = DB::connection($module_type)
                            ->table('shops')
                            ->where('email', $value)
                            ->when($currentShopId, function ($query) use ($currentShopId) {
                            $query->where('shop_id', '!=', $currentShopId);
                        })
                            ->exists();

                        // ✅ Check staff table
                        $existsInStaff = DB::connection($module_type)
                            ->table('staff')
                            ->where('email', $value)
                            ->exists();

                        if ($existsInShops || $existsInStaff) {
                            $fail(__('validation.The email has already been registered.'));
                        }
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

        $user = [
            'mobile' => $request->mobile,
        ];
        DB::table('users')->where('user_id', $request->user_id)->update($user);

        $user = User::find($request->user_id);

        if ($user->module_type == 'grocery_india') {
            $shop = \Modules\GroceryIndia\Entities\Shop::where('user_id', $request->user_id)->first();
        } else if ($user->module_type == 'grocery_german') {
            $shop = \Modules\GroceryGermany\Entities\Shop::where('user_id', $request->user_id)->first();
        }

        $shop->email = $request->email;
        $shop->save();


        // $user = User::with(['shop', 'staff'])->find($request->user_id);

        Cache::forget('user_' . $user->mobile);
        Cache::store('memcached')->forget('user_' . $user->mobile);

        // Store updated user data in cache
        $cacheKey = 'user_' . $user->mobile;
        Cache::put($cacheKey, $user);

        $response = [
            'status' => 'success',
            'message' => 'User data is update successfully.',
            'data' => $user
        ];
        return response()->json($response, 201);
    }

    // ACTIVATE SHOP OWNER & THE EMPLOYEE BELONG TO THE SHOW
    public function activateShopOwner(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:users',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $user = User::find($request->user_id);
        $user->active = 1;
        $user->save();


        $addedBy = $user->shop->shop_id;


        if($user->module_type == 'grocery_india'){

            // active shop employees
            $staffs = \Modules\GroceryIndia\Entities\Staff::where('addedBy', $addedBy)->get();

        }
        else if($user->module_type == 'grocery_german'){
            // active shop employees
            $staffs = \Modules\GroceryGermany\Entities\Staff::where('addedBy', $addedBy)->get();

        }


        foreach ($staffs as $staff) {
            $user = User::find($staff->user_id);
            $user->active = 1;
            $user->save();
        }
        // active shop employees

        $response = [
            'status' => 'success',
            'message' => 'User data is update successfully.',
            'data' => $user
        ];
        return response()->json($response, 201);

    }
    // ACTIVATE SHOP OWNER & THE EMPLOYEE BELONG TO THE SHOW

    // DEACTIVATE SHOP OWNER & THE EMPLOYEE BELONG TO THE SHOW

    public function deactivateShopOwner(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:users',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $user = User::find($request->user_id);
        $user->active = 0;
        $user->save();

        $addedBy = $user->shop->shop_id;


        if ($user->module_type == 'grocery_india') {

            // deactive shop employees
            $staffs = \Modules\GroceryIndia\Entities\Staff::where('addedBy', $addedBy)->get();

        } else if ($user->module_type == 'grocery_german') {
            // deactive shop employees
            $staffs = \Modules\GroceryGermany\Entities\Staff::where('addedBy', $addedBy)->get();

        }

        foreach ($staffs as $staff) {
            $user = User::find($staff->user_id);
            $user->active = 0;
            $user->save();
        }
        // deactive shop employees

        $response = [
            'status' => 'success',
            'message' => 'User data is update successfully.',
            'data' => $user
        ];
        return response()->json($response, 201);
    }
    // DEACTIVATE SHOP OWNER & THE EMPLOYEE BELONG TO THE SHOW


    public function upgradeSubscriptionPlan(Request $request)
    {
        // Validation
        $validate = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:users',
            'subscription_id' => 'required|numeric|exists:subscriptions',
            'payment_mode' => 'required|in:online,cash',
            // 'module_type' => 'required',
        ], [
            'subscription_id.required' => 'Please select subscription plan',
            'subscription_id.numeric' => 'Please select subscription plan',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $user = User::find($request->user_id);

        $module_type = $user->module_type;

        // Switch to the shop type connection
        DB::setDefaultConnection($module_type);

        // $shop = Shop::find($user->shop->shop_id);

        $shop = DB::table('shops')->where('user_id', $user->user_id)->first();

        if (!$shop) {
            // Handle case where the shop is not found
            return response()->json([
                'success' => false,
                'message' => 'Shop not found.'
            ], 201);
        }

        // CHECK SHOP SUBSCRIPTION IS LESS THAN 1 MONTH, IF SUBSCRIPTION LEFT ONE MONTH THEN PLAN UPGRADATION CAN BE PROCESSED


        $shop_subscription = DB::table('shop_subscriptions')
            ->where('shop_id', $shop->shop_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $currentDate = Carbon::now();

        // if ($shop_subscription) {
        //     // Calculate the subscription end date
        //     $endDate = Carbon::parse($shop_subscription->end_date);

        //     // // Check if the remaining subscription time is less than one month
        //     // $remainingDays = $currentDate->diffInDays($endDate, false); // false to get negative if past

        //     // if ($remainingDays >= 30) {
        //     //     // Allow plan upgradation
        //     //     // dd("Subscription has less than one month left. Remaining days: {$remainingDays}");

        //     //     return response()->json([
        //     //         'success' => false,
        //     //         'message' => 'Upgrade subscription is not allowed. Please try before 30 days left to expired'
        //     //     ], 429);
        //     // }
        // } else {

        //     // if subscription not found then endDate will be today
        //     $endDate = Carbon::now()->format('Y-m-d H:i:s');

        //     // Check if the remaining subscription time is less than one month
        //     $remainingDays = $currentDate->diffInDays($endDate, false); // false to get negative if past

        // }
        

        // CHECK SHOP SUBSCRIPTION IS LESS THAN 1 MONTH, IF SUBSCRIPTION LEFT ONE MONTH THEN PLAN UPGRADATION CAN BE PROCESSED

        $shop_subscription = DB::table('shop_subscriptions')
            ->where('shop_id', $shop->shop_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $currentDate = Carbon::now();
        // $startDate = $shop_subscription && $currentDate->lessThanOrEqualTo($shop_subscription->end_date)
        //     ? Carbon::parse($shop_subscription->end_date)->addDay()
        //     : $currentDate;

        $startDate = $currentDate;

        // Fetch the subscription details
        $subscription = Subscription::find($request->subscription_id);

        // Clone start date and calculate the end date by adding months
        $endDate = $startDate->copy()->addMonths($subscription->months);

        // Adjust the end date if necessary (to handle potential month overflow)
        if ($startDate->day > $endDate->day) {
            $endDate = $endDate->endOfMonth(); // Adjust to the end of the month
        }

        // Add 1 extra day to the end date
        $endDate->addDay();

        // Prepare data for insertion into the database
        $data = [
            'shop_id' => $shop->shop_id,
            // 'subscription_id' => $request->subscription_id,
            'subscription_data' => json_encode($subscription->toArray()),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_status' => 'paid',
            'payment_mode' => $request->payment_mode,

            // ✅ Morph fields
            'assigned_by_id' => Auth::guard('api')->id(),
            'assigned_by_type' => get_class(Auth::guard()->user()),

            'created_at' => now(),
            'updated_at' => now()
        ];

        // Insert into the database
        DB::table('shop_subscriptions')->insert($data);

        $all_shop_subscription = DB::table('shop_subscriptions')
            // ->leftJoin('readybill_central.subscriptions', 'shop_subscriptions.subscription_id', '=', 'subscriptions.subscription_id')
            ->where('shop_subscriptions.shop_id', $shop->shop_id)
            ->orderBy('shop_subscriptions.created_at', 'desc')
            ->select(
                'shop_subscriptions.*',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(subscription_data, '$.plan_name')) as plan_name")
            )
            ->get();

        // Respond with success
        $response = [
            'status' => 'success',
            'message' => 'Shop Subscription Successfully Upgraded',
            'data' => $all_shop_subscription,
        ];
        return response()->json($response, 201);
    }

}
