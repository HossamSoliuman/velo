@php
    use App\Casts\SanitizedHtml;
    use Illuminate\Support\Str;

    $isPolicy = in_array($page->slug, ['privacy-policy', 'terms-and-conditions'], true);
@endphp

<x-layouts.app :title="$page->title" :description="$page->meta_description ?: Str::limit(SanitizedHtml::plainText($page->content), 160)">
    <x-page-header :title="$page->title" :breadcrumbs="[[$page->title, null]]">
        @if ($isPolicy)
            <p class="text-sm">Last updated {{ $page->updated_at->format('j F Y') }}</p>
        @endif
    </x-page-header>

    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
        <div class="rich-text sm:text-lg">
            {!! $page->content !!}
        </div>

        @if ($isPolicy)
            <p class="mt-12 border-t border-brand-100 pt-6 text-sm text-slate-600">
                Questions about this page? <a href="{{ route('contact') }}" class="font-semibold text-brand-600 hover:text-brand-800">Contact us</a>.
            </p>
        @endif
    </article>

    @unless ($isPolicy)
        <x-why-velo />
        <x-enquiry-cta class="pt-16" />
    @endunless
</x-layouts.app>
