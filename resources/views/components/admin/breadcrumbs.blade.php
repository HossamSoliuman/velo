@props(['items' => []])

{{-- $items: list of [label, url]. The last item is the current page. --}}
<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        @foreach ($items as [$label, $url])
            <li class="flex min-w-0 items-center gap-1.5">
                @unless ($loop->first)
                    <svg class="size-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                @endunless
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="truncate font-semibold text-brand-600 hover:text-brand-800">{{ $label }}</a>
                @else
                    <span class="truncate font-semibold text-slate-700" @if ($loop->last) aria-current="page" @endif>{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
