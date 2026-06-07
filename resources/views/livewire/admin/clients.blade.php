<div>
    <x-admin.page-header
        title="Clients"
        subtitle="Gérer les comptes clients et leurs informations."
        icon="users" />

    {{-- Mot de passe généré : affiché une seule fois --}}
    @if($generatedPassword)
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-secondary/40 bg-secondary/5 p-4">
            <x-lucide-key-round class="mt-0.5 h-5 w-5 shrink-0 text-secondary" />
            <div class="flex-1">
                <p class="text-sm font-medium text-zinc-800">Client créé. Mot de passe à transmettre (affiché une seule fois) :</p>
                <code class="mt-1 inline-block select-all rounded-md bg-white px-3 py-1.5 font-mono text-sm text-zinc-900 ring-1 ring-zinc-200">{{ $generatedPassword }}</code>
            </div>
            <button wire:click="dismissPassword" type="button" class="text-zinc-400 hover:text-zinc-600">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>
    @endif

    {{-- Barre d'outils : recherche + action --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
            <x-text-input
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher nom, prénom, société…"
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
            Nouveau client
        </button>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="societe" label="Société" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="nom" label="Client" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="email" label="Email" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->clients as $client)
                    @php $info = $client->client; @endphp
                    <tr wire:key="client-{{ $client->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3">
                            @if($info?->societe)
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-sm font-semibold text-zinc-800">
                                    <x-lucide-building-2 class="h-4 w-4 text-primary" />
                                    {{ $info->societe }}
                                </span>
                            @else
                                <span class="text-sm text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-xs font-semibold text-primary">
                                    {{ strtoupper(mb_substr($client->prenom, 0, 1).mb_substr($client->nom, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-medium text-zinc-900">
                                        {{ $client->civilite ? $client->civilite.'. ' : '' }}{{ $client->name }}
                                    </p>
                                    <p class="text-xs text-zinc-400">{{ $client->login }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-zinc-600">
                            <span class="inline-flex items-center gap-1.5">
                                <x-lucide-mail class="h-3.5 w-3.5 text-secondary" />
                                {{ $client->email }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editClient({{ $client->id }})" type="button" title="Modifier"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>
                                <button wire:click="deleteClient({{ $client->id }})" type="button" title="Supprimer"
                                        wire:confirm="Supprimer ce client ?"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-zinc-400">
                            {{ $search !== '' ? 'Aucun client ne correspond à « '.$search.' ».' : 'Aucun client.' }}
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
                    {{ $editingId ? 'Modifier le client' : 'Nouveau client' }}
                </h2>
                <button wire:click="closeForm" type="button" class="text-zinc-400 hover:text-zinc-600">
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <form wire:submit="save" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <x-select label="Civilité" name="civilite" floatError wire:model="civilite">
                        <option value="">Sélectionner…</option>
                        <option value="M">M.</option>
                        <option value="Mme">Mme</option>
                    </x-select>

                    <div class="grid grid-cols-2 items-start gap-4">
                        <x-text-input label="Prénom" name="prenom" floatError wire:model.live.debounce.300ms="prenom" />
                        <x-text-input label="Nom" name="nom" floatError wire:model.live.debounce.300ms="nom" />
                    </div>

                    <x-text-input label="Identifiant (login)" name="login" placeholder="prenomnom" floatError wire:model.live.debounce.400ms="login" />

                    <x-text-input label="Email" name="email" type="email" floatError wire:model="email" />

                    <div class="grid grid-cols-2 items-start gap-4">
                        <x-text-input label="Email secondaire" name="email_secondaire" type="email" floatError wire:model="email_secondaire" />
                        <x-text-input label="Téléphone" name="telephone" floatError wire:model="telephone" />
                    </div>

                    {{-- Fiche métier client (mise en avant) --}}
                    <div class="space-y-6 rounded-xl border border-primary/20 bg-gradient-to-br from-primary/[0.07] to-secondary/[0.07] p-5">
                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-primary">
                            <x-lucide-building-2 class="h-4 w-4" />
                            Fiche client
                        </p>

                        <x-text-input label="Société" name="societe" floatError wire:model="societe" class="!bg-white" />
                        <x-text-input label="Lien app" name="lienapp" placeholder="https://…" floatError wire:model="lienapp" class="!bg-white" />
                        <x-text-input label="Email 3" name="email3" type="email" floatError wire:model="email3" class="!bg-white" />
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
