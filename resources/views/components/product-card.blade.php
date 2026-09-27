@props(['product'])

@php($showPrices = (bool) \App\Models\SiteSetting::value('show_prices', true))

{{-- The product name's link is stretched over the whole card, so the card is one tap target on phones. --}}
<article class="group relative flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-brand-100 transition hover:-translate-y-0.5 hover:shadow-xl hover:ring-brand-200">
    <div class="relative aspect-square overflow-hidden bg-brand-50">
        @if ($product->primaryImage)
            <img src="{{ $product->primaryImage->url }}" alt="{{ $product->primaryImage->alt ?: $product->name }}" loading="lazy"
                class="size-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex size-full items-center justify-center text-brand-200">
                <x-logo-mark class="size-1/3 opacity-40" />
            </div>
        @endif
        @if ($product->is_featured)
            <span class="absolute top-2 left-2 rounded-full bg-fan-magenta px-2 py-0.5 text-[0.6rem] font-bold tracking-wide text-white uppercase sm:top-3 sm:left-3 sm:px-2.5 sm:py-1 sm:text-[0.65rem]">Featured</span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3 sm:p-4">
        <p class="truncate text-[0.65rem] font-semibold tracking-wider text-slate-400 uppercase sm:text-[0.7rem]">SKU {{ $product->sku }}</p>
        <h3 class="mt-1 line-clamp-2 text-sm leading-snug font-bold text-ink sm:text-base">
            <a href="{{ route('products.show', $product->slug) }}" class="after:absolute after:inset-0 hover:text-brand-600">{{ $product->name }}</a>
        </h3>

        <div class="mt-auto flex items-end justify-between gap-2 pt-3 sm:pt-4">
            <div class="min-w-0">
                @if ($showPrices)
                    <p class="text-base font-extrabold text-brand-600 sm:text-lg">{{ $product->formatted_price }}</p>
                @else
                    <p class="text-xs font-bold text-brand-600 sm:text-sm">Price on request</p>
                @endif
                <p class="text-[0.7rem] whitespace-nowrap text-slate-500 sm:text-xs">Min. qty {{ number_format($product->minimum_qty) }}</p>
            </div>
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 transition group-hover:bg-brand-500 group-hover:text-white sm:size-auto sm:px-3.5 sm:py-2 sm:text-xs sm:font-bold" aria-hidden="true">
                <svg class="size-4 sm:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                <span class="hidden sm:inline">View</span>
            </span>
        </div>
    </div>
</article>
