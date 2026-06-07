<div>
    <!-- En-tête -->
    <div class="flex flex-col gap-1">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-primary">Tableau de bord</p>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
            Bonjour {{ auth()->user()->prenom }} 👋
        </h1>
        <p class="text-sm text-zinc-500">Voici un aperçu de votre espace administrateur.</p>
    </div>

    <!-- Cartes statistiques (placeholder) -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Sites',    'value' => '—', 'icon' => 'globe',  'tone' => 'primary',   'href' => 'admin.sites'],
                ['label' => 'Clients',  'value' => '—', 'icon' => 'users',  'tone' => 'secondary', 'href' => 'admin.clients'],
                ['label' => 'Actions',  'value' => '—', 'icon' => 'zap',    'tone' => 'primary',   'href' => 'admin.actions'],
                ['label' => 'Tickets',  'value' => '—', 'icon' => 'ticket', 'tone' => 'secondary', 'href' => 'admin.tickets'],
            ];
        @endphp

        @foreach ($cards as $card)
            <a href="{{ route($card['href']) }}" wire:navigate
               class="group rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span @class([
                        'flex h-11 w-11 items-center justify-center rounded-xl',
                        'bg-primary/10 text-primary' => $card['tone'] === 'primary',
                        'bg-secondary/10 text-secondary' => $card['tone'] === 'secondary',
                    ])>
                        <x-dynamic-component :component="'lucide-'.$card['icon']" class="h-5 w-5" />
                    </span>
                    <x-lucide-arrow-up-right class="h-4 w-4 text-zinc-300 transition group-hover:text-zinc-500" />
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-zinc-900">{{ $card['value'] }}</p>
                <p class="text-sm text-zinc-500">{{ $card['label'] }}</p>
            </a>
        @endforeach
    </div>

    <!-- Favoris -->
    <div class="mt-8">
        <livewire:admin.favorites />
    </div>

    <!-- Accueil + pense-bête -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 bg-white p-8 lg:col-span-2">
            <h2 class="text-lg font-semibold text-zinc-900">Bienvenue sur votre panneau de contrôle</h2>
            <p class="mt-2 max-w-2xl text-sm text-zinc-500">
                Utilisez le menu latéral pour accéder aux différentes sections : sites, contrats,
                clients, actions, tickets, chatbots et statut des services.
            </p>
        </div>

        <livewire:admin.notepad />
    </div>
</div>
