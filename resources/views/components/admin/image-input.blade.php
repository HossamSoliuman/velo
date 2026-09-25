@props(['name', 'label', 'path' => null, 'removeName' => null, 'hint' => 'JPG, PNG or WebP, up to 4 MB.'])

@php
    use Illuminate\Support\Facades\Storage;

    $url = match (true) {
        blank($path) => null,
        str($path)->startsWith(['http://', 'https://']) => $path,
        default => Storage::disk('public')->url($path),
    };
@endphp

<x-admin.field :label="$label" :for="$name" :hint="$hint" :error="$name">
    <div class="flex items-center gap-4">
        @if ($url)
            <img src="{{ $url }}" alt="Current {{ strtolower($label) }}" class="size-20 shrink-0 rounded-lg bg-slate-100 object-cover ring-1 ring-slate-200">
        @endif
        <div class="min-w-0 flex-1 space-y-2">
            <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
            @if ($url && $removeName)
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="{{ $removeName }}" value="1" class="size-4 accent-red-600">
                    Remove current image
                </label>
            @endif
        </div>
    </div>
</x-admin.field>
