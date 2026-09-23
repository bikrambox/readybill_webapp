<?php

namespace Modules\Authentication\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Modules\Authentication\Helpers\UserHelper;
use Illuminate\Support\Facades\Auth;
use Modules\GroceryIndia\Entities\Shop;

class CheckShopSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Define the routes to ignore
        $ignoredRoutes = [
            // 'api/update-profile',
            // 'api/generate-verify-otp',
            // 'api/update-mobile-number',

            //account
            `api/update-profile`,
            `api/generate-verify-otp`,
            `api/update-mobile-number`,
            `api/delete-account`,
            //account

            // others
            `api/user-preferences`,
            // others


            // change password
            `api/update-password`,
            // change password


            // support
            `api/create-query`,
            // support

            // dataset
            `api/inventory-store-multiple`,
            `api/dataset`,
            `api/update-cell-data`,
            `api/dataset/multiple-delete`,
            `api/reset-dataset`,
            // dataset

            // notification
            `api/notifications`,
            // notification

            // push notification
            `api/set-device-token`,
            // 'api/delete-device-token',
            // push notification
        ];

        // Check if the user is authenticated using the 'api' guard
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 0,
                'message' => 'Unauthorized access.',
                'code' => 401,
            ], 401);
        }

        $user = Auth::guard('api')->user();

        if ($user->isAdmin == 1) {
            $shopId = $user->shop->shop_id;
        } else if ($user->isAdmin == 0) {
            $shopId = $user->staff->addedBy;
        }

        // Get the current route name or path
        $currentRoute = $request->route()->getName(); // Get route name if named routes are used
        $currentPath = $request->path(); // Get the request path (e.g., 'api/subscription')


        // Check if the route should be ignored
        if (!in_array($currentRoute, $ignoredRoutes) && !in_array($currentPath, $ignoredRoutes)) {

            $subscriptionStatus = UserHelper::checkShopSubscription($shopId, $user->module_type);

            if ($subscriptionStatus['status'] === 0) {
                return response()->json([
                    'message' => $subscriptionStatus['message'],
                    'status' => 0,
                ], $subscriptionStatus['code']);
            }
        }


        return $next($request);
    }
}
