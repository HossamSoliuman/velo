<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use ManagesUploads;

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::query()->pluck('value', 'key'),
            'priceRanges' => json_decode((string) SiteSetting::value('price_ranges', '[]'), true) ?: [],
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach (Arr::except($validated, ['price_ranges', 'show_prices', 'default_og_image', 'remove_default_og_image']) as $key => $value) {
            SiteSetting::put($key, $value);
        }

        SiteSetting::put('show_prices', $request->boolean('show_prices') ? '1' : '0');

        $previousShareImage = SiteSetting::value('default_og_image');
        $shareImage = $this->uploadedPath($request, 'default_og_image', $previousShareImage, 'seo', 'remove_default_og_image');
        SiteSetting::put('default_og_image', $shareImage);
        $this->deleteReplacedUpload($previousShareImage, $shareImage);

        SiteSetting::put('price_ranges', collect($validated['price_ranges'] ?? [])
            ->map(fn (array $range) => [
                'min' => (int) $range['min'],
                'max' => isset($range['max']) ? (int) $range['max'] : null,
            ])
            ->sortBy('min')
            ->values()
            ->toJson());

        return redirect()->route('admin.settings.edit')->with('status', 'Settings saved.');
    }
}
