<x-layouts.admin :title="$parent ? 'Add sub-category' : 'Add category'">
    <x-admin.breadcrumbs class="mb-6" :items="array_filter([
        ['Categories', route('admin.categories.index')],
        $parent ? [$parent->name, route('admin.categories.show', $parent)] : null,
        [$parent ? 'Add sub-category' : 'Add category', null],
    ])" />

    @include('admin.categories.form', [
        'action' => route('admin.categories.store'),
        'cancelUrl' => $parent ? route('admin.categories.show', $parent) : route('admin.categories.index'),
    ])
</x-layouts.admin>
