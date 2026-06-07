<div class="relative" x-data="{ open: @entangle('showResults') }" @click.outside="open = false">
    @if($label)
        <label class="mb-1 block truncate text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <div class="relative">
        <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />

        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            @focus="open = true"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            @class([
                'w-full bg-transparent border-[2px] rounded-[10px] text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-0 transition px-5 py-2.5 pl-11 pr-11 text-sm',
                'border-primary focus:border-secondary' => ! $contratId,
                'border-emerald-500 focus:border-emerald-500' => $contratId,
            ]) />

        {{-- Sélection validée : pastille verte ; sinon bouton effacer si du texte --}}
        @if($contratId)
            <span class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-emerald-500" title="Contrat sélectionné">
                <x-lucide-check-circle class="h-4 w-4" />
            </span>
        @elseif($search !== '')
            <button wire:click="clearSelection" type="button" title="Effacer"
                    class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                <x-lucide-x class="h-4 w-4" />
            </button>
        @endif
    </div>

    {{-- Menu déroulant --}}
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms
         class="absolute z-30 mt-1 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg">
        @if(mb_strlen(trim($search)) < 2)
            <p class="px-4 py-3 text-sm text-zinc-400">Tapez au moins 2 caractères…</p>
        @else
            <div class="max-h-64 overflow-y-auto">
                @forelse($this->results as $contrat)
                    <button wire:click="selectContrat({{ $contrat->id }})" type="button" @click="open = false"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-primary/5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-lucide-file-text class="h-4 w-4" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-zinc-900">{{ $contrat->libelle }}</span>
                            @if($contrat->client)
                                <span class="block truncate text-xs text-zinc-400">{{ $contrat->client->societe ?: $contrat->client->user?->name }}</span>
                            @endif
                        </span>
                    </button>
                @empty
                    <p class="px-4 py-3 text-sm text-zinc-500">Aucun contrat ne correspond.</p>
                @endforelse
            </div>

            {{-- Proposition de création (toujours dispo si recherche active) --}}
            <button wire:click="createContrat" type="button"
                    class="flex w-full items-center gap-2 border-t border-zinc-100 bg-zinc-50 px-4 py-2.5 text-left text-sm font-medium text-primary transition hover:bg-primary/5">
                <x-lucide-plus class="h-4 w-4 shrink-0" />
                <span class="truncate">Créer le contrat «&nbsp;{{ trim($search) }}&nbsp;»</span>
            </button>
        @endif
    </div>
</div>
