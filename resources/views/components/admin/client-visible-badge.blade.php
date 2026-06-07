@props(['visible' => false])

@if($visible)
    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-600">
        <x-lucide-eye class="h-3.5 w-3.5" /> Visible client
    </span>
@else
    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-500">
        <x-lucide-eye-off class="h-3.5 w-3.5" /> Masqué client
    </span>
@endif
