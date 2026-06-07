<div>
    <x-admin.page-header title="Contrats" subtitle="Suivi des contrats clients." icon="file-text" />

    {{-- Barre d'outils : recherche + action --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
            <x-text-input
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher libellé, site, société…"
                class="!pl-11 !pr-11" />
            @if($search !== '')
                <button wire:click="$set('search', '')" type="button" title="Effacer"
                        class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                    <x-lucide-x class="h-4 w-4" />
                </button>
            @endif
        </div>

        <a href="{{ route('admin.contrats.create') }}" wire:navigate
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Nouveau contrat
        </a>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="libelle" label="Libellé" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Client</th>
                    <x-admin.sort-header field="type" label="Type" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="cycle" label="Facturation" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="credits" label="Crédits" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="date_debut" label="Début" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Réseaux</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->contrats as $contrat)
                    <tr wire:key="contrat-{{ $contrat->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.contrats.show', $contrat) }}" wire:navigate
                               class="flex items-center gap-3 group">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 text-primary">
                                    <x-lucide-file-text class="h-4 w-4" />
                                </span>
                                <span class="font-medium text-zinc-900 group-hover:text-primary">{{ $contrat->libelle }}</span>
                            </a>
                        </td>
                        <td class="px-5 py-3">
                            @if($contrat->client)
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-sm font-medium text-zinc-800">
                                    <x-lucide-building-2 class="h-3.5 w-3.5 text-primary" />
                                    {{ $contrat->client->societe ?: $contrat->client->user?->name }}
                                </span>
                            @else
                                <span class="text-sm text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ $contrat->typeLabel() ?? '—' }}</td>
                        <td class="px-5 py-3 text-zinc-600">{{ $contrat->cycleLabel() ?? '—' }}</td>
                        <td class="px-5 py-3 text-zinc-600">
                            @if($contrat->credits !== null)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-lucide-coins class="h-3.5 w-3.5 text-secondary" />
                                    {{ $contrat->credits }} h
                                </span>
                            @else — @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ $contrat->date_debut?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-zinc-600">
                            @if($contrat->reseaux_count)
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-secondary/10 px-2 py-0.5 text-xs font-semibold text-secondary">
                                    <x-lucide-share-2 class="h-3.5 w-3.5" />
                                    {{ $contrat->reseaux_count }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.contrats.edit', $contrat) }}" wire:navigate title="Modifier"
                                   class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </a>
                                <button wire:click="deleteContrat({{ $contrat->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer ce contrat ? Les comptes réseaux associés seront aussi supprimés."
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-zinc-400">
                            {{ $search !== '' ? 'Aucun contrat ne correspond à « '.$search.' ».' : 'Aucun contrat pour le moment.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
