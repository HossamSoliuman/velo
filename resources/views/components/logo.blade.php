@props(['tagline' => true])

{{-- Wordmark: inherits text colour, so use text-white on blue and text-brand-500 on white. --}}
<span {{ $attributes->merge(['class' => 'inline-flex flex-col items-center leading-none']) }}>
    <span class="flex items-end font-extrabold tracking-tight lowercase" style="font-size: 1em">
        <span>vel</span><x-logo-mark class="ml-[0.02em] h-[0.78em] w-[0.78em] translate-y-[0.02em]" />
    </span>
    @if ($tagline)
        <span class="mt-[0.12em] font-medium tracking-wide whitespace-nowrap" style="font-size: 0.27em">Printing &amp; Gifting</span>
    @endif
</span>
