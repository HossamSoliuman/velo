@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false, 'id' => null])

@php
    $errorKey = str($name)->replace(['[', ']'], ['.', ''])->toString();
    $id ??= str($errorKey)->replace('.', '-')->toString();
    $selected = (string) old($errorKey, $value);
@endphp

<x-admin.field :label="$label" :for="$id" :hint="$hint" :required="$required" :error="$errorKey">
    <select id="{{ $id }}" name="{{ $name }}" @required($required)
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:ring-2 focus:ring-brand-500/20 focus:outline-none',
            'border-red-400 focus:border-red-500' => $errors->has($errorKey),
            'border-slate-300 focus:border-brand-500' => ! $errors->has($errorKey),
        ]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-admin.field>
