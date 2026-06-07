@props(['title', 'subtitle' => null, 'icon'])

<div class="flex items-center gap-4">
    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
        <x-dynamic-component :component="'lucide-'.$icon" class="h-6 w-6" />
    </span>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-zinc-500">{{ $subtitle }}</p>
        @endif
    </div>
</div>
