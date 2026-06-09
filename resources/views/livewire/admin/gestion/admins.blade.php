<div>
    <x-admin.page-header
        title="Administrateurs"
        subtitle="Créer des comptes, suspendre l'accès et définir les permissions."
        icon="shield-user" />

    {{-- Mot de passe généré : affiché une seule fois --}}
    @if($generatedPassword)
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-secondary/40 bg-secondary/5 p-4">
            <x-lucide-key-round class="mt-0.5 h-5 w-5 shrink-0 text-secondary" />
            <div class="flex-1">
                <p class="text-sm font-medium text-zinc-800">Compte créé. Mot de passe à transmettre (affiché une seule fois) :</p>
                <code class="mt-1 inline-block select-all rounded-md bg-white px-3 py-1.5 font-mono text-sm text-zinc-900 ring-1 ring-zinc-200">{{ $generatedPassword }}</code>
            </div>
            <button wire:click="dismissPassword" type="button" class="text-zinc-400 hover:text-zinc-600">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>
    @endif

    {{-- Barre d'action --}}
    <div class="mt-6 flex justify-end">
        <button wire:click="create" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Nouvel administrateur
        </button>
    </div>

    {{-- Tableau --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <x-admin.sort-header field="nom" label="Administrateur" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="email" label="Email" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="access_level" label="Accès" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="suspended_at" label="Statut" :sort="$sortField" :direction="$sortDirection" />
                    <x-admin.sort-header field="last_login_at" label="Dernière connexion" :sort="$sortField" :direction="$sortDirection" />
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->admins as $admin)
                    @php $protected = $admin->id === auth()->id() || $admin->isSuperAdmin(); @endphp
                    <tr wire:key="admin-{{ $admin->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-xs font-semibold text-primary">
                                    {{ strtoupper(mb_substr($admin->prenom, 0, 1).mb_substr($admin->nom, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-medium text-zinc-900">
                                        {{ $admin->name }}
                                        @if($admin->isSuperAdmin())
                                            <span class="ml-1 align-middle text-[10px] font-semibold uppercase tracking-wide text-primary">· super-admin</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-zinc-400">{{ $admin->login }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-zinc-600">
                            <span class="inline-flex items-center gap-1.5">
                                <x-lucide-mail class="h-3.5 w-3.5 text-secondary" />
                                {{ $admin->email }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($admin->hasFullAccess())
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                    <x-lucide-shield-check class="h-3.5 w-3.5" /> Total
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-secondary/10 px-2.5 py-0.5 text-xs font-medium text-secondary">
                                    <x-lucide-shield class="h-3.5 w-3.5" /> Partiel ({{ $admin->access_grants_count }})
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($admin->isSuspended())
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Suspendu
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Actif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($admin->last_login_at)
                                <span class="text-zinc-600" title="{{ $admin->last_login_at->format('d/m/Y à H:i') }}">
                                    {{ $admin->last_login_at->locale('fr')->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs italic text-zinc-300">Jamais connecté</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editAdmin({{ $admin->id }})" type="button" title="Modifier"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-primary/10 hover:text-primary">
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>

                                @unless($protected)
                                    <button wire:click="toggleSuspend({{ $admin->id }})" type="button"
                                            title="{{ $admin->isSuspended() ? 'Réactiver' : 'Suspendre' }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-amber-50 hover:text-amber-600">
                                        <x-dynamic-component :component="$admin->isSuspended() ? 'lucide-circle-play' : 'lucide-ban'" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="deleteAdmin({{ $admin->id }})" type="button" title="Supprimer"
                                            wire:confirm="Supprimer cet administrateur ?"
                                            class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600">
                                        <x-lucide-trash-2 class="h-4 w-4" />
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-zinc-400">Aucun administrateur.</td>
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
                        {{ $editingId ? "Modifier l'administrateur" : 'Nouvel administrateur' }}
                    </h2>
                    <button wire:click="closeForm" type="button" class="text-zinc-400 hover:text-zinc-600">
                        <x-lucide-x class="h-5 w-5" />
                    </button>
                </div>

                <form wire:submit="save" class="flex flex-1 flex-col overflow-hidden">
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <x-select label="Civilité" name="civilite" required floatError wire:model="civilite">
                        <option value="">Sélectionner…</option>
                        <option value="M">M.</option>
                        <option value="Mme">Mme</option>
                    </x-select>

                    <div class="grid grid-cols-2 items-start gap-4">
                        <x-text-input label="Prénom" name="prenom" required floatError wire:model.live.debounce.300ms="prenom" />
                        <x-text-input label="Nom" name="nom" required floatError wire:model.live.debounce.300ms="nom" />
                    </div>

                    <x-text-input label="Identifiant (login)" name="login" required placeholder="prenomnom" floatError wire:model.live.debounce.400ms="login" />

                    <x-text-input label="Email" name="email" type="email" required floatError wire:model="email" />

                    <div class="grid grid-cols-2 items-start gap-4">
                        <x-text-input label="Email secondaire" name="email_secondaire" type="email" floatError wire:model="email_secondaire" />
                        <x-text-input label="Téléphone" name="telephone" floatError wire:model="telephone" />
                    </div>

                    {{-- Niveau d'accès --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Niveau d'accès</label>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <label @class([
                                'flex cursor-pointer items-start gap-2 rounded-lg border p-3 transition',
                                'border-primary bg-primary/5' => $accessLevel === 'full',
                                'border-zinc-200 hover:border-zinc-300' => $accessLevel !== 'full',
                            ])>
                                <input type="radio" wire:model.live="accessLevel" value="full" class="mt-0.5 text-primary focus:ring-0">
                                <span>
                                    <span class="block text-sm font-medium text-zinc-800">Total</span>
                                    <span class="block text-xs text-zinc-500">Accès à tout</span>
                                </span>
                            </label>
                            <label @class([
                                'flex cursor-pointer items-start gap-2 rounded-lg border p-3 transition',
                                'border-secondary bg-secondary/5' => $accessLevel === 'restricted',
                                'border-zinc-200 hover:border-zinc-300' => $accessLevel !== 'restricted',
                            ])>
                                <input type="radio" wire:model.live="accessLevel" value="restricted" class="mt-0.5 text-secondary focus:ring-0">
                                <span>
                                    <span class="block text-sm font-medium text-zinc-800">Partiel</span>
                                    <span class="block text-xs text-zinc-500">Ressources choisies</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Sélection granulaire des ressources --}}
                    @if($accessLevel === 'restricted')
                        <div class="space-y-4 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                            <div>
                                <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    <x-lucide-users class="h-3.5 w-3.5 text-primary" />
                                    Clients accessibles
                                </p>
                                @if($this->clients->isEmpty())
                                    <p class="mt-2 text-xs text-zinc-400">Aucun client pour le moment.</p>
                                @else
                                    <div class="mt-2 max-h-40 space-y-1 overflow-y-auto pr-1">
                                        @foreach($this->clients as $client)
                                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-zinc-700 hover:bg-white">
                                                <input type="checkbox" wire:model="grantedClientIds" value="{{ $client->id }}" class="rounded text-primary focus:ring-0">
                                                {{ $client->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="border-t border-zinc-200 pt-3">
                                <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    <x-lucide-file-text class="h-3.5 w-3.5 text-primary" />
                                    Contrats accessibles
                                </p>
                                @if($this->contrats->isEmpty())
                                    <p class="mt-2 text-xs text-zinc-400">Aucun contrat pour le moment.</p>
                                @else
                                    <div class="mt-2 max-h-40 space-y-1 overflow-y-auto pr-1">
                                        @foreach($this->contrats as $contrat)
                                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-zinc-700 hover:bg-white">
                                                <input type="checkbox" wire:model="grantedContratIds" value="{{ $contrat->id }}" class="rounded text-primary focus:ring-0">
                                                <span class="min-w-0">
                                                    <span class="block truncate">{{ $contrat->libelle }}</span>
                                                    @if($contrat->client)
                                                        <span class="block truncate text-xs text-zinc-400">{{ $contrat->client->societe ?: $contrat->client->user?->name }}</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="border-t border-zinc-200 pt-3">
                                <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    <x-lucide-globe class="h-3.5 w-3.5 text-primary" />
                                    Sites accessibles
                                </p>
                                @if($this->sites->isEmpty())
                                    <p class="mt-2 text-xs text-zinc-400">Aucun site pour le moment.</p>
                                @else
                                    <div class="mt-2 max-h-40 space-y-1 overflow-y-auto pr-1">
                                        @foreach($this->sites as $site)
                                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-zinc-700 hover:bg-white">
                                                <input type="checkbox" wire:model="grantedSiteIds" value="{{ $site->id }}" class="rounded text-primary focus:ring-0">
                                                <span class="min-w-0">
                                                    <span class="block truncate">{{ $site->nom }}</span>
                                                    @if($site->client)
                                                        <span class="block truncate text-xs text-zinc-400">{{ $site->client->societe ?: $site->client->user?->name }}</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

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
