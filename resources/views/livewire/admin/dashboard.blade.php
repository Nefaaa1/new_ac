{{-- Cockpit sans scroll (xl) : zones fixes + zone flexible dont les listes scrollent en interne --}}
<div class="flex min-h-0 flex-col gap-4 xl:h-full">
    <!-- En-tête : salutation + créations rapides -->
    <div class="flex shrink-0 flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-baseline gap-3">
            <h1 class="text-xl font-semibold tracking-tight text-zinc-900">
                Bonjour {{ auth()->user()->prenom }} 👋
            </h1>
            <p class="hidden text-xs font-medium uppercase tracking-[0.2em] text-zinc-400 md:block">
                {{ now()->locale('fr')->isoFormat('dddd D MMMM') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" x-data
                    x-on:click="document.querySelector('#saisie-express input')?.focus()"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3.5 py-2 text-xs font-semibold uppercase tracking-wide text-zinc-700 transition hover:border-secondary/50 hover:text-secondary">
                <x-lucide-zap class="h-4 w-4" />
                Nouvelle action
            </button>
            <button type="button" wire:click="$dispatch('open-quick-ticket')"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-primary/90">
                <x-lucide-plus class="h-4 w-4" />
                Nouveau ticket
            </button>
        </div>
    </div>

    <!-- Ma charge : 4 compteurs actionnables (cliquables → liste pré-filtrée) -->
    <div class="grid shrink-0 grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach ($this->counters as $card)
            <a href="{{ $card['href'] }}" wire:navigate
               class="group flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-3.5 transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md">
                <span @class([
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                    'bg-primary/10 text-primary' => $card['tone'] === 'primary',
                    'bg-secondary/10 text-secondary' => $card['tone'] === 'secondary',
                    'bg-emerald-500/10 text-emerald-600' => $card['tone'] === 'emerald',
                ])>
                    <x-dynamic-component :component="'lucide-'.$card['icon']" class="h-5 w-5" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-baseline gap-2">
                        <span class="text-2xl font-semibold leading-none tracking-tight tabular-nums text-zinc-900">{{ $card['value'] }}</span>
                        @if ($card['sub'])
                            <span class="truncate text-[11px] font-semibold text-red-600">{{ $card['sub'] }}</span>
                        @endif
                    </span>
                    <span class="mt-1 block truncate text-[11px] font-semibold uppercase tracking-wider text-zinc-400">{{ $card['label'] }}</span>
                </span>
                <x-lucide-arrow-up-right class="h-4 w-4 shrink-0 self-start text-zinc-300 transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-primary" />
            </a>
        @endforeach
    </div>

    <!-- Favoris (barre de chips) -->
    <livewire:admin.favorites />

    <!-- Saisie express d'une action -->
    <livewire:admin.quick-action />

    <!-- Zone flexible : tickets à traiter + colonne droite -->
    <div class="grid min-h-0 flex-1 grid-cols-1 gap-4 xl:grid-cols-3">
        {{-- Mes tickets à traiter (liste scrollable en interne sur xl) --}}
        <div class="flex min-h-0 flex-col rounded-2xl border border-zinc-200 bg-white xl:col-span-2">
            <div class="flex shrink-0 items-center justify-between border-b border-zinc-100 px-5 py-3">
                <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                    <x-lucide-list-checks class="h-3.5 w-3.5 text-primary" />
                    À traiter
                </h2>
                <a href="{{ route('admin.tickets') }}" wire:navigate
                   class="text-[11px] font-semibold text-primary hover:text-primary/70">Tous les tickets →</a>
            </div>

            <div class="divide-y divide-zinc-100 xl:min-h-0 xl:flex-1 xl:overflow-y-auto">
                @forelse ($this->myTickets as $ticket)
                    <a href="{{ route('admin.tickets', ['search' => $ticket->demande]) }}" wire:navigate
                       wire:key="dash-ticket-{{ $ticket->id }}"
                       class="flex items-center gap-3 px-5 py-2.5 transition hover:bg-secondary/[0.06]">
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
                        <span class="hidden w-20 shrink-0 rounded-full px-2.5 py-1 text-center text-[11px] font-semibold sm:inline-block"
                              style="background-color: {{ $ticket->importanceColor() }}1a; color: {{ $ticket->importanceColor() }}">
                            {{ $ticket->importanceLabel() }}
                        </span>
                        @if ($ticket->statut)
                            <span class="hidden shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold sm:inline-block"
                                  style="background-color: {{ $ticket->statut->color() }}1a; color: {{ $ticket->statut->color() }}">
                                {{ $ticket->statut->libelle }}
                            </span>
                        @endif
                        <span class="hidden w-16 shrink-0 text-right text-xs text-zinc-400 md:block">
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

        {{-- Colonne droite : crédits du mois + pense-bête --}}
        <div class="flex min-h-0 flex-col gap-4">
            <div class="shrink-0 rounded-2xl border border-zinc-200 bg-white px-5 py-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        <x-lucide-gauge class="h-3.5 w-3.5 text-secondary" />
                        Crédits du mois
                    </h2>
                    <a href="{{ route('admin.recap.actions') }}" wire:navigate
                       class="text-[11px] font-semibold text-primary hover:text-primary/70">Récap →</a>
                </div>

                <div class="space-y-3">
                    @forelse ($this->creditsContrats as $row)
                        <div wire:key="credit-{{ $row['contrat']->id }}">
                            <div class="flex items-baseline justify-between gap-3">
                                <a href="{{ route('admin.contrats.show', $row['contrat']) }}" wire:navigate
                                   class="truncate text-xs font-medium text-zinc-800 transition hover:text-primary">
                                    {{ $row['contrat']->libelle }}
                                </a>
                                <span @class([
                                    'shrink-0 text-[11px] tabular-nums',
                                    'font-semibold text-red-600' => $row['pct'] !== null && $row['pct'] > 100,
                                    'text-zinc-400' => $row['pct'] === null || $row['pct'] <= 100,
                                ])>
                                    {{ \App\Models\Action::formatHeures($row['temps']) }}@if($row['pct'] !== null) / {{ \App\Models\Action::formatHeures($row['credits']) }}@endif
                                </span>
                            </div>
                            @if ($row['pct'] !== null)
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-zinc-100">
                                    <div @class([
                                        'h-full rounded-full transition-all duration-500',
                                        'bg-red-500' => $row['pct'] > 100,
                                        'bg-secondary' => $row['pct'] >= 80 && $row['pct'] <= 100,
                                        'bg-primary' => $row['pct'] < 80,
                                    ]) style="width: {{ min(100, $row['pct']) }}%"></div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400">Aucun temps saisi ce mois-ci.</p>
                    @endforelse
                </div>
            </div>

            <livewire:admin.notepad />
        </div>
    </div>

    <!-- Toast de confirmation (fixe : ne décale pas la mise en page) -->
    @if($flash)
        <div x-data x-init="setTimeout(() => $wire.set('flash', null), 4000)"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-lg">
            <x-lucide-check-circle class="h-4 w-4 shrink-0" />
            {{ $flash }}
        </div>
    @endif

    <!-- Modal création express de ticket -->
    <livewire:admin.quick-ticket />
</div>
