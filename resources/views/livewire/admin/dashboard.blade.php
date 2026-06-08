<div>
    <!-- En-tête -->
    <div class="flex flex-col gap-1">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-primary">Tableau de bord</p>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
            Bonjour {{ auth()->user()->prenom }} 👋
        </h1>
        <p class="text-sm text-zinc-500">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>

    <!-- Cartes statistiques -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->stats as $card)
            <a href="{{ route($card['href']) }}" wire:navigate
               class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md">
                <span @class([
                    'absolute right-0 top-0 h-20 w-20 -translate-y-8 translate-x-8 rounded-full blur-2xl transition group-hover:opacity-100',
                    'bg-primary/10 opacity-60' => $card['tone'] === 'primary',
                    'bg-secondary/10 opacity-60' => $card['tone'] === 'secondary',
                ])></span>
                <div class="relative flex items-center justify-between">
                    <span @class([
                        'flex h-11 w-11 items-center justify-center rounded-xl',
                        'bg-primary/10 text-primary' => $card['tone'] === 'primary',
                        'bg-secondary/10 text-secondary' => $card['tone'] === 'secondary',
                    ])>
                        <x-dynamic-component :component="'lucide-'.$card['icon']" class="h-5 w-5" />
                    </span>
                    <x-lucide-arrow-up-right class="h-4 w-4 text-zinc-300 transition group-hover:text-primary" />
                </div>
                <p class="relative mt-4 text-3xl font-semibold tracking-tight text-zinc-900">{{ $card['value'] }}</p>
                <p class="relative text-xs font-semibold uppercase tracking-wider text-zinc-400">{{ $card['label'] }}</p>
            </a>
        @endforeach
    </div>

    <!-- Tickets à traiter + pense-bête -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Mes tickets à traiter --}}
        <div class="rounded-2xl border border-zinc-200 bg-white lg:col-span-2">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-700">
                    <x-lucide-list-checks class="h-4 w-4 text-primary" />
                    À traiter
                </h2>
                <a href="{{ route('admin.tickets') }}" wire:navigate
                   class="text-xs font-semibold text-primary hover:text-primary/70">Tous les tickets →</a>
            </div>

            <div class="divide-y divide-zinc-100">
                @forelse ($this->myTickets as $ticket)
                    <a href="{{ route('admin.tickets', ['search' => $ticket->demande]) }}" wire:navigate
                       wire:key="dash-ticket-{{ $ticket->id }}"
                       class="flex items-center gap-3 px-6 py-3.5 transition hover:bg-secondary/[0.06]">
                        {{-- Pastille importance --}}
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                              style="background-color: {{ $ticket->importanceColor() }}"
                              title="{{ $ticket->importanceLabel() }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-800">{{ $ticket->demande }}</p>
                            <p class="truncate text-xs text-zinc-400">
                                @if ($ticket->site)
                                    <x-lucide-globe class="mr-0.5 inline h-3 w-3 align-text-bottom" />
                                    {{ $ticket->site->nom }}
                                    @if ($ticket->site->client?->societe)
                                        · {{ $ticket->site->client->societe }}
                                    @endif
                                @else
                                    <span class="text-zinc-300">Sans site</span>
                                @endif
                            </p>
                        </div>
                        @if ($ticket->statut)
                            <span class="hidden shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold sm:inline-block"
                                  style="background-color: {{ $ticket->statut->color() }}1a; color: {{ $ticket->statut->color() }}">
                                {{ $ticket->statut->libelle }}
                            </span>
                        @endif
                        <span class="hidden w-20 shrink-0 text-right text-xs text-zinc-400 md:block">
                            {{ $ticket->date?->locale('fr')->isoFormat('D MMM') }}
                        </span>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <x-lucide-check class="h-6 w-6" />
                        </span>
                        <p class="mt-3 text-sm font-medium text-zinc-600">Rien à traiter</p>
                        <p class="text-xs text-zinc-400">Aucun ticket ouvert ne vous est attribué.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <livewire:admin.notepad />
    </div>

    <!-- Activité du mois + favoris -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Activité du mois --}}
        <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-zinc-900 to-zinc-800 p-6 text-white">
            <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-secondary">
                <x-lucide-calendar-days class="h-4 w-4" />
                {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}
            </p>

            <div class="mt-5 space-y-4">
                <div class="flex items-baseline justify-between border-b border-white/10 pb-4">
                    <div>
                        <p class="text-3xl font-semibold tracking-tight">{{ $this->activiteMois['heures'] }}</p>
                        <p class="text-xs uppercase tracking-wider text-zinc-400">Temps passé</p>
                    </div>
                    <x-lucide-clock class="h-7 w-7 text-primary" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-2xl font-semibold tracking-tight">{{ $this->activiteMois['actions'] }}</p>
                        <p class="text-xs uppercase tracking-wider text-zinc-400">Actions</p>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold tracking-tight">{{ $this->activiteMois['tickets_termines'] }}</p>
                        <p class="text-xs uppercase tracking-wider text-zinc-400">Tickets terminés</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Favoris --}}
        <div class="lg:col-span-2">
            <livewire:admin.favorites />
        </div>
    </div>
</div>
