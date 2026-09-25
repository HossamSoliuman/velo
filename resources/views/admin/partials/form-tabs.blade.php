{{-- Tab buttons for a form wrapped in x-data="{ tab: ... }". $tabs: [key => [label, hasErrors]] --}}
<div class="mb-6 flex flex-wrap gap-1 border-b border-slate-200" role="tablist">
    @foreach ($tabs as $key => [$label, $hasErrors])
        <button type="button" role="tab" x-on:click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
            :class="tab === '{{ $key }}' ? 'border-brand-500 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
            class="-mb-px flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-bold whitespace-nowrap">
            {{ $label }}
            @if ($hasErrors)
                <span class="size-2 rounded-full bg-red-500" aria-label="has errors"></span>
            @endif
        </button>
    @endforeach
</div>
