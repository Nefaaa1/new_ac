{{-- Barre de favoris façon navigateur : chips compactes, suppression au survol --}}
<div class="flex shrink-0 items-center gap-2 overflow-x-auto whitespace-nowrap [scrollbar-width:none]">
    <span class="flex shrink-0 items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
        <x-lucide-star class="h-3.5 w-3.5 text-secondary" />
        Favoris
    </span>

    @forelse($favorites as $favorite)
        <div wire:key="fav-{{ $favorite->id }}" class="group relative shrink-0">
            <a href="{{ route($favorite->route_name) }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white py-1.5 pl-3 pr-3 text-xs font-medium text-zinc-700 transition hover:border-secondary/50 hover:text-zinc-900 group-hover:pr-7">
                <x-dynamic-component :component="'lucide-'.$favorite->icon" class="h-3.5 w-3.5 text-secondary" />
                {{ $favorite->label }}
            </a>
            <button wire:click="remove({{ $favorite->id }})" type="button" title="Retirer des favoris"
                    class="absolute right-1.5 top-1/2 hidden h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full text-zinc-400 hover:bg-red-50 hover:text-red-600 group-hover:flex">
                <x-lucide-x class="h-3 w-3" />
            </button>
        </div>
    @empty
        <span class="text-xs text-zinc-400">
            Aucun favori — cliquez sur l'étoile en haut d'une page pour l'ajouter ici.
        </span>
    @endforelse
</div>
