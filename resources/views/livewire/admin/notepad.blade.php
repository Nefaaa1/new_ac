{{-- Pense-bête : compact, absorbe l'élasticité de la colonne droite sur xl --}}
<div class="flex min-h-0 flex-col rounded-2xl border border-zinc-200 bg-white p-4 xl:flex-1">
    <div class="flex shrink-0 items-center justify-between">
        <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
            <x-lucide-notebook-pen class="h-3.5 w-3.5 text-secondary" />
            Pense-bête
        </h2>

        <span class="text-[11px] text-zinc-400">
            <span wire:loading.delay wire:target="content" class="flex items-center gap-1">
                <x-lucide-loader-circle class="h-3.5 w-3.5 animate-spin" />
                Enregistrement…
            </span>
            <span wire:loading.remove wire:target="content" class="flex items-center gap-1 text-emerald-500">
                <x-lucide-check class="h-3.5 w-3.5" />
                Enregistré
            </span>
        </span>
    </div>

    <textarea wire:model.live.debounce.800ms="content" rows="5"
              placeholder="Notez ici ce que vous ne voulez pas oublier…"
              class="mt-3 w-full resize-none rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 text-xs leading-relaxed text-zinc-700 placeholder-zinc-400 transition focus:border-primary focus:bg-white focus:outline-none focus:ring-0 xl:min-h-0 xl:flex-1"></textarea>
</div>
