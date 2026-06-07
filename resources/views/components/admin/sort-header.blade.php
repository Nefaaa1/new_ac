@props([
    'field',
    'label',
    'sort' => null,       // colonne triée actuellement ($sortField)
    'direction' => 'asc', // sens actuel ($sortDirection)
])

<th class="px-5 py-3.5">
    <button type="button" wire:click="sortBy('{{ $field }}')"
            class="group inline-flex items-center gap-1.5 transition hover:text-white/80">
        {{ $label }}
        @if($sort === $field)
            <x-dynamic-component :component="$direction === 'asc' ? 'lucide-chevron-up' : 'lucide-chevron-down'" class="h-3.5 w-3.5" />
        @else
            <x-lucide-chevrons-up-down class="h-3.5 w-3.5 opacity-40 transition group-hover:opacity-80" />
        @endif
    </button>
</th>
