<x-layouts.app title="Page not found" robots="noindex,follow">
    <section class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6">
        <x-logo-mark class="mx-auto size-16 text-brand-500" />
        <p class="mt-6 text-sm font-bold tracking-widest text-fan-magenta uppercase">Error 404</p>
        <h1 class="mt-2 text-3xl font-extrabold text-brand-800 sm:text-4xl">We couldn't find that page</h1>
        <p class="mt-4 text-slate-600">The product or page may have moved or is no longer available. Try searching, or browse our categories.</p>

        <form action="{{ route('search') }}" method="GET" role="search" class="mx-auto mt-8 flex max-w-md overflow-hidden rounded-full border-2 border-brand-200 bg-white focus-within:border-brand-400">
            <label for="not-found-search" class="sr-only">Search products by name or SKU</label>
            <input id="not-found-search" type="search" name="q" maxlength="100" placeholder="Search products or SKU…"
                class="w-full bg-transparent px-5 py-3 text-sm focus:outline-none">
            <button type="submit" class="bg-brand-500 px-6 text-sm font-bold text-white hover:bg-brand-600">Search</button>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('categories.index') }}" class="rounded-full bg-brand-500 px-6 py-3 text-sm font-bold text-white hover:bg-brand-600">Browse categories</a>
            <a href="{{ route('home') }}" class="rounded-full px-6 py-3 text-sm font-bold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">Back to home</a>
        </div>
    </section>
</x-layouts.app>
