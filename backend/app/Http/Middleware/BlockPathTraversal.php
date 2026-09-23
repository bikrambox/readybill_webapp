<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockPathTraversal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // raw uri before Laravel normalizes it
        $rawUri = $_SERVER['REQUEST_URI'] ?? '';

        // dd($_SERVER['REQUEST_URI']);

        if (
            str_contains($rawUri, '../') ||
            str_contains($rawUri, '..\\') ||
            preg_match('/\.\.(\/|\\\\)/', $rawUri)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request path.'
            ], 400);
        }

        return $next($request);
    }
}
