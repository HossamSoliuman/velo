@php
    use App\Models\SiteSetting;

    $accents = ['border-fan-magenta', 'border-fan-cyan', 'border-fan-lime', 'border-fan-purple', 'border-fan-yellow', 'border-fan-blue'];
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => SiteSetting::value('site_name', config('app.name')),
        'url' => route('home'),
        'email' => SiteSetting::value('email'),
        'telephone' => SiteSetting::value('phone'),
        'address' => SiteSetting::value('address'),
        'sameAs' => array_values(array_filter([
            SiteSetting::value('facebook_url'),
            SiteSetting::value('instagram_url'),
            SiteSetting::value('linkedin_url'),
        ])),
    ], fn (mixed $value): bool => filled($value));
@endphp

<x-layouts.app :meta-title="SiteSetting::value('home_meta_title')" :description="SiteSetting::value('home_meta_description') ?: SiteSetting::value('tagline')">
    <x-slot:head>
        <x-json-ld :data="$organization" />
    </x-slot:head>

    {{-- Hero --}}
    <section class="overflow-hidden bg-brand-500 text-white">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-10 sm:gap-14 sm:px-6 sm:py-14 lg:grid-cols-2 lg:gap-10 lg:px-8 lg:py-16">
            <div>
                <h1 class="text-4xl leading-[1.05] font-extrabold tracking-tight text-balance sm:text-5xl lg:text-[3.75rem]">
                    {{ SiteSetting::value('hero_title', 'Gifts that carry your brand') }}
                    @if ($heroHighlight = SiteSetting::value('hero_highlight'))
                        <span class="text-fan-yellow">{{ $heroHighlight }}</span>
                    @endif
                </h1>
                @if ($heroSubtitle = SiteSetting::value('hero_subtitle'))
                    <p class="mt-5 max-w-lg text-base leading-relaxed text-white/85 sm:mt-6 sm:text-lg">{{ $heroSubtitle }}</p>
                @endif
                <div class="mt-8 flex flex-wrap items-center gap-3 sm:mt-10 sm:gap-x-8 sm:gap-y-4">
                    <a href="{{ route('contact') }}" class="flex-1 rounded-full bg-fan-magenta px-5 py-3.5 text-center text-sm font-bold whitespace-nowrap text-white transition hover:brightness-110 sm:flex-none sm:px-7">Request a Quote</a>
                    <a href="{{ route('categories.index') }}" class="group inline-flex flex-1 items-center justify-center gap-2 rounded-full px-5 py-3.5 text-sm font-bold whitespace-nowrap text-white ring-2 ring-white/40 sm:flex-none sm:justify-start sm:px-0 sm:ring-0">
                        Browse Products <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span>
                    </a>
                </div>
            </div>

            <x-imprint-preview />
        </div>
    </section>

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 sm:pt-20 lg:px-8">
            <x-section-heading eyebrow="Browse" title="Popular Categories" :href="route('categories.index')" />

            <div class="mt-6 grid grid-cols-3 gap-2.5 sm:mt-8 sm:grid-cols-4 sm:gap-4 lg:grid-cols-6">
                @foreach ($categories->take(12) as $category)
                    <a href="{{ route('categories.show', $category->slug) }}"
                        class="group flex flex-col items-center rounded-2xl border-t-4 {{ $accents[$loop->index % count($accents)] }} bg-brand-50 px-1.5 py-4 text-center transition hover:-translate-y-0.5 hover:bg-white hover:shadow-lg sm:px-3 sm:py-6">
                        @if ($category->image)
                            <img src="{{ $category->image_url }}" alt="" loading="lazy" class="size-12 rounded-full object-cover sm:size-16">
                        @else
                            <span class="flex size-12 items-center justify-center rounded-full bg-white text-lg font-extrabold text-brand-500 shadow-sm sm:size-16 sm:text-xl">
                                {{ mb_substr($category->name, 0, 1) }}
                            </span>
                        @endif
                        <span class="mt-2.5 text-xs leading-tight font-bold text-brand-800 group-hover:text-brand-600 sm:mt-3 sm:text-sm">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured products --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 sm:pt-20 lg:px-8">
            <x-section-heading eyebrow="Handpicked" title="Featured Products" />

            <div class="mt-6 grid grid-cols-2 gap-3 sm:mt-8 sm:gap-6 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Corporate gifting promotion --}}
    <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 sm:pt-20 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-brand-500 text-white">
            <x-logo-mark class="pointer-events-none absolute -bottom-36 -left-36 size-80 text-white/10 opacity-40 max-lg:hidden" />
            <div class="relative grid gap-8 px-5 py-10 sm:gap-10 sm:px-12 sm:py-12 lg:grid-cols-2 lg:items-center lg:py-16">
                <div>
                    <p class="text-xs font-bold tracking-widest text-fan-yellow uppercase sm:text-sm">Corporate gifting</p>
                    <h2 class="mt-1.5 text-2xl font-extrabold text-balance sm:mt-2 sm:text-4xl">{{ SiteSetting::value('promo_title', 'Corporate gifting, handled end to end') }}</h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/85 sm:mt-4 sm:text-base">{{ SiteSetting::value('promo_text', 'From welcome kits for new joiners to festive hampers for clients, we source, brand and pack gifts that fit your budget and timeline.') }}</p>
                    <div class="mt-6 flex flex-wrap gap-3 sm:mt-8 sm:gap-4">
                        <a href="{{ route('e-catalog') }}" class="flex-1 rounded-full bg-white px-5 py-3 text-center text-sm font-bold whitespace-nowrap text-brand-700 shadow-lg transition hover:bg-brand-50 sm:flex-none sm:px-6">View E-Catalog</a>
                        <a href="{{ route('contact') }}" class="flex-1 rounded-full px-5 py-3 text-center text-sm font-bold whitespace-nowrap text-white ring-2 ring-white/60 transition hover:bg-white/10 sm:flex-none sm:px-6">Request a Quote</a>
                    </div>
                </div>
                <ul class="grid grid-cols-2 gap-2.5 text-[0.8rem] leading-snug font-semibold sm:gap-4 sm:text-sm" aria-label="Occasions we cater for">
                    @foreach ([
                        ['Employee welcome kits', 'bg-fan-magenta'],
                        ['Festive hampers', 'bg-fan-yellow'],
                        ['Client appreciation', 'bg-fan-cyan'],
                        ['Events & conferences', 'bg-fan-lime'],
                        ['Awards & recognition', 'bg-fan-purple'],
                        ['Dealer & channel meets', 'bg-fan-sky'],
                    ] as [$occasion, $dot])
                        <li class="flex items-center gap-2.5 rounded-2xl bg-white/10 px-3 py-3 ring-1 ring-white/15 sm:gap-3 sm:px-5 sm:py-4">
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
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 sm:pt-20 lg:px-8">
            <x-section-heading eyebrow="Just added" title="New Arrivals" :href="route('price-range', ['sort' => 'newest'])" link="See what's new" />

            <div class="mt-6 grid grid-cols-2 gap-3 sm:mt-8 sm:gap-6 lg:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    <x-why-velo class="pt-14 sm:pt-20" />

    <x-enquiry-cta class="pt-14 sm:pt-20" />
</x-layouts.app>
