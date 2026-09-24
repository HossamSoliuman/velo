@props(['product'])

@php($showPrices = (bool) \App\Models\SiteSetting::value('show_prices', true))

<article class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-brand-100 transition hover:-translate-y-0.5 hover:shadow-xl hover:ring-brand-200">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-square overflow-hidden bg-brand-50">
        @if ($product->primaryImage)
            <img src="{{ $product->primaryImage->url }}" alt="{{ $product->primaryImage->alt ?: $product->name }}" loading="lazy"
                class="size-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex size-full items-center justify-center text-brand-200">
                <x-logo-mark class="size-1/3 opacity-40" />
            </div>
        @endif
        @if ($product->is_featured)
            <span class="absolute top-3 left-3 rounded-full bg-fan-magenta px-2.5 py-1 text-[0.65rem] font-bold tracking-wide text-white uppercase">Featured</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-[0.7rem] font-semibold tracking-wider text-slate-400 uppercase">SKU {{ $product->sku }}</p>
        <h3 class="mt-1 line-clamp-2 font-bold text-ink">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-brand-600">{{ $product->name }}</a>
        </h3>

        <div class="mt-auto flex items-end justify-between gap-2 pt-4">
            <div>
                @if ($showPrices)
                    <p class="text-lg font-extrabold text-brand-600">{{ $product->formatted_price }}</p>
                @else
                    <p class="text-sm font-bold text-brand-600">Price on request</p>
                @endif
                <p class="text-xs text-slate-500">Min. qty {{ $product->minimum_qty }}</p>
            </div>
            <a href="{{ route('products.show', $product->slug) }}" class="rounded-full bg-brand-50 px-3.5 py-2 text-xs font-bold text-brand-700 transition group-hover:bg-brand-500 group-hover:text-white">
                View
            </a>
        </div>
    </div>
</article>
