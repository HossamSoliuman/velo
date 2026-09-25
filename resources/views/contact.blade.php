@php
    use App\Models\SiteSetting;

    $phone = SiteSetting::value('phone');
    $whatsapp = SiteSetting::value('whatsapp');
    $email = SiteSetting::value('email');
    $address = SiteSetting::value('address');
    $businessHours = SiteSetting::value('business_hours');
    $mapUrl = SiteSetting::value('map_embed_url');
    $socialLinks = array_filter([
        'Facebook' => SiteSetting::value('facebook_url'),
        'Instagram' => SiteSetting::value('instagram_url'),
        'LinkedIn' => SiteSetting::value('linkedin_url'),
    ]);

    $contactMethods = array_filter([
        $phone ? ['Call us', $phone, 'tel:'.preg_replace('/[^\d+]/', '', $phone), 'bg-fan-cyan', 'M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.35-.97-.85-1.09l-4.42-1.1c-.44-.11-.9.05-1.17.41l-.97 1.29a1.13 1.13 0 0 1-1.21.38 12.04 12.04 0 0 1-7.14-7.14 1.13 1.13 0 0 1 .38-1.21l1.29-.97c.36-.27.52-.73.41-1.17l-1.1-4.42a1.13 1.13 0 0 0-1.09-.85H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'] : null,
        $whatsapp ? ['WhatsApp', $whatsapp, 'https://wa.me/'.preg_replace('/\D/', '', $whatsapp), 'bg-fan-lime', 'M8.63 12h.01M12 12h.01M15.38 12h.01M21 12c0 4.56-4.03 8.25-9 8.25a9.76 9.76 0 0 1-2.55-.34 5.97 5.97 0 0 1-3.34 1.31 6 6 0 0 0 1.03-2.07C5.84 17.66 3 15.1 3 12c0-4.56 4.03-8.25 9-8.25s9 3.69 9 8.25Z'] : null,
        $email ? ['Email us', $email, 'mailto:'.$email, 'bg-fan-magenta', 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.61a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.92v-.24'] : null,
        $address ? ['Visit us', $address, null, 'bg-fan-purple', 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z'] : null,
        $businessHours ? ['Business hours', $businessHours, null, 'bg-fan-yellow', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'] : null,
    ]);
@endphp

<x-layouts.app title="Contact Us" description="Contact Velo Printing & Gifting for corporate gifting and printing enquiries and quotations.">
    <x-page-header title="Contact Us" eyebrow="Get in touch" :breadcrumbs="[['Contact Us', null]]">
        Tell us what you need — products, quantities, branding and timelines — and we'll get back to you with options and a quotation.
    </x-page-header>

    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-5 lg:px-8">
        <div class="lg:col-span-2">
            <ul class="space-y-4">
                @foreach ($contactMethods as [$heading, $value, $href, $accent, $icon])
                    <li class="flex gap-4 rounded-2xl bg-brand-50 p-5">
                        <span class="relative flex size-11 shrink-0 items-center justify-center rounded-full bg-white text-brand-600 shadow-sm">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            <span class="absolute -top-0.5 -right-0.5 size-3 rounded-full ring-2 ring-white {{ $accent }}"></span>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-xs font-bold tracking-widest text-slate-500 uppercase">{{ $heading }}</h2>
                            @if ($href)
                                <a href="{{ $href }}" @if (str_starts_with($href, 'https://')) target="_blank" rel="noopener" @endif class="mt-1 block font-semibold break-words text-brand-800 hover:text-brand-600">{{ $value }}</a>
                            @else
                                <p class="mt-1 font-semibold whitespace-pre-line text-brand-800">{{ $value }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($socialLinks)
                <div class="mt-8">
                    <h2 class="text-xs font-bold tracking-widest text-slate-500 uppercase">Follow us</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($socialLinks as $network => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="block rounded-full px-4 py-2 text-sm font-bold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50">{{ $network }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div id="enquiry" class="scroll-mt-6 lg:col-span-3">
            @include('partials.enquiry-form', ['product' => $product])
        </div>
    </section>

    @if ($mapUrl)
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl ring-1 ring-brand-100">
                <iframe src="{{ $mapUrl }}" title="Map showing our location" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen
                    class="block h-80 w-full border-0 sm:h-96"></iframe>
            </div>
        </section>
    @elseif ($address)
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start gap-4 rounded-3xl bg-brand-500 px-6 py-8 text-white sm:flex-row sm:items-center sm:justify-between sm:px-10">
                <div>
                    <h2 class="text-xl font-extrabold">Find us</h2>
                    <p class="mt-1 text-white/85">{{ $address }}</p>
                </div>
                <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($address) }}" target="_blank" rel="noopener"
                    class="shrink-0 rounded-full bg-white px-6 py-3 text-sm font-bold text-brand-700 hover:bg-brand-50">Get directions</a>
            </div>
        </section>
    @endif
</x-layouts.app>
