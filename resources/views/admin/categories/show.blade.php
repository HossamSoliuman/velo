<x-layouts.admin :title="$category->name">
    <x-admin.breadcrumbs class="mb-6" :items="[
        ['Categories', route('admin.categories.index')],
        [$category->name, null],
    ]" />

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-6">
            <x-admin.thumbnail :url="$category->image_url" class="size-20 rounded-xl" />

            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold tracking-wide text-slate-400 uppercase">Top-level category</p>
                <h2 class="mt-0.5 text-xl font-extrabold text-brand-800">{{ $category->name }}</h2>
                <p class="text-sm text-slate-500">/category/{{ $category->slug }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-slate-500">
                    <span class="flex items-center gap-2">
                        Website
                        <x-admin.status-button :action="route('admin.categories.status.update', $category)" field="is_active" :value="$category->is_active"
                            :label="($category->is_active ? 'Hide ' : 'Show ').$category->name.' on the website'" />
                    </span>
                    <span class="flex items-center gap-2">
                        Menu
                        <x-admin.status-button :action="route('admin.categories.status.update', $category)" field="show_in_menu" :value="$category->show_in_menu"
                            on="In menu" off="Not in menu" :label="($category->show_in_menu ? 'Remove ' : 'Add ').$category->name.($category->show_in_menu ? ' from' : ' to').' the menu'" />
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3 sm:flex-col sm:items-end">
                <x-admin.button :href="route('admin.categories.edit', $category)" variant="secondary">Edit category</x-admin.button>
                @if ($category->is_active)
                    <a href="{{ route('categories.show', $category->slug) }}" target="_blank" class="text-sm font-semibold text-brand-600 hover:text-brand-800">View on website ↗</a>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100 bg-slate-50/70 text-center sm:text-left">
            <div class="px-4 py-3 sm:px-6">
                <dt class="text-xs text-slate-500">Sub-categories</dt>
                <dd class="text-lg font-bold text-brand-800">{{ $category->children_count }}</dd>
            </div>
            <div class="px-4 py-3 sm:px-6">
                <dt class="text-xs text-slate-500">Products directly in it</dt>
                <dd class="text-lg font-bold">
                    <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="text-brand-800 hover:text-brand-600">{{ $category->products_count }}</a>
                </dd>
            </div>
            <div class="px-4 py-3 sm:px-6">
                <dt class="text-xs text-slate-500">Menu position</dt>
                <dd class="text-lg font-bold text-brand-800">{{ $category->display_order }}</dd>
            </div>
        </dl>
    </section>

    <div class="mt-10 mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-base font-bold text-brand-800">Sub-categories</h2>
            <p class="mt-1 text-sm text-slate-500">Listed beneath {{ $category->name }} in the menu, lowest number first.</p>
        </div>
        <x-admin.button :href="route('admin.categories.create', ['parent' => $category->id])" class="shrink-0">Add sub-category</x-admin.button>
    </div>

    @if ($category->children->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-10 text-center">
            <x-logo-mark class="mx-auto size-10 text-brand-200" />
            <p class="mt-3 text-sm font-semibold text-slate-700">{{ $category->name }} has no sub-categories yet.</p>
            <p class="mt-1 text-sm text-slate-500">Sub-categories help visitors narrow down a large category.</p>
            <a href="{{ route('admin.categories.create', ['parent' => $category->id]) }}" class="mt-4 inline-block text-sm font-semibold text-brand-600 hover:text-brand-800">Add the first one →</a>
        </div>
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
                            <th scope="col" class="px-4 py-3">Sub-category</th>
                            <th scope="col" class="px-4 py-3">Products</th>
                            <th scope="col" class="px-4 py-3">Website</th>
                            <th scope="col" class="px-4 py-3">Menu</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($category->children as $child)
                            @include('admin.categories.row', ['category' => $child, 'isParent' => false])
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">{{ $category->children_count }} {{ str('sub-category')->plural($category->children_count) }} in {{ $category->name }}</p>
                <x-admin.button form="category-order">Save order</x-admin.button>
            </div>
        </div>
    @endif
</x-layouts.admin>
