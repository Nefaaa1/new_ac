<div>
    <x-admin.page-header title="Tickets" subtitle="Suivi des demandes d'intervention par site." icon="ticket" />

    {{-- Barre d'outils : recherche + filtres + action (une seule ligne sur desktop) --}}
    <div class="mt-6 flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-56">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
            <x-text-input
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher…"
                class="!pl-11 !pr-11" />
            @if($search !== '')
                <button wire:click="$set('search', '')" type="button" title="Effacer"
                        class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                    <x-lucide-x class="h-4 w-4" />
                </button>
            @endif
        </div>

        {{-- Filtre attribution (admins + équipes) --}}
        <div class="w-full sm:w-48">
            <x-select wire:model.live="assigneFilter">
                <option value="">Toute attribution</option>
                <option value="none">Non attribués</option>
                @if($this->equipesList->isNotEmpty())
                    <optgroup label="Équipes">
                        @foreach($this->equipesList as $equipe)
                            <option value="e:{{ $equipe->id }}">{{ $equipe->nom }}</option>
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

        {{-- Filtre statut --}}
        <div class="w-full sm:w-44">
            <x-select wire:model.live="statutFilter">
                <option value="">Tous les statuts</option>
                @foreach($this->statutsList as $statut)
                    <option value="{{ $statut->id }}">{{ $statut->libelle }}</option>
                @endforeach
            </x-select>
        </div>

        {{-- Filtre importance --}}
        <div class="w-full sm:w-44">
            <x-select wire:model.live="importanceFilter">
                <option value="">Toute importance</option>
                @foreach(\App\Models\Ticket::IMPORTANCES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>

        {{-- Filtre devis --}}
        <div class="w-full sm:w-44">
            <x-select wire:model.live="devisFilter">
                <option value="">Tous les devis</option>
                <option value="sans">Sans devis</option>
                @foreach($this->devisStatutsList as $devis)
                    <option value="{{ $devis->id }}">{{ $devis->libelle }}</option>
                @endforeach
            </x-select>
        </div>

        <button wire:click="create" type="button"
                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90 sm:ml-auto sm:w-auto">
            <x-lucide-plus class="h-4 w-4" />
            Nouveau ticket
        </button>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <th class="px-5 py-3.5">Site</th>
                    <x-admin.sort-header field="date" label="Date" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="demande" label="Demande" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="importance" label="Importance" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5">Statut</th>
                    <th class="px-5 py-3.5">Attribué à</th>
                    <th class="px-5 py-3.5">Temps</th>
                    <th class="px-5 py-3.5 text-center">Devis</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->tickets as $ticket)
                    <tr wire:key="ticket-{{ $ticket->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        {{-- Site --}}
                        <td class="px-5 py-3">
                            @if($ticket->site)
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 font-medium text-zinc-800">
                                        <x-lucide-globe class="h-3.5 w-3.5 text-primary" />
                                        <span class="truncate @if($ticket->site->trashed()) line-through decoration-red-300 @endif">{{ $ticket->site->nom }}</span>
                                    </span>
                                    @if($ticket->site->trashed())
                                        <span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-600" title="Site supprimé">archivé</span>
                                    @endif
                                </span>
                                @if($ticket->site->client)
                                    <p class="mt-0.5 truncate text-xs text-zinc-400">{{ $ticket->site->client->societe ?: $ticket->site->client->user?->name }}</p>
                                @endif
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        {{-- Date --}}
                        <td class="px-5 py-3 whitespace-nowrap text-zinc-600">{{ $ticket->date?->format('d/m/Y') ?? '—' }}</td>
                        {{-- Demande (+ descriptif) --}}
                        <td class="px-5 py-3">
                            <div class="min-w-0">
                                <p class="font-medium text-zinc-900">{{ $ticket->demande }}</p>
                                @if($ticket->descriptif)
                                    <p class="truncate text-xs text-zinc-400" title="{{ $ticket->descriptif }}">{{ $ticket->descriptif }}</p>
                                @endif
                            </div>
                        </td>
                        {{-- Importance --}}
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background-color: {{ $ticket->importanceColor() }}1a; color: {{ $ticket->importanceColor() }}">
                                <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $ticket->importanceColor() }}"></span>
                                {{ $ticket->importanceLabel() }}
                            </span>
                        </td>
                        {{-- Statut --}}
                        <td class="px-5 py-3">
                            @if($ticket->statut)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                      style="background-color: {{ $ticket->statut->color() }}1a; color: {{ $ticket->statut->color() }}">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $ticket->statut->color() }}"></span>
                                    {{ $ticket->statut->libelle }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        {{-- Attribué à : équipe (chip couleur) ou admin (avatar) --}}
                        <td class="px-5 py-3">
                            @if($ticket->equipe)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                      style="background-color: {{ $ticket->equipe->color() }}1a; color: {{ $ticket->equipe->color() }}">
                                    <x-lucide-users-round class="h-3.5 w-3.5" />
                                    {{ $ticket->equipe->nom }}
                                </span>
                            @elseif($ticket->utilisateur)
                                <span class="inline-flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-[10px] font-semibold text-primary">
                                        {{ strtoupper(mb_substr($ticket->utilisateur->prenom, 0, 1).mb_substr($ticket->utilisateur->nom, 0, 1)) }}
                                    </span>
                                    <span class="text-zinc-700">{{ $ticket->utilisateur->name }}</span>
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        {{-- Temps --}}
                        <td class="px-5 py-3 whitespace-nowrap text-zinc-600">
                            @if($ticket->tempsLabel())
                                <span class="inline-flex items-center gap-1.5">
                                    <x-lucide-clock class="h-3.5 w-3.5 text-secondary" />
                                    {{ $ticket->tempsLabel() }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        {{-- Devis --}}
                        <td class="px-5 py-3 text-center">
                            @if($ticket->a_deviser)
                                @if($ticket->devisStatut)
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          style="background-color: {{ $ticket->devisStatut->color() }}1a; color: {{ $ticket->devisStatut->color() }}"
                                          title="Devis : {{ $ticket->devisStatut->libelle }}">
                                        <x-lucide-file-text class="h-3.5 w-3.5" />
                                        {{ $ticket->devisStatut->libelle }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-500" title="À deviser">
                                        <x-lucide-file-text class="h-3.5 w-3.5" /> Devis
                                    </span>
                                @endif
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editTicket({{ $ticket->id }})" type="button" title="Modifier"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>
                                <button wire:click="deleteTicket({{ $ticket->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer ce ticket ?"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-zinc-400">
                            {{ ($search !== '' || $assigneFilter !== '' || $statutFilter !== '' || $importanceFilter !== '' || $devisFilter !== '') ? 'Aucun ticket ne correspond à votre recherche.' : 'Aucun ticket pour le moment.' }}
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
             class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col bg-white shadow-xl">
            <div class="flex shrink-0 items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="text-base font-semibold text-zinc-900">
                    {{ $editingId ? 'Modifier le ticket' : 'Nouveau ticket' }}
                </h2>
                <button wire:click="closeForm" type="button" class="text-zinc-400 hover:text-zinc-600">
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <form wire:submit="save" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <x-text-input label="Demande" name="demande" required floatError wire:model="demande"
                                  placeholder="Ex. Corriger le formulaire de contact" />

                    <x-textarea label="Descriptif" name="descriptif" rows="3" wire:model="descriptif"
                                placeholder="Détails de la demande…" />

                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <div class="relative">
                            <livewire:admin.site-picker wire:model="site_id" :required="true" :key="'site-picker-'.$formNonce" />
                            @error('site_id')
                                <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-date-input label="Date" name="date" model="date" required floatError />
                    </div>

                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <x-select label="Statut" name="statut_id" required floatError wire:model="statut_id">
                            <option value="">— Sélectionner —</option>
                            @foreach($this->statutsList as $statut)
                                <option value="{{ $statut->id }}">{{ $statut->libelle }}</option>
                            @endforeach
                        </x-select>

                        <x-select label="Importance" name="importance" required floatError wire:model="importance">
                            @foreach(\App\Models\Ticket::IMPORTANCES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <x-text-input label="Temps d'intervention (heures)" name="temps_intervention" type="number"
                                      step="0.25" min="0" floatError wire:model="temps_intervention" placeholder="1.5" />

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

                    {{-- Devis (facultatif) --}}
                    <div class="space-y-4 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <x-checkbox wire:model.live="a_deviser"
                                    label="À deviser"
                                    hint="Ce ticket fait l'objet d'un devis." />

                        <div x-show="$wire.a_deviser" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <x-select label="État du devis" name="devis_statut_id" floatError wire:model="devis_statut_id" class="!bg-white">
                                <option value="">— Par défaut —</option>
                                @foreach($this->devisStatutsList as $devis)
                                    <option value="{{ $devis->id }}">{{ $devis->libelle }}</option>
                                @endforeach
                            </x-select>
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
