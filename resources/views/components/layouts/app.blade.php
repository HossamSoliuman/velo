@props(['title' => null, 'description' => null])

@php($siteName = \App\Models\SiteSetting::value('site_name', config('app.name')))

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' | '.$siteName : $siteName }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        <meta name="theme-color" content="#3b75ba">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{ $head ?? '' }}
    </head>
    <body class="flex min-h-screen flex-col">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:text-brand-700">
            Skip to content
        </a>

        @include('partials.site-header')

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.site-footer')
    </body>
</html>
