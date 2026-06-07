@props([
    'icon' => null,
    'text' => null,
    'size' => 'md',
    'full' => false,
])

@php
    // Tailles : padding + texte + espacement icône
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-xs gap-2',
        'lg' => 'px-6 py-3 text-sm gap-2.5',
    ];
    $iconSizes = [
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $iconClasses = $iconSizes[$size] ?? $iconSizes['md'];
@endphp

<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center border border-[2px] border-primary rounded-md font-semibold uppercase tracking-widest '
        . 'bg-primary text-white hover:bg-white hover:text-primary focus:bg-white focus:text-primary '
        . 'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 '
        . 'disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 '
        . $sizeClasses . ' '
        . ($full ? 'w-full' : ''),
]) }}>
    @if($icon)
        <x-dynamic-component :component="'lucide-' . $icon" :class="$iconClasses" />
    @endif

    {{ $text ?? $slot }}
</button>
