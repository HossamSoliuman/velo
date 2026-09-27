<section {{ $attributes->class('mx-auto max-w-7xl px-4 sm:px-6 lg:px-8') }}>
    <div class="rounded-3xl bg-brand-50 px-4 py-10 sm:px-12 sm:py-12">
        <p class="text-center text-xs font-bold tracking-widest text-fan-magenta uppercase sm:text-sm">Why Velo</p>
        <h2 class="mt-1.5 text-center text-2xl font-extrabold text-balance text-brand-800 sm:mt-2 sm:text-3xl">Why choose Velo Printing &amp; Gifting</h2>
        <div class="mt-7 grid grid-cols-2 gap-3 sm:mt-10 sm:gap-6 lg:grid-cols-4">
            @foreach ([
                ['Custom Branding', 'Printing, engraving and embroidery with your logo.', 'border-fan-magenta', 'M9.53 16.12a3 3 0 0 0-5.78 1.13 2.25 2.25 0 0 1-2.4 2.25 4.5 4.5 0 0 0 8.4-2.25c0-.4-.08-.78-.22-1.13Zm0 0a16 16 0 0 0 3.39-1.62m-5.04-.03a16 16 0 0 1 1.62-3.39m3.42 3.42a16 16 0 0 0 4.76-4.65l3.88-5.81a1.15 1.15 0 0 0-1.6-1.6l-5.81 3.88a16 16 0 0 0-4.65 4.76m3.42 3.42a6.78 6.78 0 0 0-3.42-3.42'],
                ['Bulk Orders', 'Reliable supply for events, teams and campaigns.', 'border-fan-cyan', 'm20.25 7.5-.63 10.63a2.25 2.25 0 0 1-2.24 2.12H6.62a2.25 2.25 0 0 1-2.24-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.13v-1.5c0-.62-.5-1.12-1.12-1.12H3.38c-.63 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.13 1.13Z'],
                ['Wide Range', 'From diaries and pens to electronics and hampers.', 'border-fan-lime', 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
                ['On-Time Delivery', 'Planned timelines so your gifts arrive when needed.', 'border-fan-purple', 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.38c-.63 0-1.13-.5-1.13-1.13V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.13c.62 0 1.13-.5 1.09-1.12a17.9 17.9 0 0 0-3.21-9.2 2.06 2.06 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.18v-.95c0-.57-.42-1.05-.99-1.11a48.55 48.55 0 0 0-10.02 0c-.57.06-.99.54-.99 1.1v7.64m12-6.68v6.68m0 4.5v-4.5m0 0h-12'],
            ] as [$heading, $text, $accent, $icon])
                <div class="rounded-2xl border-t-4 {{ $accent }} bg-white p-4 shadow-sm sm:p-6">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 sm:size-12">
                        <svg class="size-5 sm:size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <h3 class="mt-3 text-sm font-bold text-brand-800 sm:mt-4 sm:text-base">{{ $heading }}</h3>
                    <p class="mt-1 text-xs leading-relaxed text-slate-600 sm:mt-2 sm:text-sm">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
