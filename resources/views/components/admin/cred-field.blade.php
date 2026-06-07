@props([
    'label' => null,
    'value' => null,
    'password' => false, // masque la valeur + œil de révélation (Alpine local)
    'link' => false,     // rend la valeur comme lien externe
])

@php $empty = ($value === null || $value === ''); @endphp

<div>
    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</p>

    @if($empty)
        <p class="mt-1 text-sm text-zinc-400">—</p>
    @elseif($password)
        <div class="mt-1 flex items-center gap-2" x-data="{ show: false }">
            <span x-show="!show" class="font-mono text-sm tracking-widest text-zinc-400">••••••••</span>
            <span x-show="show" x-cloak class="select-all break-all font-mono text-sm text-zinc-800">{{ $value }}</span>
            <button type="button" @click="show = ! show" class="shrink-0 text-zinc-400 transition hover:text-primary">
                <x-lucide-eye x-show="!show" class="h-4 w-4" />
                <x-lucide-eye-off x-show="show" x-cloak class="h-4 w-4" />
            </button>
        </div>
    @elseif($link)
        <a href="{{ $value }}" target="_blank" rel="noopener"
           class="mt-1 inline-flex items-center gap-1 text-sm text-primary hover:underline">
            <span class="truncate">{{ $value }}</span>
            <x-lucide-external-link class="h-3.5 w-3.5 shrink-0" />
        </a>
    @else
        <p class="mt-1 select-all break-words text-sm text-zinc-800">{{ $value }}</p>
    @endif
</div>
