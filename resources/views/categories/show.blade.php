@php
    use Illuminate\Support\Str;

    $topLevel = $category->parent ?? $category;
    $breadcrumbs = array_values(array_filter([
        ['All Categories', route('categories.index')],
        $category->parent ? [$category->parent->name, route('categories.show', $category->parent)] : null,
        [$category->name, null],
    ]));
    $isPriceFiltered = ($filters['min'] ?? 0) > 0 || $filters['max'] !== null;
    $chip = fn (bool $active): string => $active
        ? 'block whitespace-nowrap rounded-full bg-brand-500 px-4 py-2 text-sm font-bold text-white'
        : 'block whitespace-nowrap rounded-full bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100';
@endphp

<x-layouts.app :title="$category->name" :description="Str::limit($category->plain_description, 160)" :seo="$category" :canonical="$canonicalUrl" :image="$category->image_url">
    <x-page-header :title="$category->name" :eyebrow="$category->parent?->name ?? 'Category'" :breadcrumbs="$breadcrumbs">
        @if ($category->description)
            <div class="rich-text">{!! $category->description !!}</div>
        @endif
    </x-page-header>

    @if ($subCategories->isNotEmpty())
        <nav aria-label="{{ $topLevel->name }} sub-categories" class="border-b border-brand-100 bg-white">
            <ul class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-4 [scrollbar-width:none] sm:px-6 lg:px-8">
                <li>
                    <a href="{{ route('categories.show', $topLevel) }}" class="{{ $chip($category->is($topLevel)) }}" @if ($category->is($topLevel)) aria-current="page" @endif>All {{ $topLevel->name }}</a>
                </li>
                @foreach ($subCategories as $subCategory)
                    <li>
                        <a href="{{ route('categories.show', $subCategory) }}" class="{{ $chip($category->is($subCategory)) }}" @if ($category->is($subCategory)) aria-current="page" @endif>{{ $subCategory->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

    @include('partials.product-listing', [
        'emptyTitle' => $isPriceFiltered ? null : 'No products in this category yet',
        'emptyText' => $isPriceFiltered ? null : 'New products are added regularly. Tell us what you need and we will source it for you.',
    ])

    <x-enquiry-cta />
</x-layouts.app>
