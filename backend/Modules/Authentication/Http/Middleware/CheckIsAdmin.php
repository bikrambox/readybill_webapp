<?php

namespace Modules\Authentication\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckIsAdmin
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
        // return $next($request);

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            if ($user->isAdmin == 1) {
                return $next($request);
            } else {
                return response()->json([
                    "error" => __("validation.User dont' have permission to access"),
                    "status" => 0,
                ], 403);
            }
        } else {
            return response()->json([
                "error" => __("validation.User dont' have permission to access"),
                "status" => 0,
            ], 403);
        }
    }
    
}
