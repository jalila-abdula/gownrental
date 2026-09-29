<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

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
        RedirectIfAuthenticated::redirectUsing(function (): string {
            $role = auth()->user()?->role;

            return match ($role) {
                'owner' => route('owner.dashboard'),
                'employee' => route('employee.dashboard'),
                'customer' => route('customer.dashboard'),
                default => '/',
            };
        });
    }
}
