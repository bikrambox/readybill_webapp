<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;

class TrackSystemIssues
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle(Request $request, Closure $next)
    {
        $source = $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()
            ? 'app'
            : 'web';

        $request->attributes->set('log_data', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => $source,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::guard('api')->check() ? Auth::guard('api')->user()->user_id : null,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $next($request);
    }


}
