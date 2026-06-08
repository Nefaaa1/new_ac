<div>
    {{-- En-tête : retour + titre --}}
    <div class="flex items-center gap-4">
        <a href="{{ $editingId ? route('admin.contrats.show', $editingId) : route('admin.contrats') }}" wire:navigate
           class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-800">
            <x-lucide-arrow-left class="h-5 w-5" />
        </a>
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <x-lucide-file-text class="h-6 w-6" />
            </span>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
                    {{ $editingId ? 'Modifier le contrat' : 'Nouveau contrat' }}
                </h1>
                <p class="text-sm text-zinc-500">Informations générales et comptes réseaux sociaux.</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="mt-6">
        {{-- Onglets --}}
        <div class="border-b border-zinc-200">
            <nav class="-mb-px flex gap-6">
                <button type="button" wire:click="$set('activeTab', 'general')"
                        :class="$wire.activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-file-text class="h-4 w-4" />
                    Général
                    @error('libelle') <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> @enderror
                </button>
                <button type="button" wire:click="$set('activeTab', 'reseaux')"
                        :class="$wire.activeTab === 'reseaux' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-share-2 class="h-4 w-4" />
                    Réseaux sociaux
                    @if(count($reseaux))
                        <span class="rounded-full bg-secondary/15 px-2 py-0.5 text-xs font-semibold text-secondary">{{ count($reseaux) }}</span>
                    @endif
                </button>
            </nav>
        </div>

        {{-- Onglet Général --}}
        <div x-show="$wire.activeTab === 'general'" class="mt-6 grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            {{-- Carte : identité du contrat --}}
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-file-text class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Informations générales</h2>
                </header>

                <x-text-input label="Libellé du contrat" name="libelle" required floatError wire:model="libelle"
                              placeholder="Ex. Community management mensuel" />

                <div class="relative">
                    <livewire:admin.client-picker wire:model="client_id" :key="'client-picker-'.$editingId" />
                    @error('client_id')
                        <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-text-input label="Site web" name="site_web" floatError wire:model="site_web" placeholder="https://…" />

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <x-date-input label="Début du contrat" name="date_debut" model="date_debut" floatError />
                    <x-date-input label="Fin" name="date_fin" model="date_fin" floatError />
                </div>
            </section>

            {{-- Carte : conditions & facturation --}}
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/15 text-secondary">
                        <x-lucide-circle-dollar-sign class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Conditions &amp; facturation</h2>
                </header>

                <x-select label="Type de contrat" name="type" required floatError wire:model="type">
                    <option value="">— Sélectionner —</option>
                    @foreach(\App\Models\Contrat::TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>

                <x-select label="Cycle de facturation" name="cycle_facturation" required floatError wire:model="cycle_facturation">
                    <option value="">— Sélectionner —</option>
                    @foreach(\App\Models\Contrat::CYCLES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <x-text-input label="Taux horaire (€)" name="taux_horaire" type="number" step="0.01" min="0"
                                  required floatError wire:model="taux_horaire" placeholder="0.00" />
                    <x-text-input label="Crédits (1 = 1h)" name="credits" type="number" min="0"
                                  required floatError wire:model="credits" placeholder="0" />
                </div>
            </section>
        </div>

        {{-- Onglet Réseaux sociaux --}}
        <div x-show="$wire.activeTab === 'reseaux'" x-cloak class="mt-6 space-y-4">
            @forelse($reseaux as $i => $reseau)
                <div wire:key="reseau-{{ $i }}"
                     class="rounded-xl border border-primary/20 bg-gradient-to-br from-primary/[0.05] to-secondary/[0.05] p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-primary">
                            <x-lucide-share-2 class="h-4 w-4" />
                            Compte réseau #{{ $i + 1 }}
                        </p>
                        <button type="button" wire:click="removeReseau({{ $i }})" title="Retirer"
                                class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 transition hover:bg-red-50 hover:text-red-600">
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <x-select label="Réseau" name="reseaux.{{ $i }}.reseau" required floatError wire:model="reseaux.{{ $i }}.reseau" class="!bg-white">
                            <option value="">— Sélectionner —</option>
                            @foreach(\App\Models\ContratReseau::RESEAUX as $value => $meta)
                                <option value="{{ $value }}">{{ $meta['label'] }}</option>
                            @endforeach
                        </x-select>

                        <x-select label="Gestion du compte" name="reseaux.{{ $i }}.gestion" floatError wire:model="reseaux.{{ $i }}.gestion" class="!bg-white">
                            <option value="">— Non précisé —</option>
                            @foreach(\App\Models\ContratReseau::GESTION as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-select>

                        <x-text-input label="Identifiant" name="reseaux.{{ $i }}.identifiant" floatError
                                      wire:model="reseaux.{{ $i }}.identifiant" class="!bg-white" />

                        <div x-data="{ show: false }" class="relative">
                            <label class="mb-1 block truncate text-sm font-medium text-gray-700">Mot de passe</label>
                            <input :type="show ? 'text' : 'password'" wire:model="reseaux.{{ $i }}.mot_de_passe"
                                   autocomplete="new-password"
                                   class="w-full rounded-[10px] border-[2px] border-primary bg-white px-5 py-2.5 pr-11 text-sm text-gray-600 transition focus:border-secondary focus:outline-none focus:ring-0">
                            <button type="button" @click="show = ! show"
                                    class="absolute right-3.5 top-[34px] text-zinc-400 transition hover:text-zinc-600">
                                <x-lucide-eye x-show="!show" class="h-4 w-4" />
                                <x-lucide-eye-off x-show="show" x-cloak class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center">
                    <x-lucide-share-2 class="mx-auto h-8 w-8 text-zinc-300" />
                    <p class="mt-3 text-sm text-zinc-500">Aucun réseau social ajouté pour ce contrat.</p>
                </div>
            @endforelse

            <button type="button" wire:click="addReseau"
                    class="inline-flex items-center gap-2 rounded-lg border-[2px] border-dashed border-primary/40 px-4 py-2.5 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5">
                <x-lucide-plus class="h-4 w-4" />
                Ajouter un réseau social
            </button>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex items-center justify-end gap-3 border-t border-zinc-200 pt-6">
            <a href="{{ $editingId ? route('admin.contrats.show', $editingId) : route('admin.contrats') }}" wire:navigate
               class="text-sm text-zinc-500 transition hover:text-zinc-700">Annuler</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90">
                <x-lucide-check class="h-4 w-4" />
                {{ $editingId ? 'Enregistrer' : 'Créer le contrat' }}
            </button>
        </div>
    </form>
</div>
