<x-layouts.admin title="Categories">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-slate-600">Categories appear on the website and in the menu in the order below, lowest number first. Click a status to switch it on or off.</p>
        <x-admin.button :href="route('admin.categories.create')" class="shrink-0">Add category</x-admin.button>
    </div>

    @if ($categories->isEmpty())
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
                            <th scope="col" class="px-4 py-3">Products</th>
                            <th scope="col" class="px-4 py-3">Website</th>
                            <th scope="col" class="px-4 py-3">Menu</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($categories as $category)
                            @include('admin.categories.row', ['category' => $category, 'isChild' => false])
                            @foreach ($category->children as $child)
                                @include('admin.categories.row', ['category' => $child, 'isChild' => true, 'parent' => $category])
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end border-t border-slate-100 bg-slate-50 px-4 py-3">
                <x-admin.button form="category-order">Save order</x-admin.button>
            </div>
        </div>
    @endif
</x-layouts.admin>
