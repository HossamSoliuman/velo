@props(['title', 'eyebrow' => null, 'breadcrumbs' => []])

{{-- Light-blue band at the top of inner pages: breadcrumbs, heading and an optional intro in the slot. --}}
<section {{ $attributes->class('relative overflow-hidden bg-brand-50') }}>
    <x-logo-mark class="pointer-events-none absolute -top-12 -right-24 size-56 text-brand-500 opacity-10 sm:-right-20 sm:size-72 md:right-4 lg:right-16" />
    <div class="relative mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8 lg:py-14">
        <x-breadcrumbs :items="$breadcrumbs" />
        @if ($eyebrow)
            <p class="mt-4 text-xs font-bold tracking-widest text-fan-magenta uppercase sm:mt-6 sm:text-sm">{{ $eyebrow }}</p>
        @endif
        <h1 @class(['text-[1.75rem] leading-tight font-extrabold tracking-tight text-balance text-brand-800 sm:text-4xl', 'mt-1.5 sm:mt-2' => $eyebrow, 'mt-4 sm:mt-6' => ! $eyebrow])>{{ $title }}</h1>
        @if ($slot->isNotEmpty())
            <div class="mt-2.5 max-w-2xl text-sm leading-relaxed text-slate-600 sm:mt-3 sm:text-base">{{ $slot }}</div>
        @endif
    </div>
</section>
