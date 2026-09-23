<?php

namespace Modules\CoreWeb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckWebIsAdmin
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
        if (Auth::check() && Auth::user()->isAdmin == 1) {
            return $next($request);
        }

        abort(403, __('validation.You are not authorized to access this page'));
    }
}
