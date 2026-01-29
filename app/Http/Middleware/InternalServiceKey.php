<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InternalServiceKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = (string) $request->header('X-Service-Key', '');
        $expected = (string) env('ORDER_SERVICE_KEY', '');

        if ($expected === '' || $key !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}