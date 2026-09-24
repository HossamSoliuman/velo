<x-layouts.app :title="$title">
    <section class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6">
        <x-logo-mark class="mx-auto size-16 text-brand-500" />
        <h1 class="mt-6 text-3xl font-extrabold text-brand-700 sm:text-4xl">{{ $title }}</h1>
        <p class="mt-4 text-slate-600">This page is being prepared and will be available soon.</p>
        <a href="{{ route('home') }}" class="mt-8 inline-block rounded-full bg-brand-500 px-6 py-3 text-sm font-bold text-white hover:bg-brand-600">Back to home</a>
    </section>
</x-layouts.app>
