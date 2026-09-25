@props(['items' => []])

{{-- $items: list of [label, url] after Home. The last item is the current page. --}}
<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-xs font-semibold sm:text-sm">
        <li><a href="{{ route('home') }}" class="text-brand-600 hover:text-brand-800">Home</a></li>
        @foreach ($items as [$label, $url])
            <li class="flex min-w-0 items-center gap-1.5">
                <svg class="size-3.5 shrink-0 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="truncate text-brand-600 hover:text-brand-800">{{ $label }}</a>
                @else
                    <span class="truncate text-slate-500" @if ($loop->last) aria-current="page" @endif>{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
