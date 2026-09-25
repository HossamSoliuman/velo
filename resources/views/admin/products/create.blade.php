<x-layouts.admin title="Add product">
    @include('admin.products.form', ['action' => route('admin.products.store')])
</x-layouts.admin>
