<?php

namespace Modules\Authentication\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// use Modules\Authentication\Helpers\UserHelper;
use Modules\Authentication\Helpers\SubscriptionHelper;
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
        // Get always accessible routes from config
        $ignoredRoutes = config('subscription.always_accessible_routes', []);

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

        $currentRoute = $request->route()->getName();
        $currentPath = $request->path();

        // Check if route should be ignored
        if (!in_array($currentRoute, $ignoredRoutes) && !in_array($currentPath, $ignoredRoutes)) {

            // First check basic subscription validity
            $subscriptionStatus = SubscriptionHelper::checkShopSubscription($shopId, $user->module_type);

            if ($subscriptionStatus['status'] === 0) {
                return response()->json([
                    'message' => $subscriptionStatus['message'],
                    'status' => 0,
                ], $subscriptionStatus['code']);
            }

            // Then check feature-specific access (for restricted routes)
            $featureAccess = SubscriptionHelper::checkFeatureAccess($shopId, $user->module_type, $currentRoute, $currentPath);

            if ($featureAccess['status'] === 0) {
                return response()->json([
                    'message' => $featureAccess['message'],
                    'status' => 0,
                ], $featureAccess['code']);
            }
        }

        return $next($request);
    }
}
