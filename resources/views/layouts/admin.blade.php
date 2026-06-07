<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        @php $page = \App\Support\Navigation::find(request()->route()?->getName()); @endphp
        <title>{{ $page ? $page['label'].' · '.config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-screen overflow-hidden bg-gray-50 font-sans text-zinc-900 antialiased">
        @php
            $user = auth()->user();
            $initials = strtoupper(mb_substr($user->prenom, 0, 1).mb_substr($user->nom, 0, 1));
        @endphp

        <div class="flex h-screen">
            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-zinc-950 lg:flex">
                <!-- Logo -->
                <div class="flex h-16 items-center border-b border-white/5 px-6">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="block">
                        <img src="{{ asset('images/Logo-website-blanc.png') }}" alt="Partner Web Communication"
                             class="h-9 w-auto">
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 space-y-8 overflow-y-auto px-4 py-6">
                    @foreach (\App\Support\Navigation::groups() as $group)
                        @continue(isset($group['can']) && auth()->user()->cannot($group['can']))
                        <div>
                            <p class="mb-3 flex items-center gap-2 px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full',
                                    'bg-primary' => $group['color'] === 'primary',
                                    'bg-secondary' => $group['color'] === 'secondary',
                                    'bg-rose-500' => $group['color'] === 'rose',
                                ])></span>
                                {{ $group['title'] }}
                            </p>
                            <div class="space-y-1">
                                @foreach ($group['items'] as $item)
                                    @php $active = request()->routeIs($item['route']); @endphp
                                    <a href="{{ route($item['route']) }}" wire:navigate
                                       @class([
                                           'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                           'bg-primary/15 text-primary' => $active && $group['color'] === 'primary',
                                           'bg-secondary/15 text-secondary' => $active && $group['color'] === 'secondary',
                                           'bg-rose-500/15 text-rose-400' => $active && $group['color'] === 'rose',
                                           'text-zinc-400 hover:bg-white/5 hover:text-white' => ! $active,
                                       ])>
                                        <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-5 w-5 shrink-0" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
            </aside>

            <!-- Contenu -->
            <div class="flex flex-1 flex-col overflow-hidden lg:pl-64">
                <!-- Topbar (fixe) -->
                <header class="relative z-20 flex h-16 shrink-0 items-center gap-4 border-b border-zinc-200 bg-white px-6">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="lg:hidden">
                        <img src="{{ asset('images/logo-website-noir.png') }}" alt="" class="h-6 w-auto">
                    </a>

                    <livewire:admin.global-search />

                    <div class="ml-auto flex items-center gap-3">
                        @if(request()->route()?->getName())
                            <livewire:admin.favorite-toggle :route="request()->route()->getName()"
                                                            :key="'fav-toggle-'.request()->route()->getName()" />
                        @endif

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = ! open" type="button"
                                    class="flex items-center gap-3 rounded-full p-1 pr-2 transition hover:bg-zinc-100">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                                    {{ $initials }}
                                </span>
                                <span class="hidden text-left sm:block">
                                    <span class="block text-sm font-medium leading-tight text-zinc-900">{{ $user->name }}</span>
                                    <span class="block text-xs leading-tight text-zinc-400">Administrateur</span>
                                </span>
                                <x-lucide-chevron-down class="h-4 w-4 text-zinc-400" />
                            </button>

                            <div x-show="open" x-cloak @click.outside="open = false"
                                 x-transition.origin.top.right
                                 class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-zinc-200 bg-white py-1 shadow-lg">
                                <a href="{{ route('admin.profil') }}" wire:navigate
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-zinc-700 transition hover:bg-zinc-50">
                                    <x-lucide-user class="h-4 w-4 text-zinc-400" />
                                    Profil
                                </a>
                                <div class="my-1 h-px bg-zinc-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-600 transition hover:bg-red-50">
                                        <x-lucide-log-out class="h-4 w-4" />
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
