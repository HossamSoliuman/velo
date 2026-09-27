<x-layouts.app :title="$search !== '' ? 'Search results for “'.$search.'”' : 'Search'" robots="noindex,follow">
    <x-page-header :title="$search !== '' ? 'Results for “'.$search.'”' : 'Search products'" eyebrow="Search" :breadcrumbs="[['Search', null]]">
        <form action="{{ route('search') }}" method="GET" role="search" class="mt-2 flex max-w-xl overflow-hidden rounded-full border-2 border-brand-200 bg-white focus-within:border-brand-400">
            <label for="search-page-query" class="sr-only">Search products by name or SKU</label>
            <input id="search-page-query" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Product name or SKU…"
                class="w-full min-w-0 bg-transparent px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:outline-none sm:px-5 sm:text-sm">
            <button type="submit" class="shrink-0 bg-brand-500 px-5 text-sm font-bold text-white transition hover:bg-brand-600 sm:px-6">Search</button>
        </form>
    </x-page-header>

    @if ($products === null)
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
            <p class="text-sm text-slate-600 sm:text-base">Type a product name or SKU code above, for example “diary” or “VPG-GS-001”.</p>

            @if ($filterCategories->isNotEmpty())
                <h2 class="mt-8 text-xs font-bold tracking-widest text-brand-800 uppercase sm:mt-10">Or browse a category</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($filterCategories as $category)
                        <li>
                            <a href="{{ route('categories.show', $category) }}" class="block rounded-full bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @else
        @include('partials.product-listing', [
            'emptyTitle' => 'No products found for “'.$search.'”',
            'emptyText' => 'Check the spelling, try a shorter word or search by SKU code — or tell us what you need and we will source it.',
        ])
    @endif
</x-layouts.app>
