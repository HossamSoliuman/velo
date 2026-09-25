@props(['model', 'productFields' => false, 'warnings' => []])

@php
    use App\Enums\RobotsDirective;

    $robotsOptions = collect(RobotsDirective::cases())->mapWithKeys(fn (RobotsDirective $directive) => [$directive->value => $directive->label()])->all();
@endphp

<div class="space-y-6">
    @if ($warnings)
        <div class="rounded-lg border-l-4 border-fan-yellow bg-amber-50 px-4 py-3 text-sm text-slate-700" role="status">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-sm text-slate-600">Leave a field blank to fall back to the name and description. Unique titles and descriptions help each page rank on its own.</p>

    <x-admin.input name="meta_title" label="Meta title" :value="$model->meta_title" :counter="60"
        hint="Shown as the clickable headline in search results." />

    <x-admin.textarea name="meta_description" label="Meta description" :value="$model->meta_description" :counter="160"
        hint="The summary shown under the headline in search results." />

    @if ($productFields)
        <div class="grid gap-6 sm:grid-cols-2">
            <x-admin.input name="meta_keywords" label="Keywords" :value="$model->meta_keywords" hint="Optional, separated by commas." />
            <x-admin.input name="canonical_url" type="url" label="Canonical URL" :value="$model->canonical_url" placeholder="https://"
                hint="Only needed when another URL is the main version of this page." />
        </div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <x-admin.input name="og_title" label="Social share title" :value="$model->og_title" :counter="60" />
        <x-admin.select name="robots" label="Search engine indexing" :options="$robotsOptions" :value="$model->robots ?? RobotsDirective::IndexFollow->value" required />
    </div>

    <x-admin.textarea name="og_description" label="Social share description" :value="$model->og_description" :counter="200" rows="2" />

    <x-admin.image-input name="og_image" label="Social share image" :path="$model->og_image" remove-name="remove_og_image"
        hint="Shown when the page is shared on WhatsApp, LinkedIn or Facebook. Recommended 1200 × 630 px, JPG, PNG or WebP, up to 4 MB." />
</div>
