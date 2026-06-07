@props(['icon', 'title'])

<div class="mt-8 rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center">
    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 text-zinc-400 ring-1 ring-zinc-100">
        <x-dynamic-component :component="'lucide-'.$icon" class="h-6 w-6" />
    </span>
    <p class="mt-4 text-sm font-medium text-zinc-700">Page en construction</p>
    <p class="mt-1 text-xs text-zinc-400">Le contenu « {{ $title }} » arrivera bientôt.</p>
</div>
