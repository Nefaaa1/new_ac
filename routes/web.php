<?php

use App\Livewire\Admin;
use App\Livewire\Auth\Login;
use App\Livewire\Client\Dashboard as ClientDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', Login::class)->middleware('guest')->name('login');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Redirige vers le dashboard correspondant au type de l'utilisateur connecté.
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'client.dashboard');
})->middleware('auth')->name('dashboard');

// Espace administrateur.
Route::middleware(['auth', 'not-suspended', 'type:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('/sites', Admin\Sites::class)->name('sites');
    Route::get('/contrats', Admin\Contrats::class)->name('contrats');
    Route::get('/clients', Admin\Clients::class)->name('clients');
    Route::get('/actions', Admin\Actions::class)->name('actions');
    Route::get('/tickets', Admin\Tickets::class)->name('tickets');
    Route::get('/chatbots', Admin\Chatbots::class)->name('chatbots');
    Route::get('/status', Admin\Status::class)->name('status');
    Route::get('/profil', Admin\Profil::class)->name('profil');

    Route::prefix('recap')->name('recap.')->group(function () {
        Route::get('/actions', Admin\Recap\Actions::class)->name('actions');
        Route::get('/tickets', Admin\Recap\Tickets::class)->name('tickets');
    });

    // Gestion : réservé aux admins "accès total" (Gate manage-admins).
    Route::prefix('gestion')->name('gestion.')->middleware('can:manage-admins')->group(function () {
        Route::get('/admins', Admin\Gestion\Admins::class)->name('admins');
    });
});

// Espace client.
Route::middleware(['auth', 'not-suspended', 'type:client'])->group(function () {
    Route::get('/client', ClientDashboard::class)->name('client.dashboard');
});
