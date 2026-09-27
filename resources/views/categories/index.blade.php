@php
    $accents = [
        ['bg-fan-magenta', 'from-fan-magenta/90'],
        ['bg-fan-cyan', 'from-fan-cyan/90'],
        ['bg-fan-lime', 'from-fan-lime/90'],
        ['bg-fan-purple', 'from-fan-purple/90'],
        ['bg-fan-yellow', 'from-fan-yellow/90'],
        ['bg-fan-blue', 'from-fan-blue/90'],
    ];
@endphp

<x-layouts.app title="All Categories" description="Browse every Velo Printing & Gifting product category, from gift sets and diaries to bags, pens, electronics and printing services.">
    <x-page-header title="All Categories" eyebrow="Browse" :breadcrumbs="[['All Categories', null]]">
        Explore our full range of customisable corporate gifts and printing — every product can carry your brand.
    </x-page-header>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        @if ($categories->isEmpty())
            <div class="rounded-3xl border-2 border-dashed border-brand-100 px-5 py-12 text-center sm:px-6 sm:py-16">
                <x-logo-mark class="mx-auto size-14 text-brand-300" />
                <h2 class="mt-5 text-xl font-extrabold text-brand-800">Our catalogue is being updated</h2>
                <p class="mt-2 text-sm text-slate-600">Categories will appear here shortly. Meanwhile, tell us what you need.</p>
                <a href="{{ route('contact') }}" class="mt-6 inline-block rounded-full bg-brand-500 px-6 py-3 text-sm font-bold text-white hover:bg-brand-600">Contact us</a>
            </div>
        @else
            {{-- Phones get a compact card with the image beside the text; wider screens stack it on top. --}}
            <div class="grid gap-3 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                @foreach ($categories as $category)
                    @php
                        [$accentBar, $accentGradient] = $accents[$loop->index % count($accents)];
                        $productCount = $productCounts[$category->id] ?? 0;
                    @endphp
                    <article class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-brand-100 transition hover:-translate-y-0.5 hover:shadow-xl hover:ring-brand-200">
                        <div class="h-1.5 {{ $accentBar }}"></div>
                        <div class="flex flex-1 sm:flex-col">
                            <a href="{{ route('categories.show', $category) }}" class="relative block w-28 shrink-0 overflow-hidden bg-brand-500 max-sm:min-h-32 sm:aspect-video sm:w-auto" tabindex="-1" aria-hidden="true">
                                @if ($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="absolute inset-0 bg-linear-to-br {{ $accentGradient }} to-brand-600"></div>
                                    <x-logo-mark class="absolute -right-8 -bottom-10 size-28 text-white/15 sm:size-44" />
                                    <span class="absolute bottom-3 left-4 text-4xl font-extrabold text-white/90 sm:bottom-4 sm:left-5 sm:text-6xl">{{ mb_substr($category->name, 0, 1) }}</span>
                                @endif
                            </a>

                            <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
                                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
                                    <h2 class="text-base font-extrabold text-brand-800 sm:text-lg">
                                        <a href="{{ route('categories.show', $category) }}" class="hover:text-brand-600">{{ $category->name }}</a>
                                    </h2>
                                    <span class="shrink-0 text-xs font-semibold text-slate-500">{{ $productCount }} {{ str('product')->plural($productCount) }}</span>
                                </div>

                                @if ($category->description)
                                    <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-slate-600 sm:mt-2 sm:text-sm">{{ $category->plain_description }}</p>
                                @endif

                                @if ($category->children->isNotEmpty())
                                    <ul class="mt-3 flex flex-wrap gap-1.5 sm:mt-4 sm:gap-2" aria-label="{{ $category->name }} sub-categories">
                                        @foreach ($category->children as $child)
                                            <li>
                                                <a href="{{ route('categories.show', $child) }}" class="block rounded-full bg-brand-50 px-2.5 py-1 text-[0.7rem] font-semibold text-brand-700 hover:bg-brand-100 sm:px-3 sm:text-xs">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <a href="{{ route('categories.show', $category) }}" class="mt-auto pt-3 text-sm font-bold text-brand-600 hover:text-brand-800 sm:pt-5">
                                    View products <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <x-enquiry-cta heading="Can't find what you're looking for?" text="We source custom products too. Share your requirement and budget, and we'll suggest options." />
</x-layouts.app>
