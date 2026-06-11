{{-- Saisie express d'une action : une rangée, boucle clavier (re-focus du contrat après save) --}}
<section id="saisie-express" class="shrink-0 rounded-2xl border border-zinc-200 bg-white"
         x-data x-on:action-saved.window="setTimeout(() => $el.querySelector('input')?.focus(), 100)">
    <div class="rounded-b-2xl bg-gradient-to-br from-secondary/[0.04] to-transparent px-5 pb-5 pt-3">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                <x-lucide-zap class="h-3.5 w-3.5 text-secondary" />
                Saisie express
            </h2>
            <a href="{{ route('admin.actions') }}" wire:navigate
               class="text-[11px] font-semibold text-primary hover:text-primary/70">Toutes les actions →</a>
        </div>

        <form wire:submit="save" class="grid grid-cols-2 items-end gap-x-3 gap-y-4 lg:grid-cols-12">
            <div class="relative col-span-2 lg:col-span-3">
                <livewire:admin.contrat-picker wire:model="contrat_id" label="Contrat" required
                                               :key="'qa-picker-'.$pickerNonce" />
                @error('contrat_id')
                    <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2 lg:col-span-3">
                <x-text-input label="Intitulé" name="intitule" required floatError wire:model="intitule"
                              placeholder="Ce que tu as fait…" />
            </div>

            <div class="lg:col-span-2">
                <x-select label="Type" name="type" required floatError wire:model="type">
                    <option value="">—</option>
                    @foreach(\App\Models\Action::TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="lg:col-span-1">
                <x-text-input label="Temps" name="temps" type="number" step="0.25" min="0" required floatError
                              wire:model="temps" placeholder="1.5" />
            </div>

            <div class="lg:col-span-2">
                <x-date-input label="Date" name="date" model="date" required floatError />
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    title="Enregistrer l'action (Entrée)"
                    class="flex h-11 items-center justify-center rounded-[10px] bg-secondary text-white transition hover:bg-secondary/90 disabled:opacity-60 lg:col-span-1">
                <x-lucide-corner-down-left class="h-5 w-5" />
                <span class="sr-only">Enregistrer l'action</span>
            </button>
        </form>
    </div>
</section>
