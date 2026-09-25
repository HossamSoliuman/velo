<tr @class(['bg-slate-50/60' => $isChild])>
    <td class="px-4 py-3">
        <label for="order-{{ $category->id }}" class="sr-only">Display order for {{ $category->name }}</label>
        <input id="order-{{ $category->id }}" form="category-order" type="number" min="0" max="100000" name="order[{{ $category->id }}]" value="{{ $category->display_order }}"
            class="w-16 [appearance:textfield] rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-center text-sm focus:border-brand-500 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
    </td>
    <td class="px-4 py-3">
        <a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-ink hover:text-brand-600">{{ $category->name }}</a>
        <p class="text-xs text-slate-500">
            @if ($isChild)
                <span class="font-semibold text-slate-600">In {{ $parent->name }}</span> ·
            @endif
            /category/{{ $category->slug }}
        </p>
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
