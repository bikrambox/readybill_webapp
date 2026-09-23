<?php

namespace Modules\Authentication\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class SwitchDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if ($user && $user->module_type) {
            DB::setDefaultConnection($user->module_type);
        }
        return $next($request);
    }
}
