<div>
    <!-- En-tête -->
    <div class="flex items-center gap-4">
        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <x-lucide-circle-user class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Profil</h1>
            <p class="text-sm text-zinc-500">Vos informations personnelles.</p>
        </div>
    </div>

    @php $user = auth()->user(); @endphp

    <div class="mt-8 max-w-2xl rounded-2xl border border-zinc-200 bg-white p-8">
        <div class="flex items-center gap-4 border-b border-zinc-100 pb-6">
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-lg font-semibold text-primary">
                {{ strtoupper(mb_substr($user->prenom, 0, 1).mb_substr($user->nom, 0, 1)) }}
            </span>
            <div>
                <p class="text-lg font-semibold text-zinc-900">{{ $user->name }}</p>
                <span class="mt-1 inline-flex items-center rounded-full bg-secondary/10 px-2.5 py-0.5 text-xs font-medium uppercase tracking-wide text-secondary">
                    {{ $user->type }}
                </span>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Identifiant</dt>
                <dd class="mt-1 text-sm text-zinc-900">{{ $user->login }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Téléphone</dt>
                <dd class="mt-1 text-sm text-zinc-900">{{ $user->telephone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Email</dt>
                <dd class="mt-1 text-sm text-zinc-900">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Email secondaire</dt>
                <dd class="mt-1 text-sm text-zinc-900">{{ $user->email_secondaire ?? '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
