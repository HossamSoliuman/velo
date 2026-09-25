<section {{ $attributes->class('mx-auto max-w-7xl px-4 sm:px-6 lg:px-8') }}>
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
