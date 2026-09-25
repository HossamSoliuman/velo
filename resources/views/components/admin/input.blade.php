@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'hint' => null, 'required' => false, 'id' => null, 'counter' => null])

@php
    $errorKey = str($name)->replace(['[', ']'], ['.', ''])->toString();
    $id ??= str($errorKey)->replace('.', '-')->toString();
    $currentValue = in_array($type, ['password', 'file'], true) ? null : old($errorKey, $value);
@endphp

<x-admin.field :label="$label" :for="$id" :hint="$hint" :required="$required" :error="$errorKey"
    :x-data="$counter ? '{ length: '.mb_strlen((string) $currentValue).' }' : null">
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        @if ($currentValue !== null) value="{{ $currentValue }}" @endif
        @if ($counter) x-on:input="length = $event.target.value.length" @endif
        @required($required)
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-ink shadow-xs placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:outline-none',
            'border-red-400 focus:border-red-500' => $errors->has($errorKey),
            'border-slate-300 focus:border-brand-500' => ! $errors->has($errorKey),
        ]) }}>

    @if ($counter)
        <p class="text-xs" :class="length > {{ $counter }} ? 'font-semibold text-amber-600' : 'text-slate-500'">
            <span x-text="length"></span> / {{ $counter }} characters recommended
        </p>
    @endif
</x-admin.field>
