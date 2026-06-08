@props([
    'filled' => false, // l'onglet contient-il des infos ?
])

{{-- Indicateur compact « renseigné / vide » pour la liste des sites (hébergement, FTP, BDD, WordPress). --}}
@if($filled)
    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100"
          title="Renseigné">
        <x-lucide-check class="h-4 w-4" />
    </span>
@else
    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-zinc-300"
          title="Vide">
        <x-lucide-minus class="h-4 w-4" />
    </span>
@endif
