<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    #[Validate('required|string')]
    public string $login = '';

    #[Validate('required|string')]
    public string $password = '';

    /** Connexion réussie : déclenche l'écran de succès + la redirection côté client. */
    public bool $success = false;

    /** URL de destination après connexion. */
    public string $redirectTo = '';

    /**
     * Tentative d'authentification.
     */
    public function login_request(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['login' => $this->login, 'password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        if (Auth::user()->isSuspended()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login' => trans('Votre compte a été suspendu. Contactez un administrateur.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        Auth::user()->forceFill(['last_login_at' => now()])->save();

        session()->regenerate();

        $this->redirectTo = Auth::user()->isAdmin()
            ? route('admin.dashboard')
            : route('client.dashboard');

        $this->success = true;
    }

    /**
     * Vérifie que la requête n'est pas rate limited.
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Clé de throttling pour la requête.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->login).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
