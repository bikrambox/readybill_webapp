<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Crypt;


class VerifyApiSecret
{

    /**
     * Routes to be excluded from API secret verification
     */
    protected $except = [
        // 'api/v2/authorized-agent/activate/*'
    ];



    /**
     * Check if the current request is in the except array
     */
    protected function isExcluded(Request $request)
    {
        foreach ($this->except as $route) {
            if ($request->is($route)) {
                return true;
            }
        }
        return false;
    }



    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Skip verification for excluded routes
        if ($this->isExcluded($request)) {
            return $next($request);
        }

        $headerSecret = $request->header('X-API-Secret');


        if (!$headerSecret) {
            return response()->json(['error' => 'Secret key missing'], 401);
        }

        try {

            $decryptedSecret = Crypt::decryptString($headerSecret);


            $expectedSecret = env('API_SECRET_KEY', 'BVnBzajFQEEGBmjM6FCURGFUyI2vRMcwcg4TZBTGVfbfaBOOTDIkywEA6vfIfeDX');

            if ($decryptedSecret !== $expectedSecret) {
                return response()->json(['error' => 'Invalid secret key'], 401);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {

            // dd($e->getMessage());

            return response()->json(['error' => 'Invalid or corrupted secret key'], 401);
        }

        return $next($request);
    }
}
