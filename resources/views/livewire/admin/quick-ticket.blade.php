{{-- Modal centré : création express d'un ticket --}}
<div x-data="{ open: @entangle('show') }" x-show="open" x-cloak
     x-on:keydown.escape.window="$wire.close()" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div x-show="open" x-transition.opacity.duration.200ms
         class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" x-on:click="$wire.close()"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl">

        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="flex items-center gap-2 text-base font-semibold text-zinc-900">
                <x-lucide-ticket class="h-5 w-5 text-primary" />
                Nouveau ticket
            </h2>
            <button wire:click="close" type="button" class="text-zinc-400 hover:text-zinc-600">
                <x-lucide-x class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit="save">
            <div class="space-y-5 border-t border-zinc-100 px-6 py-5">
                <x-text-input label="Demande" name="demande" required floatError wire:model="demande"
                              placeholder="Ex. Corriger le formulaire de contact" />

                <x-textarea label="Descriptif" name="descriptif" rows="2" wire:model="descriptif"
                            placeholder="Détails de la demande…" />

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <div class="relative">
                        <livewire:admin.site-picker wire:model="site_id" :required="true" :key="'qt-site-picker-'.$formNonce" />
                        @error('site_id')
                            <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-date-input label="Date" name="date" model="date" required floatError />
                </div>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <x-select label="Importance" name="importance" required floatError wire:model="importance">
                        @foreach(\App\Models\Ticket::IMPORTANCES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-select>

                    <x-select label="Attribuer à" name="assigne" required floatError wire:model="assigne">
                        <option value="">— Sélectionner —</option>
                        @if($this->equipesList->isNotEmpty())
                            <optgroup label="Équipes">
                                @foreach($this->equipesList as $equipe)
                                    <option value="e:{{ $equipe->id }}">{{ $equipe->nom }} ({{ $equipe->members_count }})</option>
                                @endforeach
                            </optgroup>
                        @endif
                        <optgroup label="Administrateurs">
                            @foreach($this->adminsList as $admin)
                                <option value="u:{{ $admin->id }}">{{ $admin->name }}</option>
                            @endforeach
                        </optgroup>
                    </x-select>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-zinc-100 px-6 py-4">
                <p class="text-[11px] text-zinc-400">
                    <button wire:click="close" type="button" class="text-sm text-zinc-500 hover:text-zinc-700">Annuler</button> 
                </p>
                <div class="flex shrink-0 items-center gap-3">
                    
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
                        <x-lucide-check class="h-4 w-4" />
                        Créer le ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
