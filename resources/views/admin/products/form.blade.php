@php
    use App\Models\SiteSetting;

    $seoFields = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description', 'og_image', 'robots'];
    $errorKeys = collect($errors->keys());
    $seoHasErrors = $errorKeys->contains(fn (string $key) => in_array($key, $seoFields, true));
    $imagesHaveErrors = $errorKeys->contains(fn (string $key) => str($key)->startsWith(['images', 'new_images', 'primary_image']));
    $detailsHaveErrors = $errorKeys->contains(fn (string $key) => ! in_array($key, $seoFields, true) && ! str($key)->startsWith(['images', 'new_images', 'primary_image']));
    $initialTab = match (true) {
        $detailsHaveErrors, ! $errors->any() => 'details',
        $imagesHaveErrors => 'images',
        default => 'seo',
    };
    $selectedCategories = collect(old('categories', $product->categories->pluck('id')->all()))->map(fn ($id) => (int) $id)->all();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ tab: '{{ $initialTab }}' }">
    @csrf
    @if ($product->exists)
        @method('PUT')
    @endif

    @include('admin.partials.form-tabs', ['tabs' => [
        'details' => ['Details', $detailsHaveErrors],
        'images' => ['Images', $imagesHaveErrors],
        'seo' => ['SEO', $seoHasErrors],
    ]])

    {{-- Details --}}
    <div x-show="tab === 'details'" class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="space-y-6 lg:col-span-2" x-data="slugField({{ Js::from(old('name', $product->name ?? '')) }}, {{ Js::from(old('slug', $product->slug ?? '')) }})">
            <x-admin.input name="name" label="Product name" :value="$product->name" x-model="name" required maxlength="255" />

            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="sku" label="SKU code" :value="$product->sku" required maxlength="64" placeholder="VPG-GS-001"
                    hint="Unique. Letters, numbers, dashes and underscores." />
                <x-admin.input name="slug" label="URL slug" :value="$product->slug" x-model="slug" ::placeholder="suggestedSlug"
                    hint="Leave blank to create it from the name. Old addresses redirect after a change." />
            </div>

            <x-admin.rich-text name="description" label="Description" :value="$product->description" required />
        </x-admin.card>

        <div class="space-y-6">
            <x-admin.card title="Pricing" class="space-y-5">
                <x-admin.field label="Price" for="price" error="price" :required="true" hint="Hidden on the website when prices are turned off in Settings.">
                    <div class="flex rounded-lg shadow-xs">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-500">
                            {{ SiteSetting::value('currency_symbol', '₹') }}
                        </span>
                        <input id="price" name="price" type="number" step="0.01" min="0" max="99999999.99" required value="{{ old('price', $product->price) }}"
                            @class([
                                'block w-full min-w-0 rounded-r-lg border bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:outline-none',
                                'border-red-400' => $errors->has('price'),
                                'border-slate-300 focus:border-brand-500' => ! $errors->has('price'),
                            ])>
                    </div>
                </x-admin.field>

                <x-admin.input name="minimum_qty" type="number" label="Minimum quantity" :value="$product->minimum_qty" min="1" max="1000000" required
                    hint="The smallest quantity a customer can enquire for." />
            </x-admin.card>

            <x-admin.card title="Visibility" class="space-y-5">
                <x-admin.toggle name="is_active" label="Active" description="Inactive products are hidden from the website." :checked="$product->is_active" />
                <x-admin.toggle name="is_featured" label="Featured" description="Show this product on the home page." :checked="$product->is_featured" />
                <x-admin.input name="display_order" type="number" label="Display order" :value="$product->display_order" min="0" max="100000"
                    hint="Lower numbers appear first. Set the order within a category from the product list." />
            </x-admin.card>

            <x-admin.card title="Categories">
                <fieldset>
                    <legend class="sr-only">Categories</legend>
                    @if ($categoryOptions->isEmpty())
                        <p class="text-sm text-slate-500">Create a category first. <a href="{{ route('admin.categories.create') }}" class="font-semibold text-brand-600">Add category</a></p>
                    @else
                        <div class="max-h-80 space-y-1.5 overflow-y-auto pr-1">
                            @foreach ($categoryOptions as $parent)
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="categories[]" value="{{ $parent->id }}" @checked(in_array($parent->id, $selectedCategories, true)) class="size-4 accent-brand-500">
                                    {{ $parent->name }}
                                    @unless ($parent->is_active)
                                        <span class="text-xs font-normal text-slate-400">(inactive)</span>
                                    @endunless
                                </label>
                                @foreach ($parent->children as $child)
                                    <label class="ml-6 flex items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox" name="categories[]" value="{{ $child->id }}" @checked(in_array($child->id, $selectedCategories, true)) class="size-4 accent-brand-500">
                                        {{ $child->name }}
                                        @unless ($child->is_active)
                                            <span class="text-xs text-slate-400">(inactive)</span>
                                        @endunless
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </fieldset>
                @foreach (['categories', 'categories.*'] as $categoryErrorKey)
                    @error($categoryErrorKey)
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                @endforeach
            </x-admin.card>
        </div>
    </div>

    {{-- Images --}}
    <div x-show="tab === 'images'" x-cloak class="space-y-6">
        @if ($product->exists && $product->images->isNotEmpty())
            <x-admin.card title="Current images" description="The main image is used on product cards and at the top of the product page. Lower order numbers appear first in the gallery.">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($product->images as $image)
                        <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                            <img src="{{ $image->url }}" alt="{{ $image->alt }}" loading="lazy" class="aspect-[4/3] w-full bg-slate-100 object-cover">
                            <div class="space-y-3 p-4">
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="radio" name="primary_image" value="{{ $image->id }}" @checked((int) old('primary_image', $product->images->first()->id) === $image->id) class="size-4 accent-brand-500">
                                    Main image
                                </label>
                                <x-admin.input name="images[{{ $image->id }}][alt]" label="Alt text" :value="$image->alt" placeholder="{{ $product->name }}"
                                    hint="Describes the image for screen readers and search engines." />
                                <div class="flex items-end justify-between gap-4">
                                    <x-admin.input name="images[{{ $image->id }}][sort_order]" type="number" label="Order" :value="$image->sort_order" min="0" max="1000" class="w-24" />
                                    <input type="hidden" name="images[{{ $image->id }}][remove]" value="0">
                                    <label class="flex items-center gap-2 pb-2 text-sm font-semibold text-red-600">
                                        <input type="checkbox" name="images[{{ $image->id }}][remove]" value="1" class="size-4 accent-red-600">
                                        Remove
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @endif

        <x-admin.card title="Upload images" description="Add a main image plus gallery images. JPG, PNG or WebP, up to 4 MB each and 10 per upload.">
            <input type="file" id="new_images" name="new_images[]" multiple accept="image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
            @foreach (collect($errors->get('new_images'))->merge(collect($errors->get('new_images.*'))->flatten())->unique() as $message)
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @endforeach
            @if ($product->exists && $product->images->isEmpty())
                <p class="mt-3 text-xs text-slate-500">The first image you upload becomes the main image.</p>
            @endif
        </x-admin.card>
    </div>

    {{-- SEO --}}
    <div x-show="tab === 'seo'" x-cloak>
        <x-admin.card>
            <x-admin.seo-fields :model="$product" :product-fields="true" :warnings="$seoWarnings ?? []" />
        </x-admin.card>
    </div>

    @include('admin.partials.form-actions', ['cancelUrl' => route('admin.products.index'), 'submitLabel' => $product->exists ? 'Save changes' : 'Create product'])
</form>
