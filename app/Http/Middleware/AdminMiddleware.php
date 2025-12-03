<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !($user instanceof \App\Models\Admin)) {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
}
