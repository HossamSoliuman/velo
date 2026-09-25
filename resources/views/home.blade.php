@php
    use App\Models\SiteSetting;

    $accents = ['border-fan-magenta', 'border-fan-cyan', 'border-fan-lime', 'border-fan-purple', 'border-fan-yellow', 'border-fan-blue'];
@endphp

<x-layouts.app :description="SiteSetting::value('tagline')">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-brand-500 text-white">
        <x-logo-mark class="pointer-events-none absolute -right-24 -bottom-32 size-[34rem] text-white/10 sm:-right-10 lg:right-10 lg:-bottom-20" />
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold tracking-wider uppercase ring-1 ring-white/20">
                <span class="size-2 rounded-full bg-fan-yellow"></span> Corporate gifting &amp; printing
            </p>
            <h1 class="mt-6 max-w-2xl text-4xl leading-tight font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                {{ SiteSetting::value('hero_title', 'Gifts that carry your brand') }}
                @if ($heroHighlight = SiteSetting::value('hero_highlight'))
                    <span class="text-fan-yellow">{{ $heroHighlight }}</span>
                @endif
            </h1>
            @if ($heroSubtitle = SiteSetting::value('hero_subtitle'))
                <p class="mt-6 max-w-xl text-lg text-white/85">{{ $heroSubtitle }}</p>
            @endif
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ route('categories.index') }}" class="rounded-full bg-white px-7 py-3.5 text-sm font-bold text-brand-700 shadow-lg transition hover:bg-brand-50">Explore Products</a>
                <a href="{{ route('contact') }}" class="rounded-full bg-fan-magenta px-7 py-3.5 text-sm font-bold text-white shadow-lg transition hover:brightness-110">Request a Quote</a>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold tracking-widest text-fan-magenta uppercase">Browse</p>
                    <h2 class="mt-2 text-3xl font-extrabold text-brand-800">Popular Categories</h2>
                </div>
                <a href="{{ route('categories.index') }}" class="hidden text-sm font-semibold text-brand-600 hover:text-brand-800 sm:block">View all →</a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories->take(12) as $category)
                    <a href="{{ route('categories.show', $category->slug) }}"
                        class="group flex flex-col items-center rounded-2xl border-t-4 {{ $accents[$loop->index % count($accents)] }} bg-brand-50 px-3 py-6 text-center transition hover:-translate-y-0.5 hover:bg-white hover:shadow-lg">
                        @if ($category->image)
                            <img src="{{ $category->image_url }}" alt="" loading="lazy" class="size-16 rounded-full object-cover">
                        @else
                            <span class="flex size-16 items-center justify-center rounded-full bg-white text-xl font-extrabold text-brand-500 shadow-sm">
                                {{ mb_substr($category->name, 0, 1) }}
                            </span>
                        @endif
                        <span class="mt-3 text-sm font-bold text-brand-800 group-hover:text-brand-600">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured products --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
            <p class="text-sm font-bold tracking-widest text-fan-magenta uppercase">Handpicked</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-800">Featured Products</h2>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Corporate gifting promotion --}}
    <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-brand-500 text-white">
            <x-logo-mark class="pointer-events-none absolute -bottom-36 -left-36 size-80 text-white/10 opacity-40" />
            <div class="relative grid gap-10 px-6 py-12 sm:px-12 lg:grid-cols-2 lg:items-center lg:py-16">
                <div>
                    <p class="text-sm font-bold tracking-widest text-fan-yellow uppercase">Corporate gifting</p>
                    <h2 class="mt-2 text-3xl font-extrabold text-balance sm:text-4xl">{{ SiteSetting::value('promo_title', 'Corporate gifting, handled end to end') }}</h2>
                    <p class="mt-4 max-w-xl text-white/85">{{ SiteSetting::value('promo_text', 'From welcome kits for new joiners to festive hampers for clients, we source, brand and pack gifts that fit your budget and timeline.') }}</p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('e-catalog') }}" class="rounded-full bg-white px-6 py-3 text-sm font-bold text-brand-700 shadow-lg transition hover:bg-brand-50">View E-Catalog</a>
                        <a href="{{ route('contact') }}" class="rounded-full px-6 py-3 text-sm font-bold text-white ring-2 ring-white/60 transition hover:bg-white/10">Request a Quote</a>
                    </div>
                </div>
                <ul class="grid grid-cols-2 gap-3 text-sm font-semibold sm:gap-4">
                    @foreach ([
                        ['Employee welcome kits', 'bg-fan-magenta'],
                        ['Festive hampers', 'bg-fan-yellow'],
                        ['Client appreciation', 'bg-fan-cyan'],
                        ['Events & conferences', 'bg-fan-lime'],
                        ['Awards & recognition', 'bg-fan-purple'],
                        ['Dealer & channel meets', 'bg-fan-sky'],
                    ] as [$occasion, $dot])
                        <li class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-4 ring-1 ring-white/15 sm:px-5">
                            <span class="size-2.5 shrink-0 rounded-full {{ $dot }}"></span>
                            {{ $occasion }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- New arrivals --}}
    @if ($newArrivals->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold tracking-widest text-fan-magenta uppercase">Just added</p>
                    <h2 class="mt-2 text-3xl font-extrabold text-brand-800">New Arrivals</h2>
                </div>
                <a href="{{ route('price-range', ['sort' => 'newest']) }}" class="hidden text-sm font-semibold text-brand-600 hover:text-brand-800 sm:block">See what's new →</a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    <x-why-velo class="pt-20" />

    <x-enquiry-cta class="pt-20" />
</x-layouts.app>
