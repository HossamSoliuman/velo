{{-- Categories and sub-categories matching the index search, with where each one sits. --}}
<p class="mb-4 text-sm text-slate-600">
    {{ $categories->count() }} {{ str('result')->plural($categories->count()) }} for <strong class="text-ink">“{{ $search }}”</strong>
</p>

@if ($categories->isEmpty())
    <x-admin.card>
        <p class="text-sm text-slate-500">No category or sub-category matches your search.</p>
    </x-admin.card>
@else
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold tracking-wide text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-4 py-3">Category</th>
                        <th scope="col" class="px-4 py-3">Found in</th>
                        <th scope="col" class="px-4 py-3">Products</th>
                        <th scope="col" class="px-4 py-3">Website</th>
                        <th scope="col" class="px-4 py-3">Menu</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($categories as $category)
                        @php($url = $category->parent ? route('admin.categories.edit', $category) : route('admin.categories.show', $category))
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-admin.thumbnail :url="$category->image_url" class="size-10" />
                                    <div class="min-w-0">
                                        <a href="{{ $url }}" class="font-semibold text-ink hover:text-brand-600">{{ $category->name }}</a>
                                        <p class="text-xs text-slate-500">/category/{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                @if ($category->parent)
                                    <a href="{{ route('admin.categories.show', $category->parent) }}" class="font-semibold text-brand-600 hover:text-brand-800">{{ $category->parent->name }}</a>
                                    <span class="text-slate-500">› sub-category</span>
                                @else
                                    <span class="font-semibold text-slate-600">Top level</span>
                                    @if ($category->children_count > 0)
                                        <span class="text-slate-500">· {{ $category->children_count }} {{ str('sub-category')->plural($category->children_count) }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="font-semibold text-slate-600 hover:text-brand-600">{{ $category->products_count }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.status-button :action="route('admin.categories.status.update', $category)" field="is_active" :value="$category->is_active"
                                    :label="($category->is_active ? 'Hide ' : 'Show ').$category->name.' on the website'" />
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.status-button :action="route('admin.categories.status.update', $category)" field="show_in_menu" :value="$category->show_in_menu"
                                    on="In menu" off="Not in menu" :label="($category->show_in_menu ? 'Remove ' : 'Add ').$category->name.($category->show_in_menu ? ' from' : ' to').' the menu'" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
