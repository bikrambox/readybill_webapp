<?php

namespace Modules\CoreWeb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserIsActive
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
        // Check if the user is authenticated using the 'api' guard
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'status' => 0,
                'message' => __('validation.Unauthorized access'),
                'code' => 401,
            ], 403);
        }

        $user = Auth::guard('api')->user();


        if ($user->active == 0) {
            return response()->json([
                "error" => __('validation.Your account has been deactivated'),
                "status" => 0,
            ], 403);
        }

        return $next($request);

    }
}
