<div x-data="{ tab: 'general' }">
    {{-- En-tête : retour + titre + actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.sites') }}" wire:navigate
               class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-800">
                <x-lucide-arrow-left class="h-5 w-5" />
            </a>
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <x-lucide-globe class="h-6 w-6" />
                </span>
                <div>
                    <h1 class="flex items-center gap-2.5 text-2xl font-semibold tracking-tight text-zinc-900">
                        {{ $site->nom }}
                        @if($site->statut)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background-color: {{ $site->statut->color() }}1a; color: {{ $site->statut->color() }}">
                                <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $site->statut->color() }}"></span>
                                {{ $site->statut->libelle }}
                            </span>
                        @endif
                    </h1>
                    <p class="text-sm text-zinc-500">
                        @if($site->client)
                            {{ $site->client->societe ?: $site->client->user?->name }}
                        @else
                            Site sans client rattaché
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.sites.edit', $site) }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
                <x-lucide-pencil class="h-4 w-4" />
                Modifier
            </a>
            <button wire:click="deleteSite" type="button"
                    wire:confirm="Supprimer ce site ? Les accès (hébergement, FTP, BDD, WordPress) seront aussi supprimés."
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                <x-lucide-trash-2 class="h-4 w-4" />
                Supprimer
            </button>
        </div>
    </div>

    {{-- Onglets --}}
    <div class="mt-6 border-b border-zinc-200">
        <nav class="-mb-px flex flex-wrap gap-6">
            <button type="button" @click="tab = 'general'"
                    :class="tab === 'general' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-globe class="h-4 w-4" /> Général
            </button>
            <button type="button" @click="tab = 'hebergement'"
                    :class="tab === 'hebergement' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-server class="h-4 w-4" /> Hébergement
            </button>
            <button type="button" @click="tab = 'ftp'"
                    :class="tab === 'ftp' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-folder-tree class="h-4 w-4" /> FTP
            </button>
            <button type="button" @click="tab = 'bdd'"
                    :class="tab === 'bdd' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-database class="h-4 w-4" /> Base de données
            </button>
            <button type="button" @click="tab = 'wordpress'"
                    :class="tab === 'wordpress' ? 'border-primary text-primary' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                    class="flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition">
                <x-lucide-layout-template class="h-4 w-4" /> WordPress
            </button>
        </nav>
    </div>

    {{-- Onglet Général --}}
    <div x-show="tab === 'general'" class="mt-6 space-y-6">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                    <x-lucide-shopping-cart class="h-4 w-4 text-primary" /> Boutique
                </div>
                <p class="mt-2 text-base font-semibold text-zinc-900">{{ $site->boutique_en_ligne ? 'En ligne' : 'Non' }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                    <x-lucide-activity class="h-4 w-4 text-primary" /> Statut
                </div>
                <p class="mt-2 text-base font-semibold text-zinc-900">{{ $site->statut?->libelle ?? '—' }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                    <x-lucide-calendar class="h-4 w-4 text-primary" /> Date statut
                </div>
                <p class="mt-2 text-base font-semibold text-zinc-900">{{ $site->date_statut?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                    <x-lucide-building-2 class="h-4 w-4 text-primary" /> Client
                </div>
                <p class="mt-2 truncate text-base font-semibold text-zinc-900">
                    @if($site->client)
                        {{ $site->client->societe ?: $site->client->user?->name }}
                    @else — @endif
                </p>
            </div>
        </div>

        {{-- Mot de passe complémentaire --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm" x-data="{ show: false }">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-700">Mot de passe complémentaire</h2>
                @if($site->mot_de_passe_complementaire)
                    <button type="button" @click="show = ! show" class="text-zinc-400 transition hover:text-primary">
                        <x-lucide-eye x-show="!show" class="h-4 w-4" />
                        <x-lucide-eye-off x-show="show" x-cloak class="h-4 w-4" />
                    </button>
                @endif
            </div>
            <div class="px-6 py-4">
                @if($site->mot_de_passe_complementaire)
                    <span x-show="!show" class="font-mono text-sm tracking-widest text-zinc-400">••••••••••••</span>
                    <pre x-show="show" x-cloak class="select-all whitespace-pre-wrap font-mono text-sm text-zinc-800">{{ $site->mot_de_passe_complementaire }}</pre>
                @else
                    <p class="text-sm text-zinc-400">—</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Onglet Hébergement --}}
    <div x-show="tab === 'hebergement'" x-cloak class="mt-6">
        @php $h = $site->hebergement; @endphp
        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-700">
                    <x-lucide-server class="h-4 w-4 text-primary" /> Hébergement
                </h2>
                <x-admin.client-visible-badge :visible="$h?->client_visible" />
            </div>
            <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2" x-data="{ show: false }">
                <x-admin.cred-field label="Nom" :value="$h?->nom" />
                <x-admin.cred-field label="Registrar" :value="$h?->registrar" />
                <x-admin.cred-field label="Identifiant" :value="$h?->identifiant" />
                <x-admin.cred-field label="Mot de passe" :value="$h?->mot_de_passe" password />
                <x-admin.cred-field label="Renouvellement" :value="$h?->periodeLabel()" />
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Paiement agence</p>
                    <p class="mt-1 text-sm text-zinc-800">{{ $h?->paiement_agence ? 'Oui' : 'Non' }}</p>
                </div>
            </div>
        </section>
    </div>

    {{-- Onglet FTP --}}
    <div x-show="tab === 'ftp'" x-cloak class="mt-6">
        @php $f = $site->ftp; @endphp
        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-700">
                    <x-lucide-folder-tree class="h-4 w-4 text-primary" /> Accès FTP
                </h2>
                <x-admin.client-visible-badge :visible="$f?->client_visible" />
            </div>
            <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2" x-data="{ show: false }">
                <x-admin.cred-field label="Hôte" :value="$f?->hote" />
                <x-admin.cred-field label="Identifiant" :value="$f?->identifiant" />
                <x-admin.cred-field label="Mot de passe" :value="$f?->mot_de_passe" password />
            </div>
        </section>
    </div>

    {{-- Onglet Base de données --}}
    <div x-show="tab === 'bdd'" x-cloak class="mt-6">
        @php $b = $site->bdd; @endphp
        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-700">
                    <x-lucide-database class="h-4 w-4 text-primary" /> Base de données
                </h2>
                <x-admin.client-visible-badge :visible="$b?->client_visible" />
            </div>
            <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2" x-data="{ show: false }">
                <x-admin.cred-field label="Lien" :value="$b?->lien" />
                <x-admin.cred-field label="Serveur" :value="$b?->serveur" />
                <x-admin.cred-field label="Nom d'utilisateur" :value="$b?->username" />
                <x-admin.cred-field label="Mot de passe" :value="$b?->mot_de_passe" password />
            </div>
        </section>
    </div>

    {{-- Onglet WordPress --}}
    <div x-show="tab === 'wordpress'" x-cloak class="mt-6">
        @php $w = $site->wordpress; @endphp
        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-700">
                    <x-lucide-layout-template class="h-4 w-4 text-primary" /> WordPress
                </h2>
                <x-admin.client-visible-badge :visible="$w?->client_visible" />
            </div>
            <div class="space-y-5 px-6 py-5" x-data="{ show: false }">
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-primary">Accès administrateur</p>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-admin.cred-field label="Lien admin" :value="$w?->lien_admin" link />
                        <x-admin.cred-field label="Identifiant admin" :value="$w?->identifiant_admin" />
                        <x-admin.cred-field label="Mot de passe admin" :value="$w?->mot_de_passe_admin" password />
                    </div>
                </div>
                <div class="border-t border-zinc-100 pt-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-secondary">Accès client</p>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-admin.cred-field label="Lien client" :value="$w?->lien_client" link />
                        <x-admin.cred-field label="Identifiant client" :value="$w?->identifiant_client" />
                        <x-admin.cred-field label="Mot de passe client" :value="$w?->mot_de_passe_client" password />
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
