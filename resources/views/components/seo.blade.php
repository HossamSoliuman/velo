@props([
    'title' => null,
    'metaTitle' => null,
    'description' => null,
    'seo' => null,
    'canonical' => null,
    'robots' => null,
    'image' => null,
    'type' => 'website',
])

{{--
    Every search and social tag for a public page. "title" is the page name, shown before the business name;
    "metaTitle" is a complete title used as it is. A record with SEO fields ("seo") overrides both, and its
    own description, canonical URL, robots and social fields win over the page's fallbacks.
--}}
@php
    use App\Models\SiteSetting;
    use Illuminate\Support\Facades\Storage;

    $siteName = SiteSetting::value('site_name', config('app.name'));
    $fullTitle = $seo?->meta_title ?: ($metaTitle ?: ($title ? $title.' | '.$siteName : $siteName));
    $description = trim((string) ($seo?->meta_description ?: $description)) ?: null;
    $canonical = $seo?->canonical_url ?: ($canonical ?? url()->current());
    $robots = $robots ?? $seo?->robots ?? 'index,follow';
    $defaultImage = SiteSetting::value('default_og_image');
    $image = $seo?->ogImageUrl() ?: ($image ? url($image) : null) ?: ($defaultImage ? url(Storage::disk('public')->url($defaultImage)) : null);
@endphp

<title>{{ $fullTitle }}</title>
@if ($description)
    <meta name="description" content="{{ $description }}">
@endif
@if (filled($seo?->meta_keywords))
    <meta name="keywords" content="{{ $seo->meta_keywords }}">
@endif
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="en_IN">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $seo?->og_title ?: ($title ?? $fullTitle) }}">
@if ($ogDescription = $seo?->og_description ?: $description)
    <meta property="og:description" content="{{ $ogDescription }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
