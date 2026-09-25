@props(['title', 'eyebrow' => null, 'breadcrumbs' => []])

{{-- Light-blue band at the top of inner pages: breadcrumbs, heading and an optional intro in the slot. --}}
<section {{ $attributes->class('relative overflow-hidden bg-brand-50') }}>
    <x-logo-mark class="pointer-events-none absolute -top-12 -right-20 size-72 text-brand-500 opacity-10 sm:right-4 lg:right-16" />
    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <x-breadcrumbs :items="$breadcrumbs" />
        @if ($eyebrow)
            <p class="mt-6 text-sm font-bold tracking-widest text-fan-magenta uppercase">{{ $eyebrow }}</p>
        @endif
        <h1 @class(['text-3xl font-extrabold tracking-tight text-balance text-brand-800 sm:text-4xl', 'mt-2' => $eyebrow, 'mt-6' => ! $eyebrow])>{{ $title }}</h1>
        @if ($slot->isNotEmpty())
            <div class="mt-3 max-w-2xl text-slate-600">{{ $slot }}</div>
        @endif
    </div>
</section>
