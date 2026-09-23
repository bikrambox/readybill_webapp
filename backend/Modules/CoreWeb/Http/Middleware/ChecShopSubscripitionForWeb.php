<?php

namespace Modules\CoreWeb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Authentication\Helpers\UserHelper;

class ChecShopSubscripitionForWeb
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
            'subscription',
            'profile',
            'change-password',
            'support',
            'dataset',
        ];

        // Check if the user is authenticated using the default web guard
        if (!Auth::check()) {
            // return redirect()->route('login')->with('error', __('validation.Please log in to continue'));
            return redirect(locale_route('login'))->with('error', __('validation.Please log in to continue'));
        }

        $user = Auth::user();

        // Determine shop ID based on user role
        if ($user->isAdmin == 1) {
            $shopId = $user->shop->shop_id;
        } else {
            $shopId = $user->staff->addedBy;
        }

        // Get the current route name
        $currentRoute = $request->route()->getName();

        // Check if the route should be ignored
        if (!in_array($currentRoute, $ignoredRoutes)) {
            $subscriptionStatus = UserHelper::checkShopSubscription($shopId,$user->module_type);

            if ($subscriptionStatus['status'] === 0) {
                // return redirect()->route('subscription')->with('error', $subscriptionStatus['message']);

                return redirect(locale_route('subscription'))->with('error', $subscriptionStatus['message']);

            }
        }

        return $next($request);
    }
}
