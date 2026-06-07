<div class="relative w-full max-w-md" x-data="{ open: false }">
    <div class="relative">
        <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />

        <input type="text" wire:model.live.debounce.300ms="term"
               @focus="open = true" @click.outside="open = false" @keydown.escape="open = false; $event.target.blur()"
               placeholder="Rechercher un client, un site, un contrat…"
               class="w-full rounded-full border border-zinc-200 bg-zinc-50 py-2 pl-9 pr-9 text-sm text-zinc-700 placeholder:text-zinc-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-0">

        @if($hasQuery)
            <button wire:click="clear" @click="open = false" type="button" title="Effacer"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                <x-lucide-x class="h-4 w-4" />
            </button>
        @endif
    </div>

    @if($hasQuery)
        <div x-show="open" x-cloak x-transition.origin.top
             class="absolute left-0 right-0 z-30 mt-2 max-h-[70vh] overflow-y-auto rounded-2xl border border-zinc-200 bg-white py-2 shadow-lg">

            {{-- Décompte réel (avant plafonnement) --}}
            <p class="px-4 py-2 text-xs text-zinc-400">
                @if($total === 0)
                    Aucun résultat
                @else
                    {{ $total }} résultat{{ $total > 1 ? 's' : '' }}
                    @if($total > $limit)
                        <span class="text-zinc-300">— {{ $limit }} affichés</span>
                    @endif
                @endif
            </p>

            @foreach($groups as $group => $results)
                <div class="px-2 pt-1">
                    <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">{{ $group }}</p>

                    @foreach($results as $result)
                        <a href="{{ $result->url }}" wire:navigate @click="open = false"
                           class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-zinc-50">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <x-dynamic-component :component="'lucide-'.$result->icon" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-zinc-800">{{ $result->label }}</span>
                                @if($result->sublabel)
                                    <span class="block truncate text-xs text-zinc-400">{{ $result->sublabel }}</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
</div>
