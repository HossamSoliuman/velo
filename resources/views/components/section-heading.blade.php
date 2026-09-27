@props(['title', 'eyebrow' => null, 'href' => null, 'link' => 'View all'])

{{-- Eyebrow, heading and an optional "view all" link that stays visible on phones. --}}
<div {{ $attributes->class('flex items-end justify-between gap-4') }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="text-xs font-bold tracking-widest text-fan-magenta uppercase sm:text-sm">{{ $eyebrow }}</p>
        @endif
        <h2 @class(['text-2xl font-extrabold tracking-tight text-balance text-brand-800 sm:text-3xl', 'mt-1.5 sm:mt-2' => $eyebrow])>{{ $title }}</h2>
    </div>
    @if ($href)
        <a href="{{ $href }}" class="group inline-flex shrink-0 items-center gap-1 pb-1 text-sm font-semibold text-brand-600 hover:text-brand-800">
            {{ $link }} <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
        </a>
    @endif
</div>
