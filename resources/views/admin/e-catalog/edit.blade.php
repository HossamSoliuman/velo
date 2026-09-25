<x-layouts.admin title="E-Catalog">
    <p class="mb-6 max-w-2xl text-sm text-slate-600">Visitors can view and download this PDF from the E-Catalog page. Uploading a new file replaces the current one.</p>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card title="Current catalogue">
            @if ($path)
                <div class="flex items-start gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-fan-magenta/10 text-xs font-extrabold text-fan-magenta">PDF</span>
                    <div class="min-w-0 space-y-1 text-sm">
                        <a href="{{ $url }}" target="_blank" class="block truncate font-semibold text-brand-700 hover:text-brand-900">Open the current e-catalog ↗</a>
                        <p class="text-slate-500">
                            @if ($size !== null)
                                {{ Illuminate\Support\Number::fileSize($size, precision: 1) }}
                            @else
                                File missing from storage
                            @endif
                            @if ($updatedAt)
                                · uploaded {{ $updatedAt->format('j M Y, g:i a') }}
                            @endif
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.e-catalog.destroy') }}" class="mt-6"
                    onsubmit="return confirm('Remove the e-catalog from the website?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Remove e-catalog</button>
                </form>
            @else
                <p class="text-sm text-slate-500">No catalogue has been uploaded yet. The E-Catalog page shows a “coming soon” message until you upload one.</p>
            @endif
        </x-admin.card>

        <x-admin.card :title="$path ? 'Replace catalogue' : 'Upload catalogue'">
            <form method="POST" action="{{ route('admin.e-catalog.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <x-admin.field label="Catalogue PDF" for="catalog" error="catalog" hint="PDF only, up to 20 MB.">
                    <input type="file" id="catalog" name="catalog" accept="application/pdf" required
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                </x-admin.field>
                <x-admin.button>{{ $path ? 'Replace catalogue' : 'Upload catalogue' }}</x-admin.button>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
