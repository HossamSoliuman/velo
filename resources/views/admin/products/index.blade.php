<x-layouts.admin title="Products">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-slate-600">Search, filter and reorder products. Filter by a category to set the order products appear in on that category's page.</p>
        <x-admin.button :href="route('admin.products.create')" class="shrink-0">Add product</x-admin.button>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-[2fr_1.5fr_1fr_auto_auto] lg:items-end">
        <x-admin.input name="search" type="search" label="Search" :value="$search" placeholder="Product name or SKU" />

        <x-admin.field label="Category" for="filter-category">
            <select id="filter-category" name="category" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                <option value="">All categories</option>
                @foreach ($categoryOptions as $parent)
                    <option value="{{ $parent->id }}" @selected($category?->id === $parent->id)>{{ $parent->name }}</option>
                    @foreach ($parent->children as $child)
                        <option value="{{ $child->id }}" @selected($category?->id === $child->id)>&nbsp;&nbsp;↳ {{ $child->name }}</option>
                    @endforeach
                @endforeach
            </select>
        </x-admin.field>

        <x-admin.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Hidden']" :value="request('status')" placeholder="Any status" />

        <label class="flex items-center gap-2 pb-2 text-sm font-semibold text-slate-700">
            <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured')) class="size-4 accent-brand-500">
            Featured only
        </label>

        <div class="flex items-center gap-2">
            <x-admin.button>Filter</x-admin.button>
            @if (request()->hasAny(['search', 'category', 'status', 'featured']))
                <a href="{{ route('admin.products.index') }}" class="rounded-full px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Clear</a>
            @endif
        </div>
    </form>

    @if ($category)
        <p class="mb-4 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-800">
            Showing products in <strong>{{ $category->name }}</strong>. The order numbers set their position on this category's page.
        </p>
    @endif

    @if ($products->isEmpty())
        <x-admin.card>
            <p class="text-sm text-slate-500">
                No products match these filters.
                @if (! request()->hasAny(['search', 'category', 'status', 'featured']))
                    <a href="{{ route('admin.products.create') }}" class="font-semibold text-brand-600 hover:text-brand-800">Add the first product.</a>
                @endif
            </p>
        </x-admin.card>
    @else
        <form id="product-order" method="POST" action="{{ route('admin.product-order.update') }}">
            @csrf
            @method('PATCH')
            @if ($category)
                <input type="hidden" name="category" value="{{ $category->id }}">
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="w-24 px-4 py-3">Order</th>
                            <th scope="col" class="px-4 py-3">Product</th>
                            <th scope="col" class="px-4 py-3">Categories</th>
                            <th scope="col" class="px-4 py-3 text-right">Price</th>
                            <th scope="col" class="px-4 py-3 text-right">Min. qty</th>
                            <th scope="col" class="px-4 py-3">Website</th>
                            <th scope="col" class="px-4 py-3">Featured</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($products as $product)
                            <tr>
                                <td class="px-4 py-3">
                                    <label for="order-{{ $product->id }}" class="sr-only">Display order for {{ $product->name }}</label>
                                    <input id="order-{{ $product->id }}" form="product-order" type="number" min="0" max="100000" name="order[{{ $product->id }}]"
                                        value="{{ $category ? $product->pivot->display_order : $product->display_order }}"
                                        class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($product->primaryImage)
                                            <img src="{{ $product->primaryImage->url }}" alt="" loading="lazy" class="size-11 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                                        @else
                                            <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-300"><x-logo-mark class="size-5" /></span>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-ink hover:text-brand-600">{{ $product->name }}</a>
                                            <p class="text-xs text-slate-500">SKU {{ $product->sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="max-w-56 px-4 py-3 text-xs text-slate-600">{{ $product->categories->pluck('name')->join(', ') ?: '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap text-ink">{{ $product->formatted_price }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ number_format($product->minimum_qty) }}</td>
                                <td class="px-4 py-3">
                                    <x-admin.status-button :action="route('admin.products.status.update', $product)" field="is_active" :value="$product->is_active"
                                        :label="($product->is_active ? 'Hide ' : 'Show ').$product->name.' on the website'" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-admin.status-button :action="route('admin.products.status.update', $product)" field="is_featured" :value="$product->is_featured"
                                        on="Featured" off="No" :label="($product->is_featured ? 'Remove ' : 'Feature ').$product->name.($product->is_featured ? ' from the home page' : ' on the home page')" />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">{{ $products->total() }} {{ str('product')->plural($products->total()) }}</p>
                <x-admin.button form="product-order">Save order</x-admin.button>
            </div>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</x-layouts.admin>
