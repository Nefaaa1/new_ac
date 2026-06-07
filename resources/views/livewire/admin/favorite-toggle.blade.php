<div class="relative" x-data="{ open: false }">
    @if($this->favorite)
        <button wire:click="remove" type="button" title="Retirer des favoris"
                class="flex h-9 w-9 items-center justify-center rounded-full text-secondary transition hover:bg-secondary/10">
            <x-lucide-star class="h-5 w-5 fill-current" />
        </button>
    @else
        <button @click="open = ! open" type="button" title="Ajouter aux favoris"
                class="flex h-9 w-9 items-center justify-center rounded-full text-zinc-400 transition hover:bg-zinc-100 hover:text-secondary">
            <x-lucide-star class="h-5 w-5" />
        </button>

        <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
             class="absolute right-0 z-30 mt-2 w-64 rounded-xl border border-zinc-200 bg-white p-3 shadow-lg">
            <label class="text-xs font-medium text-zinc-500">Nom du favori</label>
            <input type="text" wire:model="label" wire:keydown.enter="add" x-init="$nextTick(() => {})"
                   class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-700 focus:border-primary focus:outline-none focus:ring-0">
            @error('label')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-3 flex items-center justify-end gap-3">
                <button @click="open = false" type="button" class="text-xs text-zinc-500 hover:text-zinc-700">
                    Annuler
                </button>
                <button wire:click="add" type="button"
                        class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary/90">
                    <x-lucide-star class="h-3.5 w-3.5" />
                    Ajouter
                </button>
            </div>
        </div>
    @endif
</div>
