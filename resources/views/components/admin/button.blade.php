@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])

@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2',
        match ($variant) {
            'secondary' => 'bg-white text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50 focus-visible:outline-brand-500',
            'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-600',
            default => 'bg-brand-500 text-white shadow-sm hover:bg-brand-600 focus-visible:outline-brand-500',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
