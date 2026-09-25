@php
    use App\Models\SiteSetting;
    use Illuminate\Support\Number;
@endphp

<x-layouts.app title="E-Catalog" description="View or download the Velo Printing & Gifting product catalogue.">
    <x-page-header title="E-Catalog" eyebrow="Our catalogue" :breadcrumbs="[['E-Catalog', null]]">
        Our complete range of corporate gifts and printing in one document. View it online or download it to share with your team.
    </x-page-header>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($url)
            <div class="flex flex-col gap-6 rounded-3xl bg-white p-6 ring-1 ring-brand-100 sm:p-8 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <span class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-fan-magenta/10 text-fan-magenta">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.63a3.38 3.38 0 0 0-3.38-3.37h-1.5A1.13 1.13 0 0 1 13.5 7.13v-1.5a3.38 3.38 0 0 0-3.38-3.38H8.25m2.25 0H5.63c-.63 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 0 0-9-9Z"/><path stroke-linecap="round" d="M8.25 15.75h7.5M8.25 18.75h4.5"/></svg>
                    </span>
                    <div>
                        <h2 class="text-xl font-extrabold text-brand-800">{{ SiteSetting::value('site_name', config('app.name')) }} catalogue</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            PDF · {{ Number::fileSize($size, maxPrecision: 1) }}
                            @if ($updatedAt)
                                · Updated {{ $updatedAt->format('j M Y') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('e-catalog.download') }}" class="rounded-full bg-fan-magenta px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:brightness-110">Download PDF</a>
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-full px-6 py-3 text-sm font-bold text-brand-700 ring-1 ring-brand-200 transition hover:bg-brand-50">Open in new tab</a>
                </div>
            </div>

            <div class="mt-8 hidden overflow-hidden rounded-3xl bg-brand-50 ring-1 ring-brand-100 md:block">
                <iframe src="{{ $url }}#view=FitH" title="E-catalog preview" loading="lazy" class="h-[80vh] w-full"></iframe>
            </div>
            <p class="mt-4 text-sm text-slate-500 md:hidden">Open the catalogue in a new tab to zoom in and swipe through the pages.</p>
        @else
            <div class="rounded-3xl border-2 border-dashed border-brand-100 px-6 py-16 text-center">
                <x-logo-mark class="mx-auto size-14 text-brand-300" />
                <h2 class="mt-5 text-xl font-extrabold text-brand-800">Our new catalogue is on its way</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">Browse our products online in the meantime, or ask us to email you the latest catalogue.</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('categories.index') }}" class="rounded-full bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600">Browse all categories</a>
                    <a href="{{ route('contact') }}" class="rounded-full px-5 py-2.5 text-sm font-bold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">Request the catalogue</a>
                </div>
            </div>
        @endif
    </section>

    <x-enquiry-cta />
</x-layouts.app>
