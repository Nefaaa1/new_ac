@php
    // Données admins pour le drag & drop (Alpine) : id + nom + initiales.
    $adminsData = $this->adminsList->map(fn ($a) => [
        'id' => $a->id,
        'name' => $a->name,
        'initials' => mb_strtoupper(mb_substr($a->prenom, 0, 1).mb_substr($a->nom, 0, 1)),
    ])->values();
@endphp

<div>
    <x-admin.page-header
        title="Équipes"
        subtitle="Regrouper des administrateurs pour leur attribuer des tickets."
        icon="users-round" />

    {{-- Barre d'action --}}
    <div class="mt-6 flex justify-end">
        <button wire:click="create" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Nouvelle équipe
        </button>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="nom" label="Équipe" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Membres</th>
                    <x-admin.sort-header field="members" label="Nombre" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->equipes as $equipe)
                    <tr wire:key="equipe-{{ $equipe->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                                  style="background-color: {{ $equipe->color() }}1a; color: {{ $equipe->color() }}">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $equipe->color() }}"></span>
                                {{ $equipe->nom }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($equipe->members->isEmpty())
                                <span class="text-zinc-300">—</span>
                            @else
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($equipe->members as $member)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 py-0.5 pl-0.5 pr-2 text-xs text-zinc-700">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-[9px] font-semibold text-primary">
                                                {{ strtoupper(mb_substr($member->prenom, 0, 1).mb_substr($member->nom, 0, 1)) }}
                                            </span>
                                            {{ $member->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ $equipe->members_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editEquipe({{ $equipe->id }})" type="button" title="Modifier"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>
                                <button wire:click="deleteEquipe({{ $equipe->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer cette équipe ? Les tickets attribués n'auront plus d'équipe affichée."
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-zinc-400">Aucune équipe pour le moment.</td>
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
             class="absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col bg-white shadow-xl">
            <div class="flex shrink-0 items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="text-base font-semibold text-zinc-900">
                    {{ $editingId ? "Modifier l'équipe" : 'Nouvelle équipe' }}
                </h2>
                <button wire:click="closeForm" type="button" class="text-zinc-400 hover:text-zinc-600">
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <form wire:submit="save" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-[1fr_auto]">
                        <x-text-input label="Nom de l'équipe" name="nom" required floatError wire:model="nom"
                                      placeholder="Ex. Support, Développement…" />
                        <div>
                            <x-field-label label="Couleur" :required="true" />
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="couleur"
                                       class="h-11 w-14 shrink-0 cursor-pointer rounded-[10px] border-[2px] border-primary bg-white p-1" />
                                <x-text-input name="couleur" floatError wire:model.live="couleur" class="w-32 font-mono uppercase" />
                            </div>
                        </div>
                    </div>

                    {{-- Membres : drag & drop entre Disponibles et Membres --}}
                    <div wire:key="team-dnd-{{ $editingId ?? 'new' }}"
                         x-data="teamMembers(@entangle('memberIds'), @js($adminsData))">
                        <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            <x-lucide-users class="h-3.5 w-3.5 text-primary" />
                            Membres — glissez les administrateurs (ou cliquez)
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- Disponibles --}}
                            <div x-on:drop.prevent="dropAvailable()" x-on:dragover.prevent
                                 class="flex min-h-[12rem] flex-col rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50/60 p-3">
                                <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Disponibles</p>
                                <div class="space-y-1.5">
                                    <template x-for="admin in available" :key="admin.id">
                                        <div draggable="true"
                                             x-on:dragstart="start(admin.id)"
                                             x-on:click="add(admin.id)"
                                             class="flex cursor-grab items-center gap-2.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-2 text-sm shadow-sm transition hover:border-primary/40 active:cursor-grabbing">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-[10px] font-semibold text-primary" x-text="admin.initials"></span>
                                            <span class="min-w-0 flex-1 truncate text-zinc-800" x-text="admin.name"></span>
                                            <x-lucide-plus class="h-4 w-4 text-zinc-300" />
                                        </div>
                                    </template>
                                    <p x-show="available.length === 0" class="px-1 py-6 text-center text-xs text-zinc-400">Tous les admins sont membres.</p>
                                </div>
                            </div>

                            {{-- Membres --}}
                            <div x-on:drop.prevent="dropMembers()" x-on:dragover.prevent
                                 class="flex min-h-[12rem] flex-col rounded-xl border-2 border-dashed border-primary/30 bg-primary/[0.04] p-3">
                                <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-wide text-primary">Membres (<span x-text="members.length"></span>)</p>
                                <div class="space-y-1.5">
                                    <template x-for="admin in members" :key="admin.id">
                                        <div draggable="true"
                                             x-on:dragstart="start(admin.id)"
                                             x-on:click="remove(admin.id)"
                                             class="flex cursor-grab items-center gap-2.5 rounded-lg border border-primary/20 bg-white px-2.5 py-2 text-sm shadow-sm transition hover:border-red-300 active:cursor-grabbing">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-[10px] font-semibold text-primary" x-text="admin.initials"></span>
                                            <span class="min-w-0 flex-1 truncate text-zinc-800" x-text="admin.name"></span>
                                            <x-lucide-x class="h-4 w-4 text-zinc-300" />
                                        </div>
                                    </template>
                                    <p x-show="members.length === 0" class="px-1 py-6 text-center text-xs text-zinc-400">Glissez des admins ici.</p>
                                </div>
                            </div>
                        </div>
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
