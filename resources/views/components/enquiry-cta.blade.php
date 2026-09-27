@props([
    'heading' => 'Planning corporate gifts?',
    'text' => 'Tell us your quantity and budget — we\'ll send a tailored quotation.',
])

<section {{ $attributes->class('mx-auto max-w-7xl px-4 sm:px-6 lg:px-8') }}>
    <div class="relative overflow-hidden rounded-3xl bg-brand-700 px-5 py-10 text-white sm:px-12 sm:py-12">
        <div class="absolute inset-x-0 top-0 h-1.5 bg-fan-gradient"></div>
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-balance sm:text-3xl">{{ $heading }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-white/80 sm:text-base">{{ $text }}</p>
            </div>
            <a href="{{ route('contact') }}#enquiry" class="shrink-0 rounded-full bg-fan-magenta px-7 py-3.5 text-center text-sm font-bold shadow-lg transition hover:brightness-110">Send an Enquiry</a>
        </div>
    </div>
</section>
