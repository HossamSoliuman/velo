@php
    $seoFields = ['meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'robots'];
    $seoHasErrors = $errors->hasAny($seoFields);
    $contentHasErrors = $errors->hasAny(['title', 'content']);
@endphp

<x-layouts.admin :title="'Edit '.$page->title">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.pages.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">← All pages</a>
        <a href="{{ $page->url }}" target="_blank" class="text-sm font-semibold text-brand-600 hover:text-brand-800">View on website ↗</a>
    </div>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data"
        x-data="{ tab: '{{ $seoHasErrors && ! $contentHasErrors ? 'seo' : 'content' }}' }">
        @csrf
        @method('PUT')

        @include('admin.partials.form-tabs', ['tabs' => [
            'content' => ['Content', $contentHasErrors],
            'seo' => ['SEO', $seoHasErrors],
        ]])

        <div x-show="tab === 'content'">
            <x-admin.card class="space-y-6">
                <x-admin.input name="title" label="Page title" :value="$page->title" required maxlength="255" />
                <x-admin.rich-text name="content" label="Content" :value="$page->content" />
            </x-admin.card>
        </div>

        <div x-show="tab === 'seo'" x-cloak>
            <x-admin.card>
                <x-admin.seo-fields :model="$page" :warnings="$seoWarnings" />
            </x-admin.card>
        </div>

        @include('admin.partials.form-actions', ['cancelUrl' => route('admin.pages.index'), 'submitLabel' => 'Save page'])
    </form>
</x-layouts.admin>
