<x-layouts.admin title="Categories">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-slate-600">Top-level categories, in the order they appear on the website and in the menu, lowest number first. Open a category to manage its sub-categories.</p>
        <x-admin.button :href="route('admin.categories.create')" class="shrink-0">Add category</x-admin.button>
    </div>

    <form method="GET" action="{{ route('admin.categories.index') }}" role="search" class="mb-6 flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center">
        <label for="search" class="sr-only">Find a category or sub-category</label>
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m21 21-4.3-4.3M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/></svg>
            <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Find a category or sub-category by name or slug"
                class="block w-full rounded-lg border border-slate-300 bg-white py-2 pr-3 pl-9 text-sm text-ink focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        </div>
        <div class="flex items-center gap-2">
            <x-admin.button variant="secondary">Search</x-admin.button>
            @if ($search !== '')
                <a href="{{ route('admin.categories.index') }}" class="rounded-full px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Clear</a>
            @endif
        </div>
    </form>

    @if ($search !== '')
        @include('admin.categories.search-results')
    @elseif ($categories->isEmpty())
        <x-admin.card>
            <p class="text-sm text-slate-500">No categories yet. <a href="{{ route('admin.categories.create') }}" class="font-semibold text-brand-600 hover:text-brand-800">Add the first one.</a></p>
        </x-admin.card>
    @else
        <form id="category-order" method="POST" action="{{ route('admin.category-order.update') }}">
            @csrf
            @method('PATCH')
        </form>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="w-24 px-4 py-3">Order</th>
                            <th scope="col" class="px-4 py-3">Category</th>
                            <th scope="col" class="px-4 py-3">Sub-categories</th>
                            <th scope="col" class="px-4 py-3">Products</th>
                            <th scope="col" class="px-4 py-3">Website</th>
                            <th scope="col" class="px-4 py-3">Menu</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($categories as $category)
                            @include('admin.categories.row', ['category' => $category, 'isParent' => true])
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php($subCategoryCount = $categories->sum('children_count'))
            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">{{ $categories->count() }} {{ str('category')->plural($categories->count()) }} · {{ $subCategoryCount }} {{ str('sub-category')->plural($subCategoryCount) }}</p>
                <x-admin.button form="category-order">Save order</x-admin.button>
            </div>
        </div>
    @endif
</x-layouts.admin>
