<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ECatalogController extends Controller
{
    use ManagesUploads;

    public function edit(): View
    {
        $path = SiteSetting::value('e_catalog_path');
        $updatedAt = SiteSetting::value('e_catalog_updated_at');

        return view('admin.e-catalog.edit', [
            'path' => filled($path) ? $path : null,
            'url' => filled($path) ? Storage::disk('public')->url($path) : null,
            'size' => filled($path) && Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : null,
            'updatedAt' => filled($updatedAt) ? Carbon::parse($updatedAt) : null,
        ]);
    }

    /**
     * Upload a new catalogue PDF, replacing the current one.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'catalog' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'catalog.max' => 'The catalogue must not be larger than 20 MB.',
        ]);

        $previousPath = SiteSetting::value('e_catalog_path');

        SiteSetting::put('e_catalog_path', $request->file('catalog')->store('catalog', 'public'));
        SiteSetting::put('e_catalog_updated_at', now()->toIso8601String());

        $this->deleteUpload($previousPath);

        return redirect()->route('admin.e-catalog.edit')->with('status', 'E-catalog uploaded.');
    }

    public function destroy(): RedirectResponse
    {
        $this->deleteUpload(SiteSetting::value('e_catalog_path'));

        SiteSetting::put('e_catalog_path', null);
        SiteSetting::put('e_catalog_updated_at', null);

        return redirect()->route('admin.e-catalog.edit')->with('status', 'E-catalog removed.');
    }
}
