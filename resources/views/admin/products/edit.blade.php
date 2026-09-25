<x-layouts.admin title="Edit product">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">← All products</a>
        @if ($product->is_active)
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="text-sm font-semibold text-brand-600 hover:text-brand-800">View on website ↗</a>
        @endif
    </div>

    @include('admin.products.form', ['action' => route('admin.products.update', $product)])

    <x-admin.card title="Delete product" class="mt-10 ring-red-200">
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            onsubmit="return confirm({{ Js::from('Delete the product “'.$product->name.'” and its images? This cannot be undone.') }})">
            @csrf
            @method('DELETE')
            <p class="text-sm text-slate-600">Deletes the product and its images permanently. To hide it for now, untick <strong>Active</strong> instead.</p>
            <x-admin.button variant="danger" class="shrink-0">Delete product</x-admin.button>
        </form>
    </x-admin.card>
</x-layouts.admin>
