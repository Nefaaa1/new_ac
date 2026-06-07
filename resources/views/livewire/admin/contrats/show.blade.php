<div x-data="{ tab: 'general' }">
    {{-- En-tête : retour + titre + actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.contrats') }}" wire:navigate
               class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-800">
                <x-lucide-arrow-left class="h-5 w-5" />
            </a>
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <x-lucide-file-text class="h-6 w-6" />
                </span>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ $contrat->libelle }}</h1>
                    <p class="text-sm text-zinc-500">
                        @if($contrat->client)
                            {{ $contrat->client->societe ?: $contrat->client->user?->name }}
                        @else
                            Contrat sans client rattaché
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.contrats.edit', $contrat) }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
                <x-lucide-pencil class="h-4 w-4" />
                Modifier
            </a>
            <button wire:click="deleteContrat" type="button"
                    wire:confirm="Supprimer ce contrat ? Les comptes réseaux associés seront aussi supprimés."
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                <x-lucide-trash-2 class="h-4 w-4" />
                Supprimer
            </button>
        </div>
    </div>

    {{-- Onglets --}}
    <div class="mt-6 border-b border-zinc-200">
        <nav class="-mb-px flex gap-6">
            <button type="button" @click="tab = 'general'"
                    :class="tab === 'general' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-file-text class="h-4 w-4" />
                Général
            </button>
            <button type="button" @click="tab = 'reseaux'"
                    :class="tab === 'reseaux' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-share-2 class="h-4 w-4" />
                Réseaux sociaux
                @if($contrat->reseaux->count())
                    <span class="rounded-full bg-secondary/15 px-2 py-0.5 text-xs font-semibold text-secondary">{{ $contrat->reseaux->count() }}</span>
                @endif
            </button>
        </nav>
    </div>

    {{-- Onglet Général --}}
    <div x-show="tab === 'general'" class="mt-6 space-y-6">
        {{-- Chiffres clés mis en avant --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                    <x-lucide-badge-check class="h-4 w-4 text-primary" /> Type
                </div>
                <p class="mt-2 text-base font-semibold text-zinc-900">{{ $contrat->typeLabel() ?? '—' }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                    <x-lucide-repeat-2 class="h-4 w-4 text-primary" /> Facturation
                </div>
                <p class="mt-2 text-base font-semibold text-zinc-900">{{ $contrat->cycleLabel() ?? '—' }}</p>
            </div>

            <div class="rounded-2xl border border-secondary/30 bg-gradient-to-br from-secondary/[0.12] to-secondary/[0.04] p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-secondary">
                    <x-lucide-circle-dollar-sign class="h-4 w-4" /> Taux horaire
                </div>
                <p class="mt-2 text-2xl font-bold text-zinc-900">
                    {{ $contrat->taux_horaire !== null ? number_format((float) $contrat->taux_horaire, 2, ',', ' ').' €' : '—' }}
                </p>
            </div>

            <div class="rounded-2xl border border-primary/30 bg-gradient-to-br from-primary/[0.12] to-primary/[0.04] p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-primary">
                    <x-lucide-coins class="h-4 w-4" /> Crédits
                </div>
                <p class="mt-2 text-2xl font-bold text-zinc-900">
                    {{ $contrat->credits !== null ? $contrat->credits : '—' }}<span class="ml-1 text-sm font-medium text-zinc-400">{{ $contrat->credits !== null ? 'h' : '' }}</span>
                </p>
            </div>
        </div>

        {{-- Détails --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-100 px-6 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Détails</h2>
            </div>
            <dl class="divide-y divide-zinc-100">
                <div class="flex items-center gap-4 px-6 py-4">
                    <dt class="flex w-40 shrink-0 items-center gap-2 text-sm text-zinc-500">
                        <x-lucide-building-2 class="h-4 w-4 text-zinc-400" /> Client
                    </dt>
                    <dd class="text-sm font-medium text-zinc-900">
                        @if($contrat->client)
                            <a href="{{ route('admin.clients', ['open' => $contrat->client->user_id]) }}" wire:navigate
                               class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-primary transition hover:bg-primary/15">
                                {{ $contrat->client->societe ?: $contrat->client->user?->name }}
                            </a>
                        @else <span class="text-zinc-400">— Aucun client rattaché</span> @endif
                    </dd>
                </div>

                <div class="flex items-center gap-4 px-6 py-4">
                    <dt class="flex w-40 shrink-0 items-center gap-2 text-sm text-zinc-500">
                        <x-lucide-globe class="h-4 w-4 text-zinc-400" /> Site web
                    </dt>
                    <dd class="text-sm font-medium text-zinc-900">
                        @if($contrat->site_web)
                            <a href="{{ $contrat->site_web }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 text-primary hover:underline">
                                {{ $contrat->site_web }}
                                <x-lucide-external-link class="h-3.5 w-3.5" />
                            </a>
                        @else <span class="text-zinc-400">—</span> @endif
                    </dd>
                </div>

                <div class="flex items-center gap-4 px-6 py-4">
                    <dt class="flex w-40 shrink-0 items-center gap-2 text-sm text-zinc-500">
                        <x-lucide-calendar class="h-4 w-4 text-zinc-400" /> Période
                    </dt>
                    <dd class="text-sm font-medium text-zinc-900">
                        {{ $contrat->date_debut?->format('d/m/Y') ?? '—' }}
                        <span class="mx-1.5 text-zinc-300">→</span>
                        {{ $contrat->date_fin?->format('d/m/Y') ?? 'En cours' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Onglet Réseaux sociaux --}}
    <div x-show="tab === 'reseaux'" x-cloak class="mt-6 space-y-4">
        @forelse($contrat->reseaux as $reseau)
            <div wire:key="show-reseau-{{ $reseau->id }}"
                 x-data="{ show: false }"
                 class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center">
                <div class="flex items-center gap-3 sm:w-48 sm:shrink-0">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-dynamic-component :component="'lucide-'.$reseau->reseauIcon()" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="font-semibold text-zinc-900">{{ $reseau->reseauLabel() }}</p>
                        @if($reseau->gestionLabel())
                            <span @class([
                                'mt-0.5 inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                'bg-primary/10 text-primary' => $reseau->gestion === 'agence',
                                'bg-secondary/15 text-secondary' => $reseau->gestion === 'client',
                            ])>Gestion : {{ $reseau->gestionLabel() }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Identifiant</p>
                        <p class="mt-1 select-all text-sm text-zinc-800">{{ $reseau->identifiant ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Mot de passe</p>
                        @if($reseau->mot_de_passe)
                            <div class="mt-1 flex items-center gap-2">
                                <span x-show="!show" class="font-mono text-sm tracking-widest text-zinc-400">••••••••</span>
                                <span x-show="show" x-cloak class="select-all font-mono text-sm text-zinc-800">{{ $reseau->mot_de_passe }}</span>
                                <button type="button" @click="show = ! show" class="text-zinc-400 transition hover:text-primary">
                                    <x-lucide-eye x-show="!show" class="h-4 w-4" />
                                    <x-lucide-eye-off x-show="show" x-cloak class="h-4 w-4" />
                                </button>
                            </div>
                        @else
                            <p class="mt-1 text-sm text-zinc-400">—</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center">
                <x-lucide-share-2 class="mx-auto h-8 w-8 text-zinc-300" />
                <p class="mt-3 text-sm text-zinc-500">Aucun réseau social rattaché à ce contrat.</p>
                <a href="{{ route('admin.contrats.edit', $contrat) }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                    <x-lucide-plus class="h-4 w-4" /> En ajouter
                </a>
            </div>
        @endforelse
    </div>
</div>
