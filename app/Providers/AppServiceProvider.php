<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Blade directive to check roles in views: @role('admin'), @role('admin|pos')
        Blade::if('role', function ($role) {
            $user = auth()->user();
            if (! $user) {
                return false;
            }

            return $user->hasRole($role);
        });
    }
}
