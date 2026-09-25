<x-layouts.admin title="Dashboard">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Products', $stats['products'], $stats['activeProducts'].' shown on the website', 'border-fan-magenta', route('admin.products.index')],
            ['Active products', $stats['activeProducts'], ($stats['products'] - $stats['activeProducts']).' hidden', 'border-fan-cyan', route('admin.products.index', ['status' => 'active'])],
            ['Featured products', $stats['featuredProducts'], 'Shown on the home page', 'border-fan-lime', route('admin.products.index', ['featured' => 1])],
            ['Categories', $stats['categories'], $stats['activeCategories'].' active', 'border-fan-purple', route('admin.categories.index')],
        ] as [$label, $count, $detail, $accent, $url])
            <a href="{{ $url }}" class="rounded-2xl border-t-4 {{ $accent }} bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-extrabold text-brand-800">{{ number_format($count) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $detail }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <x-admin.card title="Quick actions" class="lg:col-span-1">
            <div class="grid gap-3">
                <x-admin.button :href="route('admin.products.create')">Add a product</x-admin.button>
                <x-admin.button :href="route('admin.categories.create')" variant="secondary">Add a category</x-admin.button>
                <x-admin.button :href="route('admin.e-catalog.edit')" variant="secondary">
                    {{ $hasECatalog ? 'Replace the e-catalog' : 'Upload the e-catalog' }}
                </x-admin.button>
                <x-admin.button :href="route('admin.settings.edit')" variant="secondary">Edit contact details</x-admin.button>
            </div>
        </x-admin.card>

        <x-admin.card title="Recently updated products" class="lg:col-span-2">
            @if ($recentProducts->isEmpty())
                <p class="text-sm text-slate-500">No products yet. <a href="{{ route('admin.products.create') }}" class="font-semibold text-brand-600 hover:text-brand-800">Add your first product.</a></p>
            @else
                <ul class="-my-3 divide-y divide-slate-100">
                    @foreach ($recentProducts as $product)
                        <li class="flex items-center gap-4 py-3">
                            @if ($product->primaryImage)
                                <img src="{{ $product->primaryImage->url }}" alt="" class="size-11 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                            @else
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-300"><x-logo-mark class="size-5" /></span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="block truncate text-sm font-semibold text-ink hover:text-brand-600">{{ $product->name }}</a>
                                <p class="text-xs text-slate-500">SKU {{ $product->sku }} · updated {{ $product->updated_at->diffForHumans() }}</p>
                            </div>
                            @unless ($product->is_active)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Hidden</span>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>
</x-layouts.admin>
