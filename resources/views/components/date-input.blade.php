@props([
    'label' => null,
    'name' => null,         // pour l'auto-détection des erreurs (ex. "date_debut")
    'model',                // nom de la propriété Livewire pilotée (ex. "date_debut")
    'size' => 'md',
    'disabled' => false,
    'placeholder' => 'jj / mm / aaaa',
    'error' => null,
    'floatError' => false,  // erreur en position absolue (ne décale pas la mise en page)
])

@php
    // Tailles alignées sur text-input / select
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

    // Classes appliquées au champ visible (Flatpickr crée un altInput qui les reçoit aussi).
    $inputClasses = 'w-full cursor-pointer bg-transparent border-[2px] rounded-[10px] text-gray-600 '
        . 'placeholder-gray-400 focus:outline-none focus:ring-0 transition pr-11 ' . $sizeClasses . ' '
        . ($hasError ? 'border-red-500 focus:border-red-500' : 'border-primary focus:border-secondary');
@endphp

<div class="relative">
    @if($label)
        <label class="mb-1 block truncate text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    {{-- wire:ignore : Flatpickr manipule le DOM, Livewire ne doit pas le ré-écraser --}}
    <div wire:ignore class="relative" x-data="datePicker('{{ $model }}', @js($inputClasses))">
        <input x-ref="input" type="text" placeholder="{{ $placeholder }}" @disabled($disabled)
               class="{{ $inputClasses }}" />
        <x-lucide-calendar class="pointer-events-none absolute right-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
    </div>

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
