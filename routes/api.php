<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/info', function () {
    return response()->json([
        'ok11' => true,
        'service' => config('app.name'),
        'sha' => env('IMAGE_SHA', null),
        'time' => now()->toISOString(),
    ]);
});

Route::get('/database', function () {
    try {
        $started = microtime(true);
        DB::connection()->select('SELECT 1');
        $ms = (microtime(true) - $started) * 1000;

        return response()->json([
            'ok' => true,
            'db' => [
                'connection' => DB::getDefaultConnection(),
                'database' => DB::connection()->getDatabaseName(),
                'ping_ms' => round($ms, 2),
            ],
            'time' => now()->toISOString(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => 'DB connection failed',
            'message' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
        ], 500);
    }
});

Route::get('/orders', function () {
    return response()->json([
        [
            'id' => 1,
            'name' => 'Order 1',
            'total_cost' => 50.90,
            'currency' => 'EUR',
        ],
        [
            'id' => 2,
            'name' => 'Order 2',
            'total_cost' => 40.90,
            'currency' => 'EUR',
        ],
        [
            'id' => 3,
            'name' => 'Order 3',
            'total_cost' => 30.90,
            'currency' => 'EUR',
        ],
    ]);
});
