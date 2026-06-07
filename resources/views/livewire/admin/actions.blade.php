<div>
    <x-admin.page-header title="Actions" subtitle="Suivi des actions réalisées par contrat." icon="zap" />

    {{-- Barre d'outils : recherche + action --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
            <x-text-input
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher intitulé, contrat…"
                class="!pl-11 !pr-11" />
            @if($search !== '')
                <button wire:click="$set('search', '')" type="button" title="Effacer"
                        class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                    <x-lucide-x class="h-4 w-4" />
                </button>
            @endif
        </div>

        <button wire:click="create" type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Nouvelle action
        </button>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="date" label="Date" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="intitule" label="Intitulé" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="type" label="Type" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Contrat</th>
                    <x-admin.sort-header field="temps" label="Temps" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->actions as $action)
                    <tr wire:key="action-{{ $action->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3 whitespace-nowrap text-zinc-600">{{ $action->date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-zinc-900">{{ $action->intitule }}</p>
                            @if($action->commentaire)
                                <p class="truncate text-xs text-zinc-400" title="{{ $action->commentaire }}">{{ $action->commentaire }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                                {{ $action->typeLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($action->contrat && ! $action->contrat->trashed())
                                <a href="{{ route('admin.contrats.show', $action->contrat) }}" wire:navigate
                                   class="group inline-flex items-center gap-1.5 text-zinc-700 hover:text-primary">
                                    <x-lucide-file-text class="h-3.5 w-3.5 text-secondary" />
                                    <span class="truncate">{{ $action->contrat->libelle }}</span>
                                </a>
                            @elseif($action->contrat)
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-zinc-500">
                                        <x-lucide-file-text class="h-3.5 w-3.5 text-zinc-300" />
                                        <span class="truncate line-through decoration-red-300">{{ $action->contrat->libelle }}</span>
                                    </span>
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600"
                                          title="Le contrat lié a été supprimé">
                                        <x-lucide-triangle-alert class="h-3 w-3" /> Contrat supprimé
                                    </span>
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-zinc-600">
                            <span class="inline-flex items-center gap-1.5">
                                <x-lucide-clock class="h-3.5 w-3.5 text-secondary" />
                                {{ $action->tempsLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editAction({{ $action->id }})" type="button" title="Modifier"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>
                                <button wire:click="deleteAction({{ $action->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer cette action ?"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-zinc-400">
                            {{ $search !== '' ? 'Aucune action ne correspond à « '.$search.' ».' : 'Aucune action pour le moment.' }}
                        </td>
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
                    {{ $editingId ? "Modifier l'action" : 'Nouvelle action' }}
                </h2>
                <button wire:click="closeForm" type="button" class="text-zinc-400 hover:text-zinc-600">
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <form wire:submit="save" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <x-text-input label="Intitulé" name="intitule" floatError wire:model="intitule" />

                    <div class="grid grid-cols-2 items-start gap-4">
                        <x-date-input label="Date" name="date" model="date" floatError />
                        <x-text-input label="Temps (heures)" name="temps" type="number" step="0.25" min="0"
                                      floatError wire:model="temps" placeholder="1.5" />
                    </div>

                    <x-select label="Type d'action" name="type" floatError wire:model="type">
                        <option value="">— Sélectionner —</option>
                        @foreach(\App\Models\Action::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-select>

                    {{-- Autocomplétion contrat (composant réutilisable) --}}
                    <div>
                        @if($contratTrashed)
                            <div class="mb-2 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                                <x-lucide-triangle-alert class="mt-0.5 h-4 w-4 shrink-0" />
                                <span>Le contrat actuellement lié a été <strong>supprimé</strong>. Vous pouvez conserver l'action telle quelle ou la rattacher à un autre contrat.</span>
                            </div>
                        @endif
                        <div class="relative">
                            <livewire:admin.contrat-picker wire:model="contrat_id" :key="'contrat-picker-'.$formNonce" />
                            @error('contrat_id')
                                <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Commentaire (facultatif)</label>
                        <textarea wire:model="commentaire" rows="3"
                                  class="w-full resize-none rounded-[10px] border-[2px] border-primary bg-transparent px-5 py-2.5 text-sm text-gray-600 placeholder-gray-400 transition focus:border-secondary focus:outline-none focus:ring-0"></textarea>
                    </div>
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
