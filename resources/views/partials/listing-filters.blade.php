{{--
    Filter options for a product listing, as plain links so they work without JavaScript.
    Expects $filters, $priceRanges and $listingUrl; optionally $filterCategories and $selectedCategory
    (to offer a category filter) and $showPriceFilter.
--}}
@php
    $filterCategories ??= null;
    $selectedCategory ??= null;
    $showPriceFilter ??= true;
    $hasPriceFilter = ($filters['min'] ?? 0) > 0 || $filters['max'] !== null;

    $option = fn (bool $active): string => $active
        ? 'flex items-center gap-2.5 rounded-lg bg-brand-50 px-2.5 py-2 font-semibold text-brand-700'
        : 'flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-slate-600 hover:bg-brand-50/70 hover:text-brand-700';
    $dot = fn (bool $active): string => $active
        ? 'size-3.5 shrink-0 rounded-full border-4 border-brand-500 bg-white'
        : 'size-3.5 shrink-0 rounded-full border-2 border-slate-300 bg-white';
@endphp

<div class="space-y-8">
    @if ($filterCategories !== null && $filterCategories->isNotEmpty())
        <section>
            <h2 class="text-xs font-bold tracking-widest text-brand-800 uppercase">Category</h2>
            <ul class="mt-3 space-y-0.5 text-sm">
                <li>
                    <a href="{{ $listingUrl(['category' => null]) }}" rel="nofollow" class="{{ $option($selectedCategory === null) }}" @if ($selectedCategory === null) aria-current="true" @endif>
                        <span class="{{ $dot($selectedCategory === null) }}"></span> All categories
                    </a>
                </li>
                @foreach ($filterCategories as $category)
                    @php($isSelected = (bool) $selectedCategory?->is($category))
                    <li>
                        <a href="{{ $listingUrl(['category' => $category->slug]) }}" rel="nofollow" class="{{ $option($isSelected) }}" @if ($isSelected) aria-current="true" @endif>
                            <span class="{{ $dot($isSelected) }}"></span> {{ $category->name }}
                        </a>
                        @if ($category->children->isNotEmpty() && ($isSelected || ($selectedCategory && $category->children->contains($selectedCategory))))
                            <ul class="mt-0.5 ml-4 space-y-0.5 border-l-2 border-brand-100 pl-2">
                                @foreach ($category->children as $child)
                                    @php($isChildSelected = (bool) $selectedCategory?->is($child))
                                    <li>
                                        <a href="{{ $listingUrl(['category' => $child->slug]) }}" rel="nofollow" class="{{ $option($isChildSelected) }}" @if ($isChildSelected) aria-current="true" @endif>
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($showPriceFilter && $priceRanges)
        <section>
            <h2 class="text-xs font-bold tracking-widest text-brand-800 uppercase">Price</h2>
            <ul class="mt-3 space-y-0.5 text-sm">
                <li>
                    <a href="{{ $listingUrl(['min' => null, 'max' => null]) }}" rel="nofollow" class="{{ $option(! $hasPriceFilter) }}" @if (! $hasPriceFilter) aria-current="true" @endif>
                        <span class="{{ $dot(! $hasPriceFilter) }}"></span> Any price
                    </a>
                </li>
                @foreach ($priceRanges as $range)
                    @php($isSelected = $hasPriceFilter && $range['min'] === (int) $filters['min'] && $range['max'] === $filters['max'])
                    <li>
                        <a href="{{ $listingUrl(['min' => $range['min'], 'max' => $range['max']]) }}" rel="nofollow" class="{{ $option($isSelected) }}" @if ($isSelected) aria-current="true" @endif>
                            <span class="{{ $dot($isSelected) }}"></span> {{ $range['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
