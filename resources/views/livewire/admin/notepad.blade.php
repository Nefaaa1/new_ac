<div class="rounded-2xl border border-zinc-200 bg-white p-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-lucide-notebook-pen class="h-5 w-5 text-secondary" />
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Pense-bête</h2>
        </div>

        <span class="text-xs text-zinc-400">
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

    <textarea wire:model.live.debounce.800ms="content" rows="8"
              placeholder="Notez ici ce que vous ne voulez pas oublier…"
              class="mt-4 w-full resize-none rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 text-sm leading-relaxed text-zinc-700 placeholder-zinc-400 transition focus:border-primary focus:bg-white focus:outline-none focus:ring-0"></textarea>
</div>
