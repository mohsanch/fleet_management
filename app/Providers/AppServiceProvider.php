<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('super-admin-only', function ($user) {
            return $user->user_type === 'super_admin';
        });

        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }
}

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
