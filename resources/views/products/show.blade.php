@php
    use App\Models\SiteSetting;
    use Illuminate\Support\Str;

    $showPrices = (bool) SiteSetting::value('show_prices', true);
    $whatsapp = preg_replace('/\D/', '', (string) SiteSetting::value('whatsapp'));
    $phone = SiteSetting::value('phone');
    $images = $product->images;
    $breadcrumbs = array_values(array_filter([
        $primaryCategory?->parent ? [$primaryCategory->parent->name, route('categories.show', $primaryCategory->parent)] : null,
        $primaryCategory ? [$primaryCategory->name, route('categories.show', $primaryCategory)] : null,
        [$product->name, null],
    ]));
@endphp

<x-layouts.app :title="$product->name" :description="Str::limit($product->plain_description, 160)" :seo="$product" :image="$images->first()?->url" type="product">
    <x-slot:head>
        <x-json-ld :data="$product->structuredData($primaryCategory)" />
    </x-slot:head>

    <div class="border-b border-brand-100 bg-brand-50">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <x-breadcrumbs :items="$breadcrumbs" />
        </div>
    </div>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
            {{-- Gallery --}}
            <div x-data="{ active: 0 }">
                <div class="relative aspect-square overflow-hidden rounded-3xl bg-white ring-1 ring-brand-100">
                    @forelse ($images as $image)
                        <img src="{{ $image->url }}" alt="{{ $image->alt ?: $product->name }}"
                            x-show="active === {{ $loop->index }}" @unless ($loop->first) x-cloak loading="lazy" @endunless
                            class="size-full object-contain">
                    @empty
                        <div class="flex size-full items-center justify-center bg-brand-50">
                            <x-logo-mark class="size-1/3 text-brand-300 opacity-50" />
                        </div>
                    @endforelse

                    @if ($product->is_featured)
                        <span class="absolute top-4 left-4 rounded-full bg-fan-magenta px-3 py-1 text-xs font-bold tracking-wide text-white uppercase">Featured</span>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <ul class="mt-4 grid grid-cols-5 gap-3" aria-label="Product images">
                        @foreach ($images as $image)
                            <li>
                                <button type="button" x-on:click="active = {{ $loop->index }}" :aria-pressed="(active === {{ $loop->index }}).toString()"
                                    class="block aspect-square w-full overflow-hidden rounded-xl bg-white ring-2 ring-transparent transition hover:ring-brand-200"
                                    :class="active === {{ $loop->index }} && 'ring-brand-500!'">
                                    <img src="{{ $image->url }}" alt="" loading="lazy" class="size-full object-cover">
                                    <span class="sr-only">Show image {{ $loop->iteration }} of {{ $loop->count }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Summary --}}
            <div>
                @if ($product->categories->isNotEmpty())
                    <ul class="flex flex-wrap gap-2" aria-label="Categories">
                        @foreach ($product->categories as $category)
                            <li>
                                <a href="{{ route('categories.show', $category) }}" class="block rounded-full bg-brand-50 px-3 py-1 text-xs font-bold tracking-wide text-brand-700 uppercase hover:bg-brand-100">{{ $category->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-balance text-brand-800 sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-2 text-sm font-semibold tracking-wider text-slate-500 uppercase">SKU <span class="text-ink">{{ $product->sku }}</span></p>

                @if ($product->plain_description !== '')
                    <p class="mt-5 line-clamp-3 text-slate-600">{{ $product->plain_description }}</p>
                @endif

                <div class="mt-8 rounded-2xl bg-brand-50 p-6 ring-1 ring-brand-100">
                    <dl class="flex flex-wrap items-end justify-between gap-6">
                        <div>
                            <dt class="text-xs font-bold tracking-widest text-slate-500 uppercase">Price</dt>
                            @if ($showPrices)
                                <dd class="mt-1 text-3xl font-extrabold text-brand-600">{{ $product->formatted_price }} <span class="text-sm font-semibold text-slate-500">per unit</span></dd>
                            @else
                                <dd class="mt-1 text-2xl font-extrabold text-brand-600">Price on request</dd>
                            @endif
                        </div>
                        <div class="sm:text-right">
                            <dt class="text-xs font-bold tracking-widest text-slate-500 uppercase">Minimum quantity</dt>
                            <dd class="mt-1 text-2xl font-extrabold text-ink">{{ number_format($product->minimum_qty) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('contact', ['product' => $product->slug]) }}#enquiry" x-data x-on:click.prevent="$dispatch('open-enquiry')" aria-haspopup="dialog"
                            class="flex-1 rounded-full bg-fan-magenta px-6 py-3.5 text-center text-sm font-bold text-white shadow-lg transition hover:brightness-110">
                            Enquire Now
                        </a>
                        @if ($whatsapp !== '')
                            <a href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode("Hello, I'd like a quote for {$product->name} (SKU {$product->sku}): ".route('products.show', $product)) }}"
                                target="_blank" rel="noopener"
                                class="flex-1 rounded-full bg-white px-6 py-3.5 text-center text-sm font-bold text-brand-700 ring-1 ring-brand-200 transition hover:bg-brand-100">
                                Chat on WhatsApp
                            </a>
                        @elseif ($phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}"
                                class="flex-1 rounded-full bg-white px-6 py-3.5 text-center text-sm font-bold text-brand-700 ring-1 ring-brand-200 transition hover:bg-brand-100">
                                Call {{ $phone }}
                            </a>
                        @endif
                    </div>
                    <p class="mt-4 text-xs text-slate-500">Share your quantity, branding and delivery date, and we'll reply with a quotation.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Full description --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 border-t border-brand-100 pt-10 lg:grid-cols-3 lg:gap-14">
            <div>
                <h2 class="text-2xl font-extrabold text-brand-800">Product details</h2>
                <dl class="mt-6 divide-y divide-brand-100 text-sm">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-slate-500">SKU</dt>
                        <dd class="font-semibold text-ink">{{ $product->sku }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-slate-500">Minimum quantity</dt>
                        <dd class="font-semibold text-ink">{{ number_format($product->minimum_qty) }}</dd>
                    </div>
                    @if ($product->categories->isNotEmpty())
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-slate-500">Category</dt>
                            <dd class="text-right font-semibold text-ink">{{ $product->categories->pluck('name')->join(', ') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
            <div class="rich-text lg:col-span-2">
                {!! $product->description !!}
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
            <p class="text-sm font-bold tracking-widest text-fan-magenta uppercase">More to explore</p>
            <h2 class="mt-2 text-3xl font-extrabold text-brand-800">Related Products</h2>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                @foreach ($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
    @endif

    <x-enquiry-cta class="pt-16" />

    {{-- Enquiry modal, opened by Enquire Now. Without JavaScript, Enquire Now opens the contact page instead. --}}
    <div x-data="{ open: false }" x-on:open-enquiry.window="open = true" x-on:close-enquiry.window="open = false" x-on:keydown.escape.window="open = false">
        <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="enquiry-heading">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-brand-950/70"></div>
            <div class="fixed inset-0 overflow-y-auto">
                <div x-on:click.self="open = false" class="flex min-h-full items-end justify-center sm:items-center sm:p-6">
                    <div x-show="open" x-trap.inert.noscroll="open"
                        x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="relative w-full max-w-2xl">
                        <button type="button" x-on:click="open = false" aria-label="Close"
                            class="absolute top-4 right-4 z-10 rounded-full p-2 text-slate-500 hover:bg-brand-50 hover:text-brand-700 sm:top-6 sm:right-6">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                        @include('partials.enquiry-form', ['product' => $product, 'inModal' => true])
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
