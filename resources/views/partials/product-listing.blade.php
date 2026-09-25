{{--
    Filters, toolbar, product grid, pagination and empty state for a product listing.
    Expects $products, $filters, $priceRanges, $sortOptions and $listingUrl; the optional filter
    variables are passed on to partials.listing-filters.
--}}
@php
    use App\Models\SiteSetting;
    use Illuminate\Support\Arr;

    $selectedCategory ??= null;
    $hasPriceFilter = ($filters['min'] ?? 0) > 0 || $filters['max'] !== null;

    $activeFilters = array_values(array_filter([
        $selectedCategory ? [$selectedCategory->name, $listingUrl(['category' => null])] : null,
        $hasPriceFilter ? [SiteSetting::priceRangeLabel($filters['min'], $filters['max']), $listingUrl(['min' => null, 'max' => null])] : null,
    ]));
@endphp

<div x-data="{ filtersOpen: false }" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:flex lg:gap-10 lg:px-8">
    <aside class="hidden w-60 shrink-0 lg:block" aria-label="Product filters">
        @include('partials.listing-filters')
    </aside>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-brand-100 pb-4">
            <p class="text-sm text-slate-600">
                @if ($products->total() > 0)
                    Showing <strong class="text-ink">{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
                    of <strong class="text-ink">{{ $products->total() }}</strong> {{ str('product')->plural($products->total()) }}
                @else
                    No products found
                @endif
            </p>

            <div class="flex items-center gap-2">
                <button type="button" x-on:click="filtersOpen = ! filtersOpen" :aria-expanded="filtersOpen" aria-controls="mobile-filters"
                    class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50 lg:hidden">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M7 12h10M10 18h4"/></svg>
                    Filters
                    @if ($activeFilters)
                        <span class="rounded-full bg-fan-magenta px-1.5 text-xs text-white">{{ count($activeFilters) }}</span>
                    @endif
                </button>

                <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-2">
                    @foreach (Arr::only(request()->query(), ['q', 'category', 'min', 'max']) as $key => $value)
                        @if (is_string($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="sort" class="hidden text-sm text-slate-500 sm:block">Sort by</label>
                    <select id="sort" name="sort" x-data x-on:change="$el.form.submit()"
                        class="rounded-full border border-brand-200 bg-white py-2 pr-8 pl-4 text-sm font-semibold text-brand-800 focus:border-brand-400 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                        @foreach ($sortOptions as $option)
                            <option value="{{ $option->value }}" @selected($filters['sort'] === $option)>{{ $option->label() }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="rounded-full bg-brand-500 px-4 py-2 text-sm font-bold text-white">Apply</button></noscript>
                </form>
            </div>
        </div>

        <div id="mobile-filters" x-show="filtersOpen" x-collapse x-cloak class="lg:hidden">
            <div class="border-b border-brand-100 py-6">
                @include('partials.listing-filters')
            </div>
        </div>

        @if ($activeFilters)
            <div class="mt-4 flex flex-wrap items-center gap-2">
                @foreach ($activeFilters as [$label, $removeUrl])
                    <a href="{{ $removeUrl }}" rel="nofollow" class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 py-1.5 pr-2.5 pl-3.5 text-xs font-bold text-brand-700 ring-1 ring-brand-100 hover:bg-brand-100">
                        {{ $label }}
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                        <span class="sr-only">Remove filter</span>
                    </a>
                @endforeach
                @if (count($activeFilters) > 1)
                    <a href="{{ $listingUrl(['category' => null, 'min' => null, 'max' => null]) }}" rel="nofollow" class="px-2 text-xs font-semibold text-slate-500 underline hover:text-brand-700">Clear all</a>
                @endif
            </div>
        @endif

        @if ($products->isNotEmpty())
            <h2 class="sr-only">Products</h2>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            {{ $products->onEachSide(1)->links('partials.pagination') }}
        @else
            <div class="mt-8 rounded-3xl border-2 border-dashed border-brand-100 px-6 py-16 text-center">
                <x-logo-mark class="mx-auto size-14 text-brand-300" />
                <h2 class="mt-5 text-xl font-extrabold text-brand-800">{{ $emptyTitle ?? 'No products match your selection' }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">
                    {{ $emptyText ?? 'Try another price range or category — or tell us what you need and we will source it for you.' }}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @if ($activeFilters)
                        <a href="{{ $listingUrl(['category' => null, 'min' => null, 'max' => null]) }}" rel="nofollow" class="rounded-full bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600">Clear filters</a>
                    @endif
                    <a href="{{ route('categories.index') }}" class="rounded-full px-5 py-2.5 text-sm font-bold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">Browse all categories</a>
                    <a href="{{ route('contact') }}" class="rounded-full px-5 py-2.5 text-sm font-bold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">Ask us to source it</a>
                </div>
            </div>
        @endif
    </div>
</div>
