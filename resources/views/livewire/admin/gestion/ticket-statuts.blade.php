<div>
    <x-admin.page-header
        title="Statuts tickets"
        subtitle="Définir les statuts de workflow des tickets (libellé, couleur, ordre)."
        icon="ticket" />

    {{-- Barre d'action --}}
    <div class="mt-6 flex justify-end">
        <button wire:click="create" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Nouveau statut
        </button>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="position" label="Ordre" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="libelle" label="Statut" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Clôture</th>
                    <x-admin.sort-header field="tickets" label="Tickets" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->statuts as $statut)
                    <tr wire:key="ticket-statut-{{ $statut->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3 text-zinc-500">{{ $statut->position }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-2.5 font-medium text-zinc-900">
                                <span class="h-3 w-3 shrink-0 rounded-full ring-2 ring-white" style="background-color: {{ $statut->color() }}"></span>
                                {{ $statut->libelle }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($statut->cloture)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-600">
                                    <x-lucide-flag class="h-3.5 w-3.5" /> Clôture
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ $statut->tickets_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editStatut({{ $statut->id }})" type="button" title="Modifier"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>
                                <button wire:click="deleteStatut({{ $statut->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer ce statut ? Les tickets qui le portent n'auront plus de statut affiché."
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-zinc-400">Aucun statut pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Panneau latéral création / édition --}}
    <div x-data="{ open: @entangle('showForm') }" x-show="open" x-cloak
         x-on:keydown.escape.window="$wire.closeForm()" class="fixed inset-0 z-40">
        <div x-show="open" x-transition.opacity.duration.200ms
             class="absolute inset-0 bg-zinc-900/40" x-on:click="$wire.closeForm()"></div>

        <div x-show="open"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-xl">
            <div class="flex shrink-0 items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="text-base font-semibold text-zinc-900">
                    {{ $editingId ? 'Modifier le statut' : 'Nouveau statut' }}
                </h2>
                <button wire:click="closeForm" type="button" class="text-zinc-400 hover:text-zinc-600">
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <form wire:submit="save" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <x-text-input label="Libellé" name="libelle" required floatError wire:model="libelle"
                                  placeholder="Ex. À faire, En cours…" />

                    {{-- Couleur --}}
                    <div>
                        <x-field-label label="Couleur" :required="true" />
                        <div class="flex items-center gap-3">
                            <input type="color" wire:model.live="couleur"
                                   class="h-11 w-14 shrink-0 cursor-pointer rounded-[10px] border-[2px] border-primary bg-white p-1" />
                            <x-text-input name="couleur" floatError wire:model.live="couleur" class="font-mono uppercase" />
                        </div>
                    </div>

                    <x-text-input label="Ordre d'affichage" name="position" type="number" min="0"
                                  floatError wire:model="position" placeholder="0" />

                    <x-checkbox wire:model="cloture"
                                label="Statut de clôture"
                                hint="Statut terminal du ticket (un seul). Cible du bouton « Terminer »." />
                </div>

                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-zinc-100 px-6 py-4">
                    <button wire:click="closeForm" type="button" class="text-sm text-zinc-500 hover:text-zinc-700">Annuler</button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
                        <x-lucide-check class="h-4 w-4" />
                        {{ $editingId ? 'Enregistrer' : 'Créer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
