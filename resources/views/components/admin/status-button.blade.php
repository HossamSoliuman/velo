@props(['action', 'field', 'value', 'on' => 'Active', 'off' => 'Hidden', 'label'])

{{-- A pill that flips one boolean field on the record when clicked. --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="{{ $field }}" value="{{ $value ? 0 : 1 }}">
    <button type="submit" title="{{ $label }}" aria-label="{{ $label }}"
        @class([
            'rounded-full px-2.5 py-1 text-xs font-bold whitespace-nowrap ring-1 transition',
            'bg-emerald-50 text-emerald-700 ring-emerald-200 hover:bg-emerald-100' => $value,
            'bg-slate-100 text-slate-500 ring-slate-200 hover:bg-slate-200' => ! $value,
        ])>
        {{ $value ? $on : $off }}
    </button>
</form>
