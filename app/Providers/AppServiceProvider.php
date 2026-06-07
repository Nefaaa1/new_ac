<?php

namespace App\Providers;

use App\Models\User;
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
        // Seuls les admins "accès total" gèrent les autres administrateurs.
        Gate::define('manage-admins', fn (User $user) => $user->isAdmin() && $user->hasFullAccess());
    }
}
