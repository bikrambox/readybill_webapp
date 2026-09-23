<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckUserCountryCode
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
        $countryCode = strtolower($request->segment(3, ''));

        // Check authenticated user and validate country code
        $user = Auth::guard('api')->user();

        if ($user && (strtolower($user->detected_country_code) !== $countryCode)) {
            return response()->json([
                'status' => 0,
                'message' => __('validation.Unauthorized access'),
                'code' => 401,
            ], 403);
        }

        return $next($request);
        
    }
}
