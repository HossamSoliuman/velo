@php
    $seoFields = ['meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'robots'];
    $seoHasErrors = $errors->hasAny($seoFields);
    $detailsHasErrors = $errors->any() && ! collect($errors->keys())->every(fn (string $key) => in_array($key, $seoFields, true));
    $hasChildren = ($category->children_count ?? 0) > 0;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data"
    x-data="{ tab: '{{ $seoHasErrors && ! $detailsHasErrors ? 'seo' : 'details' }}' }">
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif

    @include('admin.partials.form-tabs', ['tabs' => [
        'details' => ['Details', $detailsHasErrors],
        'seo' => ['SEO', $seoHasErrors],
    ]])

    <div x-show="tab === 'details'" class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="space-y-6 lg:col-span-2" x-data="slugField({{ Js::from(old('name', $category->name ?? '')) }}, {{ Js::from(old('slug', $category->slug ?? '')) }})">
            <x-admin.input name="name" label="Name" :value="$category->name" x-model="name" required maxlength="255" />

            <x-admin.input name="slug" label="URL slug" :value="$category->slug" x-model="slug" ::placeholder="suggestedSlug"
                hint="Used in the web address /category/…. Leave blank to create it from the name. If you change it, the old address redirects to the new one." />

            @if ($hasChildren)
                <x-admin.field label="Parent category" hint="This category has sub-categories, so it stays a top-level category.">
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">None (top-level category)</p>
                </x-admin.field>
            @else
                <x-admin.select name="parent_id" label="Parent category" :options="$parentOptions->pluck('name', 'id')" :value="$category->parent_id"
                    placeholder="None (top-level category)" hint="Sub-categories appear beneath their parent in the menu." />
            @endif

            <x-admin.rich-text name="description" label="Description" :value="$category->description" hint="Shown at the top of the category page." />
        </x-admin.card>

        <div class="space-y-6">
            <x-admin.card title="Visibility" class="space-y-5">
                <x-admin.toggle name="is_active" label="Active" description="Inactive categories are hidden from the website." :checked="$category->is_active" />
                <x-admin.toggle name="show_in_menu" label="Show in menu" description="List this category in the main navigation." :checked="$category->show_in_menu" />
                <x-admin.input name="display_order" type="number" label="Display order" :value="$category->display_order" min="0" max="100000"
                    hint="Lower numbers appear first in the menu." />
            </x-admin.card>

            <x-admin.card title="Image">
                <x-admin.image-input name="image" label="Category image" :path="$category->image" remove-name="remove_image"
                    hint="Used on category cards. Square images work best. JPG, PNG or WebP, up to 4 MB." />
            </x-admin.card>
        </div>
    </div>

    <div x-show="tab === 'seo'" x-cloak>
        <x-admin.card>
            <x-admin.seo-fields :model="$category" :warnings="$seoWarnings ?? []" />
        </x-admin.card>
    </div>

    @include('admin.partials.form-actions', ['cancelUrl' => route('admin.categories.index'), 'submitLabel' => $category->exists ? 'Save changes' : 'Create category'])
</form>
