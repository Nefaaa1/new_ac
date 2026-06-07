<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-primary">Espace client</p>
            <h1 class="mt-1 text-2xl font-semibold uppercase tracking-widest text-white">Dashboard</h1>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-primary-button size="sm" icon="log-out" text="Déconnexion" />
        </form>
    </div>

    <div class="mt-8 h-px w-full bg-zinc-800"></div>

    {{-- Contenu client à venir --}}
</div>
