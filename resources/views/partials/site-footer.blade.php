@php
    use App\Models\SiteSetting;

    $socialLinks = array_filter([
        'Facebook' => SiteSetting::value('facebook_url'),
        'Instagram' => SiteSetting::value('instagram_url'),
        'LinkedIn' => SiteSetting::value('linkedin_url'),
    ]);

    // [icon path, text, link] for each contact detail that is filled in.
    $phone = SiteSetting::value('phone');
    $email = SiteSetting::value('email');
    $contactLines = array_filter([
        ['M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z', SiteSetting::value('address'), null],
        ['M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.35-.97-.85-1.09l-4.42-1.1c-.44-.11-.9.05-1.17.41l-.97 1.29a1.13 1.13 0 0 1-1.21.38 12.04 12.04 0 0 1-7.14-7.14 1.13 1.13 0 0 1 .38-1.21l1.29-.97c.36-.27.52-.73.41-1.17l-1.1-4.42a1.13 1.13 0 0 0-1.09-.85H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z', $phone, $phone ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null],
        ['M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.61a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.92v-.24', $email, $email ? 'mailto:'.$email : null],
        ['M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', SiteSetting::value('business_hours'), null],
    ], fn (array $line): bool => filled($line[1]));
@endphp

<footer class="mt-14 bg-brand-800 text-sm text-white/75 sm:mt-16">
    <div class="h-1 bg-fan-gradient"></div>

    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-x-6 gap-y-10 px-4 py-12 sm:px-6 sm:py-14 lg:grid-cols-4 lg:px-8">
        <div class="col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" class="inline-block text-white" aria-label="Home">
                <x-logo class="text-5xl" />
            </a>
            <p class="mt-4 max-w-xs leading-relaxed sm:mt-5">{{ SiteSetting::value('tagline') }}</p>
            @if ($socialLinks)
                <ul class="mt-5 flex flex-wrap gap-2 sm:gap-3">
                    @foreach ($socialLinks as $network => $url)
                        <li><a href="{{ $url }}" target="_blank" rel="noopener" class="block rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20">{{ $network }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h2 class="text-xs font-bold tracking-widest text-white uppercase">Quick Links</h2>
            <ul class="mt-4 space-y-2.5">
                <li><a href="{{ route('categories.index') }}" class="hover:text-white">All Categories</a></li>
                <li><a href="{{ route('price-range') }}" class="hover:text-white">Shop by Price</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                <li><a href="{{ route('e-catalog') }}" class="hover:text-white">E-Catalog</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">Contact Us</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-xs font-bold tracking-widest text-white uppercase">Categories</h2>
            <ul class="mt-4 space-y-2.5">
                @foreach ($navigationCategories->take(6) as $category)
                    <li><a href="{{ route('categories.show', $category->slug) }}" class="hover:text-white">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="col-span-2 lg:col-span-1">
            <h2 class="text-xs font-bold tracking-widest text-white uppercase">Get in Touch</h2>
            <address class="mt-4 grid gap-3 not-italic sm:grid-cols-2 lg:grid-cols-1">
                @foreach ($contactLines as [$icon, $value, $href])
                    <p class="flex items-start gap-3">
                        <svg class="mt-0.5 size-4 shrink-0 text-white/50" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        @if ($href)
                            <a href="{{ $href }}" class="min-w-0 break-words hover:text-white">{{ $value }}</a>
                        @else
                            <span class="min-w-0">{{ $value }}</span>
                        @endif
                    </p>
                @endforeach
            </address>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-3 px-4 py-5 text-center text-xs sm:flex-row sm:justify-between sm:px-6 sm:text-left lg:px-8">
            <p>&copy; {{ now()->year }} {{ SiteSetting::value('site_name') }}. All rights reserved.</p>
            <ul class="flex gap-5">
                <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}" class="hover:text-white">Terms &amp; Conditions</a></li>
            </ul>
        </div>
    </div>
</footer>
