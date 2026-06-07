@props([
    'label' => null,
    'disabled' => false,
    'size' => 'md',
    'name' => null,
    'error' => null,
    'floatError' => false, // erreur en position absolue (ne décale pas la mise en page)
])

@php
    // Tailles : padding + taille de texte (alignées sur text-input)
    $sizes = [
        'sm' => 'px-4 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3.5 text-base',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];

    // Erreurs : explicites via :error, sinon auto-détectées via le name
    $messages = $error ?? ($name ? $errors->get($name) : null);
    $hasError = ! empty($messages);
    $firstMessage = is_array($messages) ? ($messages[0] ?? null) : $messages;
@endphp

<div class="relative">
    @if($label)
        <label class="mb-1 block truncate text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <select
        @disabled($disabled)
        @if($name) name="{{ $name }}" @endif
        @if($hasError) aria-invalid="true" @endif
        {{ $attributes->merge([
            'class' => 'w-full bg-transparent border-[2px] rounded-[10px] text-gray-600 focus:outline-none focus:ring-0 transition '
                . $sizeClasses . ' '
                . ($hasError
                    ? 'border-red-500 focus:border-red-500'
                    : 'border-primary focus:border-secondary')
        ]) }}
    >
        {{ $slot }}
    </select>

    @if($hasError)
        @if($floatError)
            <p class="absolute left-1 top-full mt-0.5 whitespace-nowrap text-[11px] leading-tight text-red-600">{{ $firstMessage }}</p>
        @else
            <ul class="mt-1.5 text-sm text-red-600 space-y-1">
                @foreach ((array) $messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
