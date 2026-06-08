<div>
    {{-- En-tête : retour + titre --}}
    <div class="flex items-center gap-4">
        <a href="{{ $editingId ? route('admin.sites.show', $editingId) : route('admin.sites') }}" wire:navigate
           class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-800">
            <x-lucide-arrow-left class="h-5 w-5" />
        </a>
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <x-lucide-globe class="h-6 w-6" />
            </span>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
                    {{ $editingId ? 'Modifier le site' : 'Nouveau site' }}
                </h1>
                <p class="text-sm text-zinc-500">Informations générales et accès techniques.</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="mt-6">
        {{-- Onglets --}}
        <div class="border-b border-zinc-200">
            <nav class="-mb-px flex flex-wrap gap-6">
                <button type="button" wire:click="$set('activeTab', 'general')"
                        :class="$wire.activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-globe class="h-4 w-4" />
                    Général
                    @if($errors->hasAny(['nom', 'date_statut']))
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    @endif
                </button>
                <button type="button" wire:click="$set('activeTab', 'hebergement')"
                        :class="$wire.activeTab === 'hebergement' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-server class="h-4 w-4" />
                    Hébergement
                </button>
                <button type="button" wire:click="$set('activeTab', 'ftp')"
                        :class="$wire.activeTab === 'ftp' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-folder-tree class="h-4 w-4" />
                    FTP
                </button>
                <button type="button" wire:click="$set('activeTab', 'bdd')"
                        :class="$wire.activeTab === 'bdd' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-database class="h-4 w-4" />
                    Base de données
                </button>
                <button type="button" wire:click="$set('activeTab', 'wordpress')"
                        :class="$wire.activeTab === 'wordpress' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                    <x-lucide-layout-template class="h-4 w-4" />
                    WordPress
                </button>
            </nav>
        </div>

        {{-- Onglet Général --}}
        <div x-show="$wire.activeTab === 'general'" class="mt-6 grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-globe class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Identité du site</h2>
                </header>

                <x-text-input label="Nom du site" name="nom" required floatError wire:model="nom"
                              placeholder="Ex. Boutique Dupont" />

                <div class="relative">
                    <livewire:admin.client-picker wire:model="client_id" :key="'client-picker-'.$editingId" />
                    @error('client_id')
                        <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-checkbox wire:model="boutique_en_ligne"
                            label="Boutique en ligne"
                            hint="Le site comporte une partie e-commerce." />
            </section>

            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/15 text-secondary">
                        <x-lucide-activity class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Statut &amp; accès</h2>
                </header>

                <x-select label="Statut" name="statut_id" floatError wire:model.live="statut_id">
                    <option value="">— Aucun —</option>
                    @foreach($this->statutsList as $statut)
                        <option value="{{ $statut->id }}">{{ $statut->libelle }}</option>
                    @endforeach
                </x-select>

                <div>
                    <x-date-input label="Date de statut" :required="$this->dateRequise"
                                  name="date_statut" model="date_statut" floatError />
                    @if($this->dateRequise)
                        <p class="mt-1 flex items-center gap-1 text-xs text-secondary">
                            <x-lucide-info class="h-3.5 w-3.5" /> Le statut choisi impose une date.
                        </p>
                    @endif
                </div>

                <x-textarea label="Mot de passe complémentaire" name="mot_de_passe_complementaire"
                            rows="3" wire:model="mot_de_passe_complementaire"
                            placeholder="Notes / identifiants additionnels…" />
            </section>
        </div>

        {{-- Onglet Hébergement --}}
        <div x-show="$wire.activeTab === 'hebergement'" x-cloak class="mt-6">
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-server class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Hébergement</h2>
                </header>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <x-text-input label="Nom de l'hébergement" wire:model="hebergement.nom" />
                    <x-text-input label="Registrar" wire:model="hebergement.registrar" />
                    <x-text-input label="Identifiant" wire:model="hebergement.identifiant" />
                    <x-password-input label="Mot de passe" wire:model="hebergement.mot_de_passe" />
                    <x-select label="Période de renouvellement" wire:model="hebergement.periode_renouvellement">
                        <option value="">— Non précisé —</option>
                        @foreach(\App\Models\SiteHebergement::PERIODES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-checkbox wire:model="hebergement.paiement_agence" label="Paiement agence" />
                    <x-checkbox wire:model="hebergement.client_visible"
                                label="Visible par le client" hint="Identifiants affichés dans l'espace client." />
                </div>
            </section>
        </div>

        {{-- Onglet FTP --}}
        <div x-show="$wire.activeTab === 'ftp'" x-cloak class="mt-6">
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-folder-tree class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Accès FTP</h2>
                </header>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <x-text-input label="Hôte" wire:model="ftp.hote" placeholder="ftp.exemple.com" />
                    <x-text-input label="Identifiant" wire:model="ftp.identifiant" />
                    <x-password-input label="Mot de passe" wire:model="ftp.mot_de_passe" />
                </div>

                <x-checkbox wire:model="ftp.client_visible"
                            label="Visible par le client" hint="Identifiants affichés dans l'espace client." />
            </section>
        </div>

        {{-- Onglet Base de données --}}
        <div x-show="$wire.activeTab === 'bdd'" x-cloak class="mt-6">
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-database class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Base de données</h2>
                </header>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <x-text-input label="Lien" wire:model="bdd.lien" placeholder="https://… (phpMyAdmin)" />
                    <x-text-input label="Serveur" wire:model="bdd.serveur" placeholder="localhost" />
                    <x-text-input label="Nom d'utilisateur" wire:model="bdd.username" />
                    <x-password-input label="Mot de passe" wire:model="bdd.mot_de_passe" />
                </div>

                <x-checkbox wire:model="bdd.client_visible"
                            label="Visible par le client" hint="Identifiants affichés dans l'espace client." />
            </section>
        </div>

        {{-- Onglet WordPress : accès admin (gauche) + accès client (droite) --}}
        <div x-show="$wire.activeTab === 'wordpress'" x-cloak class="mt-6 grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            {{-- Accès administrateur --}}
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-shield-user class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Accès administrateur</h2>
                </header>

                <x-text-input label="Lien admin" wire:model="wordpress.lien_admin" placeholder="https://…/wp-admin" />
                <x-text-input label="Identifiant admin" wire:model="wordpress.identifiant_admin" />
                <x-password-input label="Mot de passe admin" wire:model="wordpress.mot_de_passe_admin" />
            </section>

            {{-- Accès client --}}
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <header class="flex items-center gap-2.5 border-b border-zinc-100 pb-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/15 text-secondary">
                        <x-lucide-circle-user class="h-4 w-4" />
                    </span>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Accès client</h2>
                </header>

                <x-text-input label="Lien client" wire:model="wordpress.lien_client" />
                <x-text-input label="Identifiant client" wire:model="wordpress.identifiant_client" />
                <x-password-input label="Mot de passe client" wire:model="wordpress.mot_de_passe_client" />

                
            </section>
            <section class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="">
                    <x-checkbox wire:model="wordpress.client_visible"
                                label="Visible par le client" hint="Identifiants affichés dans l'espace client." />
                </div>
            </section>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex items-center justify-end gap-3 border-t border-zinc-200 pt-6">
            <a href="{{ $editingId ? route('admin.sites.show', $editingId) : route('admin.sites') }}" wire:navigate
               class="text-sm text-zinc-500 transition hover:text-zinc-700">Annuler</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90">
                <x-lucide-check class="h-4 w-4" />
                {{ $editingId ? 'Enregistrer' : 'Créer le site' }}
            </button>
        </div>
    </form>
</div>
