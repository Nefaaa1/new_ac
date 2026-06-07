<div>
    <div class="flex items-center gap-2">
        <x-lucide-star class="h-5 w-5 text-secondary" />
        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Favoris</h2>
    </div>

    @if($favorites->isEmpty())
        <div class="mt-3 rounded-2xl border border-dashed border-zinc-300 bg-white p-6 text-center">
            <p class="text-sm text-zinc-500">Aucun favori pour le moment.</p>
            <p class="mt-1 text-xs text-zinc-400">
                Cliquez sur l'étoile <x-lucide-star class="inline h-3.5 w-3.5 align-text-bottom" />
                en haut d'une page pour l'ajouter ici.
            </p>
        </div>
    @else
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($favorites as $favorite)
                <div wire:key="fav-{{ $favorite->id }}"
                     class="group relative rounded-xl border border-zinc-200 bg-white transition hover:border-secondary/40 hover:shadow-sm">
                    @if($editingId === $favorite->id)
                        <div class="p-3">
                            <input type="text" wire:model="editingLabel" wire:keydown.enter="update"
                                   class="w-full rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-700 focus:border-primary focus:outline-none focus:ring-0">
                            @error('editingLabel')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="mt-2 flex items-center justify-end gap-3">
                                <button wire:click="cancel" type="button" class="text-xs text-zinc-500 hover:text-zinc-700">Annuler</button>
                                <button wire:click="update" type="button" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary/90">Enregistrer</button>
                            </div>
                        </div>
                    @else
                        <a href="{{ route($favorite->route_name) }}" wire:navigate
                           class="flex items-center gap-3 p-4 pr-16">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-secondary/10 text-secondary">
                                <x-dynamic-component :component="'lucide-'.$favorite->icon" class="h-5 w-5" />
                            </span>
                            <span class="truncate text-sm font-medium text-zinc-800">{{ $favorite->label }}</span>
                        </a>

                        <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-1 opacity-0 transition group-hover:opacity-100">
                            <button wire:click="edit({{ $favorite->id }})" type="button" title="Renommer"
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700">
                                <x-lucide-pencil class="h-4 w-4" />
                            </button>
                            <button wire:click="remove({{ $favorite->id }})" type="button" title="Supprimer"
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                <x-lucide-trash-2 class="h-4 w-4" />
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
