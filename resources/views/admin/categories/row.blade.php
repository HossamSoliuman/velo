{{-- A row in an orderable category table. Top-level rows ($isParent) open the category's page; sub-category rows open the edit form. --}}
@php($url = $isParent ? route('admin.categories.show', $category) : route('admin.categories.edit', $category))

<tr class="group hover:bg-slate-50/70">
    <td class="px-4 py-3">
        <label for="order-{{ $category->id }}" class="sr-only">Display order for {{ $category->name }}</label>
        <input id="order-{{ $category->id }}" form="category-order" type="number" min="0" max="100000" name="order[{{ $category->id }}]" value="{{ $category->display_order }}"
            class="w-16 [appearance:textfield] rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-center text-sm focus:border-brand-500 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
    </td>
    <td class="px-4 py-3">
        <div class="flex items-center gap-3">
            <x-admin.thumbnail :url="$category->image_url" class="size-10" />
            <div class="min-w-0">
                <a href="{{ $url }}" class="font-semibold text-ink hover:text-brand-600">{{ $category->name }}</a>
                <p class="text-xs text-slate-500">/category/{{ $category->slug }}</p>
            </div>
        </div>
    </td>
    @if ($isParent)
        <td class="px-4 py-3">
            @if ($category->children_count > 0)
                <a href="{{ $url }}" class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold whitespace-nowrap text-brand-700 ring-1 ring-brand-100 hover:bg-brand-100">
                    {{ $category->children_count }} {{ str('sub-category')->plural($category->children_count) }}
                </a>
                <p class="mt-1 max-w-64 truncate text-xs text-slate-500" title="{{ $category->children->pluck('name')->join(', ') }}">{{ $category->children->pluck('name')->join(', ') }}</p>
            @else
                <a href="{{ route('admin.categories.create', ['parent' => $category->id]) }}" class="text-xs font-semibold whitespace-nowrap text-slate-400 hover:text-brand-600">+ Add sub-category</a>
            @endif
        </td>
    @endif
    <td class="px-4 py-3">
        <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="font-semibold text-slate-600 hover:text-brand-600"
            title="Products in {{ $category->name }}">{{ $category->products_count }}</a>
    </td>
    <td class="px-4 py-3">
        <x-admin.status-button :action="route('admin.categories.status.update', $category)" field="is_active" :value="$category->is_active"
            :label="($category->is_active ? 'Hide ' : 'Show ').$category->name.' on the website'" />
    </td>
    <td class="px-4 py-3">
        <x-admin.status-button :action="route('admin.categories.status.update', $category)" field="show_in_menu" :value="$category->show_in_menu"
            on="In menu" off="Not in menu" :label="($category->show_in_menu ? 'Remove ' : 'Add ').$category->name.($category->show_in_menu ? ' from' : ' to').' the menu'" />
    </td>
    <td class="px-4 py-3">
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-brand-600 hover:text-brand-800">Edit</a>
            @if ($isParent)
                <a href="{{ $url }}" class="rounded-full p-1 text-slate-400 group-hover:text-brand-600 hover:bg-brand-50" aria-label="Open {{ $category->name }}" title="Open {{ $category->name }}">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </a>
            @endif
        </div>
    </td>
</tr>
