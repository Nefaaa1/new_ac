<div>
    @if($success)
        <!-- Écran de succès + redirection -->
        <div x-data x-init="setTimeout(() => Livewire.navigate(@js($redirectTo)), 1500)"
             class="py-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                <x-lucide-check class="h-7 w-7" />
            </div>
            <h1 class="mt-6 text-xl font-semibold uppercase tracking-widest text-gray-900">
                Connexion réussie
            </h1>
            <p class="mt-2 flex items-center justify-center gap-2 text-sm text-gray-500">
                <x-lucide-loader-circle class="h-4 w-4 animate-spin" />
                Redirection vers votre espace…
            </p>
            <div class="mx-auto mt-4 h-px w-12 bg-primary"></div>
        </div>
    @else
        <!-- Logo -->
        <div class="flex justify-center">
            <img src="{{ asset('images/logo-website-noir.png') }}" alt="Partner Web Communication"
                 class="h-14 w-auto">
        </div>

        <!-- Message d'accueil -->
        <div class="mt-8 text-center">
            <h1 class="text-xl font-semibold uppercase tracking-widest text-gray-900">
                Bienvenue
            </h1>
            <p class="mt-2 text-sm text-gray-500">
                Connectez-vous pour accéder à votre espace.
            </p>
            <div class="mx-auto mt-4 h-px w-12 bg-primary"></div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mt-6" :status="session('status')" />

        <!-- Formulaire -->
        <form wire:submit="login_request" class="mt-8 space-y-2">
            <x-text-input label="Nom d'utilisateur" id="login" class="block w-full" type="text"
                          name="login" wire:model="login" required autofocus autocomplete="username" />

            <x-text-input label="Mot de passe" id="password" class="block w-full" type="password"
                          name="password" wire:model="password" required autocomplete="current-password" />

            <div class="pt-2">
                <x-primary-button full size="lg" icon="log-in" text="Connexion"
                                  wire:loading.attr="disabled" />
            </div>
        </form>
    @endif
</div>
