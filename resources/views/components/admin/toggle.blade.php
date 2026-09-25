@props(['name', 'label', 'checked' => false, 'description' => null])

@php($isChecked = (bool) old($name, $checked))

<label {{ $attributes->class('flex cursor-pointer items-start gap-3') }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked($isChecked) class="mt-0.5 size-4 shrink-0 accent-brand-500">
    <span>
        <span class="block text-sm font-semibold text-slate-700">{{ $label }}</span>
        @if ($description)
            <span class="block text-xs text-slate-500">{{ $description }}</span>
        @endif
    </span>
</label>
