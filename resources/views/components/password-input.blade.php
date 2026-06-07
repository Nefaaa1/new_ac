@props([
    'label' => null,
    'name' => null,
    'placeholder' => '',
    'error' => null,
    'floatError' => false, // erreur en position absolue (ne décale pas la mise en page)
])

@php
    // Erreurs : explicites via :error, sinon auto-détectées via le name
    $messages = $error ?? ($name ? $errors->get($name) : null);
    $hasError = ! empty($messages);
    $firstMessage = is_array($messages) ? ($messages[0] ?? null) : $messages;
@endphp

<div class="relative" x-data="{ show: false }">
    @if($label)
        <label class="mb-1 block truncate text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <div class="relative">
        <input
            :type="show ? 'text' : 'password'"
            autocomplete="new-password"
            @if($name) name="{{ $name }}" @endif
            placeholder="{{ $placeholder }}"
            @if($hasError) aria-invalid="true" @endif
            {{ $attributes->merge([
                'class' => 'w-full bg-transparent border-[2px] rounded-[10px] px-5 py-2.5 pr-11 text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-0 transition '
                    . ($hasError
                        ? 'border-red-500 focus:border-red-500'
                        : 'border-primary focus:border-secondary')
            ]) }}
        >
        <button type="button" @click="show = ! show"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
            <x-lucide-eye x-show="!show" class="h-4 w-4" />
            <x-lucide-eye-off x-show="show" x-cloak class="h-4 w-4" />
        </button>
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
