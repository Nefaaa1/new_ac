<div>
    <x-admin.page-header title="Sites" subtitle="Sites web gérés (hébergement, FTP, base de données, WordPress)." icon="globe" />

    {{-- Barre d'outils : recherche + action --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
            <x-text-input
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un site, une société…"
                class="!pl-11 !pr-11" />
            @if($search !== '')
                <button wire:click="$set('search', '')" type="button" title="Effacer"
                        class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                    <x-lucide-x class="h-4 w-4" />
                </button>
            @endif
        </div>

        <a href="{{ route('admin.sites.create') }}" wire:navigate
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Nouveau site
        </a>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="nom" label="Site" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Client</th>
                    <th class="px-5 py-3.5">Boutique</th>
                    <th class="px-5 py-3.5">Statut</th>
                    <x-admin.sort-header field="date_statut" label="Date statut" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->sites as $site)
                    <tr wire:key="site-{{ $site->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.sites.show', $site) }}" wire:navigate class="flex items-center gap-3 group">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 text-primary">
                                    <x-lucide-globe class="h-4 w-4" />
                                </span>
                                <span class="font-medium text-zinc-900 group-hover:text-primary">{{ $site->nom }}</span>
                            </a>
                        </td>
                        <td class="px-5 py-3">
                            @if($site->client)
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-sm font-medium text-zinc-800">
                                    <x-lucide-building-2 class="h-3.5 w-3.5 text-primary" />
                                    {{ $site->client->societe ?: $site->client->user?->name }}
                                </span>
                            @else
                                <span class="text-sm text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($site->boutique_en_ligne)
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-secondary/10 px-2 py-0.5 text-xs font-semibold text-secondary">
                                    <x-lucide-shopping-cart class="h-3.5 w-3.5" /> En ligne
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($site->statut)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                      style="background-color: {{ $site->statut->color() }}1a; color: {{ $site->statut->color() }}">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $site->statut->color() }}"></span>
                                    {{ $site->statut->libelle }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ $site->date_statut?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.sites.edit', $site) }}" wire:navigate title="Modifier"
                                   class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </a>
                                <button wire:click="deleteSite({{ $site->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer ce site ? Les accès (hébergement, FTP, BDD, WordPress) seront aussi supprimés."
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-zinc-400">
                            {{ $search !== '' ? 'Aucun site ne correspond à « '.$search.' ».' : 'Aucun site pour le moment.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
