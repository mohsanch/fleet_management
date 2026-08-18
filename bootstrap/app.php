<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (!function_exists('activity_log')) {
    function activity_log($action, $reason = null, $model = null, $details = null) {
        \App\Models\ActivityLog::create([
            'user_id'    => auth()->id() ?? 1,
            'action'     => $action,
            'reason'     => $reason,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model ? $model->id : null,
            'details'    => $details,
        ]);
    }
}
