@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false])

@php($id = $name.'-input')

<x-admin.field :label="$label" :for="$id.'-editor'" :hint="$hint" :required="$required" :error="$name">
    <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ old($name, $value) }}">
    <trix-editor id="{{ $id }}-editor" input="{{ $id }}" @if ($label) aria-label="{{ $label }}" @endif
        @class(['is-invalid' => $errors->has($name)])></trix-editor>
</x-admin.field>
