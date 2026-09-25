@props(['title' => null, 'description' => null])

<section {{ $attributes->class('rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6') }}>
    @if ($title)
        <header class="mb-5">
            <h2 class="text-base font-bold text-brand-800">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </header>
    @endif

    {{ $slot }}
</section>
