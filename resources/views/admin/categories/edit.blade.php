<x-layouts.admin :title="$category->parent ? 'Edit sub-category' : 'Edit category'">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <x-admin.breadcrumbs :items="$category->parent
            ? [
                ['Categories', route('admin.categories.index')],
                [$category->parent->name, route('admin.categories.show', $category->parent)],
                [$category->name, null],
            ]
            : [
                ['Categories', route('admin.categories.index')],
                [$category->name, route('admin.categories.show', $category)],
                ['Edit', null],
            ]" />
        @if ($category->is_active)
            <a href="{{ route('categories.show', $category->slug) }}" target="_blank" class="text-sm font-semibold text-brand-600 hover:text-brand-800">View on website ↗</a>
        @endif
    </div>

    @include('admin.categories.form', [
        'action' => route('admin.categories.update', $category),
        'cancelUrl' => route('admin.categories.show', $category->parent ?? $category),
    ])

    @php
        $parentNames = $reassignOptions->pluck('name', 'id');
        $reassignLabels = $reassignOptions->mapWithKeys(fn ($option) => [
            $option->id => $option->parent_id ? $parentNames->get($option->parent_id).' › '.$option->name : $option->name,
        ]);
    @endphp

    <x-admin.card title="Delete category" class="mt-10 ring-red-200">
        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="space-y-4"
            onsubmit="return confirm({{ Js::from('Delete the category “'.$category->name.'”? This cannot be undone.') }})">
            @csrf
            @method('DELETE')

            <div class="space-y-2 text-sm text-slate-600">
                @if ($category->products_count > 0)
                    <p>This category contains <strong>{{ $category->products_count }} {{ str('product')->plural($category->products_count) }}</strong>. Choose a category to move them to first.</p>
                @else
                    <p>This category has no products.</p>
                @endif
                @if ($category->children_count > 0)
                    <p>Its {{ $category->children_count }} {{ str('sub-category')->plural($category->children_count) }} will move up a level.</p>
                @endif
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                @if ($category->products_count > 0)
                    <x-admin.select name="reassign_to" label="Move products to" :options="$reassignLabels" placeholder="Choose a category…" class="sm:w-80" required />
                @endif
                <x-admin.button variant="danger">Delete category</x-admin.button>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
