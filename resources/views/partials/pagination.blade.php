@php
    $edge = 'inline-flex items-center gap-1.5 rounded-full px-4 py-2.5 text-sm font-bold ring-1 ring-brand-200';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-10 flex items-center justify-between gap-3 border-t border-brand-100 pt-6">
        @if ($paginator->onFirstPage())
            <span class="{{ $edge }} cursor-default text-slate-400 opacity-60" aria-disabled="true">← Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $edge }} text-brand-700 transition hover:bg-brand-50">← Previous</a>
        @endif

        <p class="text-sm text-slate-600 sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 text-slate-400">{{ $element }}</span></li>
                @else
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="flex size-10 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Page {{ $page }}" class="flex size-10 items-center justify-center rounded-full text-sm font-semibold text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $edge }} text-brand-700 transition hover:bg-brand-50">Next →</a>
        @else
            <span class="{{ $edge }} cursor-default text-slate-400 opacity-60" aria-disabled="true">Next →</span>
        @endif
    </nav>
@endif
