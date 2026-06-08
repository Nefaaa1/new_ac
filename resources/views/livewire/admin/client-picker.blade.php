<div class="relative" x-data="{ open: @entangle('showResults') }" @click.outside="open = false">
    <x-field-label :label="$label" :required="$required" />

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
                'border-primary focus:border-secondary' => ! $clientId,
                'border-emerald-500 focus:border-emerald-500' => $clientId,
            ]) />

        {{-- Sélection validée : pastille verte ; sinon bouton effacer si du texte --}}
        @if($clientId)
            <span class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-emerald-500" title="Client sélectionné">
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
                @forelse($this->results as $client)
                    <button wire:click="selectClient({{ $client->id }})" type="button" @click="open = false"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-primary/5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-lucide-building-2 class="h-4 w-4" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-zinc-900">{{ $client->societe ?: $client->user?->name }}</span>
                            @if($client->societe && $client->user)
                                <span class="block truncate text-xs text-zinc-400">{{ $client->user->name }}</span>
                            @endif
                        </span>
                    </button>
                @empty
                    <p class="px-4 py-3 text-sm text-zinc-500">Aucun client ne correspond.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>
