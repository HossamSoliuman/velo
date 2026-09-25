@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ $title }} | Velo Admin</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-brand-900 px-4 py-12">
        <a href="{{ route('home') }}" class="text-white" aria-label="Back to the website">
            <x-logo class="text-5xl" />
        </a>

        <main class="mt-8 w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="h-1.5 bg-fan-gradient"></div>
            <div class="px-6 py-8 sm:px-10">
                <h1 class="text-2xl font-extrabold text-brand-800">{{ $title }}</h1>

                @if (session('status'))
                    <div class="mt-5 rounded-lg border-l-4 border-fan-lime bg-brand-50 px-4 py-3 text-sm text-slate-700" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-6">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </body>
</html>
