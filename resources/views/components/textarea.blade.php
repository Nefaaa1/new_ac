@props([
    'label' => null,
    'disabled' => false,
    'placeholder' => '',
    'rows' => 3,
    'name' => null,
    'error' => null,
    'floatError' => false, // erreur en position absolue (ne décale pas la mise en page)
    'required' => false,   // astérisque amber « champ obligatoire »
])

@php
    // Erreurs : explicites via :error, sinon auto-détectées via le name
    $messages = $error ?? ($name ? $errors->get($name) : null);
    $hasError = ! empty($messages);
    $firstMessage = is_array($messages) ? ($messages[0] ?? null) : $messages;
@endphp

<div class="relative">
    <x-field-label :label="$label" :required="$required" />

    <textarea
        @disabled($disabled)
        @if($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($hasError) aria-invalid="true" @endif
        @if($required) aria-required="true" @endif
        {{ $attributes->merge([
            'class' => 'w-full resize-none bg-transparent border-[2px] rounded-[10px] px-5 py-2.5 text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-0 transition '
                . ($hasError
                    ? 'border-red-500 focus:border-red-500'
                    : 'border-primary focus:border-secondary')
        ]) }}
    ></textarea>

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
