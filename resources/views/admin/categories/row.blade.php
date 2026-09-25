<tr @class(['bg-slate-50/60' => $isChild])>
    <td class="px-4 py-3">
        <label for="order-{{ $category->id }}" class="sr-only">Display order for {{ $category->name }}</label>
        <input id="order-{{ $category->id }}" form="category-order" type="number" min="0" max="100000" name="order[{{ $category->id }}]" value="{{ $category->display_order }}"
            @class(['w-20 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none', 'ml-4' => $isChild])>
    </td>
    <td class="px-4 py-3">
        <div @class(['flex items-center gap-3', 'pl-4' => $isChild])>
            @if ($isChild)
                <span class="text-slate-300" aria-hidden="true">↳</span>
            @endif
            <div class="min-w-0">
                <a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-ink hover:text-brand-600">{{ $category->name }}</a>
                <p class="text-xs text-slate-500">/category/{{ $category->slug }}</p>
            </div>
        </div>
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
