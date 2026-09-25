<x-layouts.admin title="Add category">
    @include('admin.categories.form', ['action' => route('admin.categories.store')])
</x-layouts.admin>
