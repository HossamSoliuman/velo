@props(['url' => null])

{{-- A square image, or the Velo mark when there is none. Size it with a size-* class. --}}
@if ($url)
    <img src="{{ $url }}" alt="" loading="lazy" {{ $attributes->class('shrink-0 rounded-lg bg-slate-100 object-cover ring-1 ring-slate-200') }}>
@else
    <span {{ $attributes->class('flex shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-300') }}><x-logo-mark class="size-1/2" /></span>
@endif
