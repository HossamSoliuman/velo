@php
    use App\Models\Category;
    use App\Models\SiteSetting;

    $phone = SiteSetting::value('phone');
    $email = SiteSetting::value('email');
    $searchQuery = is_string(request()->query('q')) ? request()->query('q') : '';
    $inlineCategories = $navigationCategories->take((int) SiteSetting::value('nav_category_limit', 7));
    $navLink = 'block whitespace-nowrap px-2.5 py-3.5 text-[0.8rem] font-semibold text-white/90 transition hover:bg-brand-600 hover:text-white aria-[current=page]:bg-brand-700 aria-[current=page]:text-white';

    // The top-level category being browsed, so its menu link can be marked as current.
    $routeCategory = request()->route('category');
    $currentTopLevelId = $routeCategory instanceof Category ? ($routeCategory->parent_id ?? $routeCategory->id) : null;
@endphp

<header x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="relative z-40">
    {{-- Contact strip --}}
    <div class="bg-brand-900 text-xs text-white/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2 sm:px-6 lg:px-8">
            <div class="flex items-center gap-5">
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="flex items-center gap-1.5 hover:text-white">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.513 2.305a1.5 1.5 0 0 1-.8 1.67l-.8.4a11.04 11.04 0 0 0 5.424 5.424l.4-.8a1.5 1.5 0 0 1 1.67-.8l2.305.513A1.5 1.5 0 0 1 18 13.352V14.5a1.5 1.5 0 0 1-1.5 1.5H15C8.096 16 2.5 10.404 2.5 3.5V3.5Z"/></svg>
                        {{ $phone }}
                    </a>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}" class="hidden items-center gap-1.5 hover:text-white sm:flex">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                        {{ $email }}
                    </a>
                @endif
            </div>
            <p class="hidden md:block">{{ SiteSetting::value('business_hours') }}</p>
        </div>
    </div>

    {{-- Logo, search and call-to-action --}}
    <div class="border-b border-brand-100 bg-white">
        <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6 lg:gap-8 lg:px-8 lg:py-4">
            <button type="button" @click="drawer = true" class="-ml-2 rounded-md p-2 text-brand-700 hover:bg-brand-50 lg:hidden" aria-label="Open menu">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <a href="{{ route('home') }}" class="shrink-0 text-brand-500" aria-label="{{ SiteSetting::value('site_name') }} home">
                <x-logo class="text-[2.6rem] lg:text-5xl" />
            </a>

            <form action="{{ route('search') }}" method="GET" role="search" class="ml-auto hidden max-w-xl flex-1 md:block">
                <label for="header-search" class="sr-only">Search products by name or SKU</label>
                <div class="flex overflow-hidden rounded-full border-2 border-brand-100 bg-brand-50/60 focus-within:border-brand-400">
                    <input id="header-search" type="search" name="q" value="{{ $searchQuery }}" placeholder="Search products or SKU…"
                        class="w-full bg-transparent px-5 py-2.5 text-sm placeholder:text-slate-400 focus:outline-none">
                    <button type="submit" class="bg-brand-500 px-5 text-white transition hover:bg-brand-600" aria-label="Search">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                    </button>
                </div>
            </form>

            <a href="{{ route('contact') }}" class="ml-auto shrink-0 rounded-full bg-fan-magenta px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:brightness-110 md:ml-0 lg:px-6">
                Get a Quote
            </a>
        </div>
    </div>

    {{-- Desktop navigation --}}
    <nav class="relative hidden bg-brand-500 lg:block" aria-label="Main">
        <ul class="mx-auto flex max-w-7xl items-stretch px-4 sm:px-6 lg:px-8">
            <li><a href="{{ route('home') }}" class="{{ $navLink }}" @if (request()->routeIs('home')) aria-current="page" @endif>Home</a></li>

            {{-- A click straight after the hover that opened a menu (or a tap, which fires both) keeps it open. --}}
            <li x-data="{ open: false, timer: null, openedAt: 0 }"
                @mouseenter="clearTimeout(timer); if (! open) { open = true; openedAt = Date.now() }"
                @mouseleave="timer = setTimeout(() => open = false, 150)"
                @click.outside="open = false"
                @keydown.escape="open = false">
                <button type="button" @click="open = Date.now() - openedAt < 500 || ! open" :aria-expanded="open" aria-controls="mega-menu"
                    class="{{ $navLink }} flex items-center gap-1" :class="open && 'bg-brand-600 text-white'">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    All Categories
                    <svg class="size-3.5 transition" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </button>

                {{-- Mega menu --}}
                <div id="mega-menu" x-show="open" x-cloak
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-end="opacity-0"
                    class="absolute inset-x-0 top-full border-t-4 border-fan-magenta bg-white shadow-2xl">
                    <div class="mx-auto grid max-h-[70vh] max-w-7xl grid-cols-4 gap-x-8 gap-y-6 overflow-y-auto px-8 py-8 xl:grid-cols-5">
                        @forelse ($navigationCategories as $category)
                            <div>
                                <a href="{{ route('categories.show', $category->slug) }}" class="text-sm font-bold text-brand-700 hover:text-fan-magenta">
                                    {{ $category->name }}
                                </a>
                                @if ($category->children->isNotEmpty())
                                    <ul class="mt-2 space-y-1.5 border-l-2 border-brand-100 pl-3">
                                        @foreach ($category->children as $child)
                                            <li>
                                                <a href="{{ route('categories.show', $child->slug) }}" class="text-sm text-slate-600 hover:text-brand-600">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @empty
                            <p class="col-span-full text-sm text-slate-500">Categories will appear here once they are added.</p>
                        @endforelse
                    </div>
                    <div class="bg-brand-50">
                        <div class="mx-auto flex max-w-7xl items-center justify-between px-8 py-3 text-sm">
                            <span class="text-slate-600">Can't find what you need? We source custom products too.</span>
                            <a href="{{ route('categories.index') }}" class="font-semibold text-brand-600 hover:text-brand-800">Browse all categories →</a>
                        </div>
                    </div>
                </div>
            </li>

            @foreach ($inlineCategories as $category)
                <li @class(['hidden', 'xl:block' => $loop->index < 4, '2xl:block' => $loop->index >= 4])>
                    <a href="{{ route('categories.show', $category->slug) }}" class="{{ $navLink }}" @if ($category->id === $currentTopLevelId) aria-current="page" @endif>{{ $category->name }}</a>
                </li>
            @endforeach

            <li class="relative" x-data="{ open: false, openedAt: 0 }" @mouseenter="if (! open) { open = true; openedAt = Date.now() }" @mouseleave="open = false" @keydown.escape="open = false">
                <button type="button" @click="open = Date.now() - openedAt < 500 || ! open" :aria-expanded="open" class="{{ $navLink }} flex items-center gap-1">
                    Price Range
                    <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </button>
                <ul x-show="open" x-cloak x-transition.opacity class="absolute left-0 top-full w-56 overflow-hidden rounded-b-lg bg-white py-2 shadow-xl ring-1 ring-brand-100">
                    @foreach ($priceRanges as $range)
                        <li>
                            <a href="{{ route('price-range', array_filter(['min' => $range['min'], 'max' => $range['max']], fn ($value) => $value !== null)) }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700">{{ $range['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>

            <li class="ml-auto"><a href="{{ route('about') }}" class="{{ $navLink }}" @if (request()->routeIs('about')) aria-current="page" @endif>About Us</a></li>
            <li><a href="{{ route('e-catalog') }}" class="{{ $navLink }}" @if (request()->routeIs('e-catalog')) aria-current="page" @endif>E-Catalog</a></li>
            <li><a href="{{ route('contact') }}" class="{{ $navLink }}" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact Us</a></li>
        </ul>
    </nav>
    <div class="h-1 bg-fan-gradient"></div>

    {{-- Mobile drawer --}}
    <div x-show="drawer" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div x-show="drawer" x-transition.opacity @click="drawer = false" class="absolute inset-0 bg-brand-950/60"></div>

        <div x-show="drawer" x-trap.noscroll="drawer"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-[85vw] max-w-sm flex-col overflow-y-auto bg-white shadow-xl">
            <div class="flex items-center justify-between bg-brand-500 px-4 py-4 text-white">
                <x-logo class="text-4xl" />
                <button type="button" @click="drawer = false" class="rounded-md p-2 hover:bg-brand-600" aria-label="Close menu">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="h-1 bg-fan-gradient"></div>

            <form action="{{ route('search') }}" method="GET" role="search" class="p-4">
                <label for="drawer-search" class="sr-only">Search products by name or SKU</label>
                <input id="drawer-search" type="search" name="q" value="{{ $searchQuery }}" placeholder="Search products or SKU…"
                    class="w-full rounded-full border-2 border-brand-100 bg-brand-50/60 px-4 py-2.5 text-sm focus:border-brand-400 focus:outline-none">
            </form>

            <nav class="flex-1 px-2 pb-6 text-sm font-semibold text-slate-800" aria-label="Mobile">
                <a href="{{ route('home') }}" class="block rounded-md px-3 py-3 hover:bg-brand-50">Home</a>

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between rounded-md px-3 py-3 hover:bg-brand-50">
                        All Categories
                        <svg class="size-4 transition" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                    <ul x-show="open" x-collapse class="ml-3 border-l-2 border-brand-100 pl-2 font-medium">
                        @foreach ($navigationCategories as $category)
                            <li x-data="{ sub: false }">
                                <div class="flex items-center">
                                    <a href="{{ route('categories.show', $category->slug) }}" class="flex-1 rounded-md px-3 py-2.5 hover:bg-brand-50">{{ $category->name }}</a>
                                    @if ($category->children->isNotEmpty())
                                        <button type="button" @click="sub = !sub" :aria-expanded="sub" class="rounded-md p-2.5 text-slate-500 hover:bg-brand-50" aria-label="Show {{ $category->name }} sub-categories">
                                            <svg class="size-4 transition" :class="sub && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                                        </button>
                                    @endif
                                </div>
                                @if ($category->children->isNotEmpty())
                                    <ul x-show="sub" x-collapse class="ml-4 font-normal text-slate-600">
                                        @foreach ($category->children as $child)
                                            <li><a href="{{ route('categories.show', $child->slug) }}" class="block rounded-md px-3 py-2 hover:bg-brand-50">{{ $child->name }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between rounded-md px-3 py-3 hover:bg-brand-50">
                        Price Range
                        <svg class="size-4 transition" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                    <ul x-show="open" x-collapse class="ml-3 border-l-2 border-brand-100 pl-2 font-medium">
                        @foreach ($priceRanges as $range)
                            <li>
                                <a href="{{ route('price-range', array_filter(['min' => $range['min'], 'max' => $range['max']], fn ($value) => $value !== null)) }}" class="block rounded-md px-3 py-2.5 hover:bg-brand-50">{{ $range['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <a href="{{ route('about') }}" class="block rounded-md px-3 py-3 hover:bg-brand-50">About Us</a>
                <a href="{{ route('e-catalog') }}" class="block rounded-md px-3 py-3 hover:bg-brand-50">E-Catalog</a>
                <a href="{{ route('contact') }}" class="block rounded-md px-3 py-3 hover:bg-brand-50">Contact Us</a>
            </nav>
        </div>
    </div>
</header>
