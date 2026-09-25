@props(['title' => 'Dashboard'])

@php
    use Illuminate\Support\Facades\Route;

    $navigation = [
        ['Dashboard', 'admin.dashboard', 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['Categories', 'admin.categories.index', 'M4 6h7v7H4zM13 6h7v7h-7zM4 15h7v5H4zM13 15h7v5h-7z'],
        ['Products', 'admin.products.index', 'M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['Enquiries', 'admin.enquiries.index', 'M3 8l9 6 9-6M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z'],
        ['Pages', 'admin.pages.index', 'M7 3h7l5 5v13H7zM14 3v5h5'],
        ['E-Catalog', 'admin.e-catalog.edit', 'M12 4v12m0 0-4-4m4 4 4-4M4 20h16'],
        ['Settings', 'admin.settings.edit', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ $title }} | Velo Admin</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
    </head>
    <body class="bg-slate-50" x-data="{ sidebar: false }">
        {{-- Sidebar --}}
        <div x-show="sidebar" x-cloak x-transition.opacity @click="sidebar = false" class="fixed inset-0 z-30 bg-brand-950/60 lg:hidden"></div>
        <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-brand-900 text-white/80 transition-transform lg:translate-x-0">
            <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="flex items-center gap-3 px-6 py-5 text-white">
                <x-logo class="text-4xl" :tagline="false" />
                <span class="rounded bg-white/10 px-2 py-0.5 text-[0.65rem] font-bold tracking-widest uppercase">Admin</span>
            </a>
            <div class="h-1 bg-fan-gradient"></div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-6 text-sm font-semibold" aria-label="Admin">
                @foreach ($navigation as [$label, $routeName, $icon])
                    @php($isCurrent = request()->routeIs(substr_count($routeName, '.') > 1 ? str($routeName)->beforeLast('.').'.*' : $routeName))
                    <a href="{{ Route::has($routeName) ? route($routeName) : '#' }}"
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 transition',
                            'bg-brand-500 text-white' => $isCurrent,
                            'hover:bg-white/10 hover:text-white' => ! $isCurrent,
                        ])
                        @if ($isCurrent) aria-current="page" @endif>
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                        {{ $label }}
                        @if ($routeName === 'admin.enquiries.index' && $unreadEnquiries > 0)
                            <span class="ml-auto rounded-full bg-fan-magenta px-2 py-0.5 text-xs font-bold text-white">{{ $unreadEnquiries }}<span class="sr-only"> unread</span></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <a href="{{ route('home') }}" target="_blank" class="mx-3 mb-4 rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 hover:text-white">View website ↗</a>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-slate-200 bg-white px-4 py-3 sm:px-6">
                <button type="button" @click="sidebar = true" class="-ml-1 rounded-md p-2 text-brand-700 hover:bg-brand-50 lg:hidden" aria-label="Open sidebar">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-bold text-brand-800">{{ $title }}</h1>

                <div class="ml-auto flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ Route::has('admin.account.edit') ? route('admin.account.edit') : '#' }}" class="hidden font-semibold text-slate-600 hover:text-brand-700 sm:inline">{{ auth()->user()->name }}</a>
                        @if (Route::has('admin.logout'))
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full bg-brand-50 px-4 py-1.5 font-semibold text-brand-700 hover:bg-brand-100">Log out</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border-l-4 border-fan-lime bg-white px-4 py-3 text-sm text-slate-700 shadow-sm" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('warnings'))
                    <div class="mb-6 rounded-lg border-l-4 border-fan-yellow bg-white px-4 py-3 text-sm text-slate-700 shadow-sm" role="status">
                        <p class="font-semibold text-slate-800">SEO suggestions</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5">
                            @foreach (session('warnings') as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-white px-4 py-3 text-sm text-red-700 shadow-sm" role="alert">
                        Some fields need your attention. Please check the highlighted fields below.
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
