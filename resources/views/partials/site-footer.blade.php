@php
    use App\Models\SiteSetting;

    $socialLinks = array_filter([
        'Facebook' => SiteSetting::value('facebook_url'),
        'Instagram' => SiteSetting::value('instagram_url'),
        'LinkedIn' => SiteSetting::value('linkedin_url'),
    ]);
@endphp

<footer class="mt-16 bg-brand-800 text-sm text-white/75">
    <div class="h-1 bg-fan-gradient"></div>

    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="inline-block text-white" aria-label="Home">
                <x-logo class="text-5xl" />
            </a>
            <p class="mt-5 leading-relaxed">{{ SiteSetting::value('tagline') }}</p>
            @if ($socialLinks)
                <ul class="mt-5 flex gap-3">
                    @foreach ($socialLinks as $network => $url)
                        <li><a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20">{{ $network }}</a></li>
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

        <div>
            <h2 class="text-xs font-bold tracking-widest text-white uppercase">Get in Touch</h2>
            <address class="mt-4 space-y-2.5 not-italic">
                <p>{{ SiteSetting::value('address') }}</p>
                @if ($phone = SiteSetting::value('phone'))
                    <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a></p>
                @endif
                @if ($email = SiteSetting::value('email'))
                    <p><a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></p>
                @endif
                <p>{{ SiteSetting::value('business_hours') }}</p>
            </address>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-5 text-xs sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} {{ SiteSetting::value('site_name') }}. All rights reserved.</p>
            <ul class="flex gap-5">
                <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}" class="hover:text-white">Terms &amp; Conditions</a></li>
            </ul>
        </div>
    </div>
</footer>
