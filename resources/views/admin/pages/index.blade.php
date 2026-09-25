<x-layouts.admin title="Pages">
    <p class="mb-6 max-w-2xl text-sm text-slate-600">Edit the wording and search settings of the website's content pages.</p>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <ul class="divide-y divide-slate-100">
            @forelse ($pages as $page)
                <li class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('admin.pages.edit', $page) }}" class="font-semibold text-ink hover:text-brand-600">{{ $page->title }}</a>
                        <p class="text-xs text-slate-500">/{{ $page->slug }} · updated {{ $page->updated_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                </li>
            @empty
                <li class="px-5 py-4 text-sm text-slate-500">No pages found. Run the database seeder to create the About Us, Privacy Policy and Terms pages.</li>
            @endforelse
        </ul>
    </div>
</x-layouts.admin>
