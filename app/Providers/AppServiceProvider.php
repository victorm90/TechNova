<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('manage-nomencladores', fn (User $user): bool => $user->isAdmin());
        Gate::define('manage-productos', fn (User $user): bool => $user->isAdmin());
        Gate::define('manage-proveedores', fn (User $user): bool => $user->isAdmin());
        Gate::define('ver-inventario', fn (User $user): bool => true);
        Gate::define(
            'registrar-movimientos',
            fn (User $user): bool => $user->isAdmin() || $user->role === User::ROLE_OPERATOR,
        );

        RedirectIfAuthenticated::redirectUsing(
            fn (Request $request): string => route('dashboard'),
        );
    }
}
