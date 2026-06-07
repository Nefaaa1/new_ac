@props([
    'label' => null,
    'hint' => null,   // texte secondaire sous le label
    'disabled' => false,
])

{{-- Case à cocher stylée (design teal). wire:model passé en attribut. --}}
<label @class([
    'flex items-start gap-2.5 rounded-lg border p-3 transition',
    'cursor-pointer border-zinc-200 hover:border-primary/40' => ! $disabled,
    'cursor-not-allowed border-zinc-100 opacity-60' => $disabled,
])>
    <input
        type="checkbox"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 rounded border-2 border-primary text-primary focus:ring-0']) }}
    >
    @if($label || $hint)
        <span class="min-w-0">
            @if($label)<span class="block text-sm font-medium text-zinc-800">{{ $label }}</span>@endif
            @if($hint)<span class="block text-xs text-zinc-500">{{ $hint }}</span>@endif
        </span>
    @endif
</label>
