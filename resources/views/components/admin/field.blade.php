@props(['label' => null, 'for' => null, 'error' => null, 'hint' => null, 'required' => false])

<div {{ $attributes->class('space-y-1.5') }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="block text-sm font-semibold text-slate-700">
            {{ $label }}@if ($required)<span class="text-fan-magenta" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if ($error)
        @error($error)
            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
