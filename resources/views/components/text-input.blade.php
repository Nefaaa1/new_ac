@props([
    'label' => null,
    'disabled' => false,
    'placeholder' => '',
    'size' => 'md',
    'name' => null,
    'error' => null,
])

@php
    // Tailles : padding + taille de texte
    $sizes = [
        'sm' => 'px-4 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3.5 text-base',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];

    // Erreurs : explicites via :error, sinon auto-détectées via le name
    $messages = $error ?? ($name ? $errors->get($name) : null);
    $hasError = ! empty($messages);
@endphp

@if($label)
    <label class="block mb-1 text-sm font-medium text-gray-700">{{ $label }}</label>
@endif

<input
    @disabled($disabled)
    @if($name) name="{{ $name }}" @endif
    placeholder="{{ $placeholder }}"
    @if($hasError) aria-invalid="true" @endif
    {{ $attributes->merge([
        'class' => 'w-full bg-transparent border-[2px] rounded-[10px] text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-0 transition '
            . $sizeClasses . ' '
            . ($hasError
                ? 'border-red-500 focus:border-red-500'
                : 'border-primary focus:border-secondary')
    ]) }}
>

@if($hasError)
    <ul class="mt-1.5 text-sm text-red-600 space-y-1">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
