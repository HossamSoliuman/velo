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
                            <img src="{{ Storage::disk('public')->url($category->image) }}" alt="" loading="lazy" class="size-16 rounded-full object-cover">
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

    {{-- Why Velo --}}
    <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-brand-50 px-6 py-12 sm:px-12">
            <h2 class="text-center text-3xl font-extrabold text-brand-800">Why choose Velo Printing &amp; Gifting</h2>
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Custom Branding', 'Printing, engraving and embroidery with your logo.', 'bg-fan-magenta'],
                    ['Bulk Orders', 'Reliable supply for events, teams and campaigns.', 'bg-fan-cyan'],
                    ['Wide Range', 'From diaries and pens to electronics and hampers.', 'bg-fan-lime'],
                    ['On-Time Delivery', 'Planned timelines so your gifts arrive when needed.', 'bg-fan-purple'],
                ] as [$heading, $text, $dot])
                    <div class="text-center">
                        <span class="mx-auto block h-1.5 w-12 rounded-full {{ $dot }}"></span>
                        <h3 class="mt-4 font-bold text-brand-800">{{ $heading }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Call to action --}}
    <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-brand-700 px-6 py-12 text-white sm:px-12">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-fan-gradient"></div>
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold sm:text-3xl">Planning corporate gifts?</h2>
                    <p class="mt-2 text-white/80">Tell us your quantity and budget — we'll send a tailored quotation.</p>
                </div>
                <a href="{{ route('contact') }}" class="shrink-0 rounded-full bg-fan-magenta px-7 py-3.5 text-center text-sm font-bold shadow-lg transition hover:brightness-110">Send an Enquiry</a>
            </div>
        </div>
    </section>
</x-layouts.app>
