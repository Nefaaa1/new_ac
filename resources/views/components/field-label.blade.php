@props([
    'label' => null,
    'required' => false, // affiche l'astérisque amber « champ obligatoire »
])

{{-- Label de champ partagé (text-input, select, textarea, date-input, password-input).
     L'indicateur visuel d'obligation = astérisque secondary (amber). --}}
@if($label)
    <label {{ $attributes->merge(['class' => 'mb-1 flex items-baseline gap-1 text-sm font-medium text-gray-700']) }}>
        <span class="truncate">{{ $label }}</span>
        @if($required)
            <span class="text-secondary" aria-hidden="true" title="Champ obligatoire">*</span>
            <span class="sr-only">(obligatoire)</span>
        @endif
    </label>
@endif
