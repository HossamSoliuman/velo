@php
    $isFiltered = ($filters['min'] ?? 0) > 0 || $filters['max'] !== null;
    $pill = fn (bool $active): string => $active
        ? 'block whitespace-nowrap rounded-full bg-brand-500 px-4 py-2 text-sm font-bold text-white'
        : 'block whitespace-nowrap rounded-full bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100';
@endphp

<x-layouts.app :title="$heading" description="Browse customisable corporate gifts and printing by budget.">
    <x-page-header :title="$heading" :eyebrow="$isFiltered ? 'Shop by Price' : 'Budget'"
        :breadcrumbs="$isFiltered ? [['Shop by Price', route('price-range')], [$heading, null]] : [['Shop by Price', null]]">
        Find branded gifts that fit your budget. Prices shown are per unit; your quotation reflects quantity and branding.
    </x-page-header>

    @if ($priceRanges)
        <nav aria-label="Price ranges" class="border-b border-brand-100 bg-white">
            <ul class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-4 [scrollbar-width:none] sm:px-6 lg:px-8">
                <li>
                    <a href="{{ $listingUrl(['min' => null, 'max' => null]) }}" class="{{ $pill(! $isFiltered) }}" @if (! $isFiltered) aria-current="page" @endif>All prices</a>
                </li>
                @foreach ($priceRanges as $range)
                    @php($isCurrent = $isFiltered && $range['min'] === (int) $filters['min'] && $range['max'] === $filters['max'])
                    <li>
                        <a href="{{ $listingUrl(['min' => $range['min'], 'max' => $range['max']]) }}" class="{{ $pill($isCurrent) }}" @if ($isCurrent) aria-current="page" @endif>{{ $range['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

    @include('partials.product-listing', ['showPriceFilter' => false])

    <x-enquiry-cta />
</x-layouts.app>
