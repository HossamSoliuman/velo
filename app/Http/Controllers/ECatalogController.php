<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ECatalogController extends Controller
{
    /**
     * Show the current catalogue PDF, or a note that it is on its way.
     */
    public function show(): View
    {
        $path = $this->catalogPath();
        $updatedAt = SiteSetting::value('e_catalog_updated_at');

        return view('e-catalog', [
            'url' => $path !== null ? Storage::disk('public')->url($path) : null,
            'size' => $path !== null ? Storage::disk('public')->size($path) : null,
            'updatedAt' => $path !== null && filled($updatedAt) ? Carbon::parse($updatedAt) : null,
        ]);
    }

    /**
     * Download the catalogue PDF under a readable file name.
     */
    public function download(): StreamedResponse
    {
        $path = $this->catalogPath();

        abort_if($path === null, 404);

        return Storage::disk('public')->download($path, Str::slug(SiteSetting::value('site_name', config('app.name'))).'-e-catalog.pdf');
    }

    /**
     * Path of the uploaded catalogue on the public disk, or null when there is none.
     */
    private function catalogPath(): ?string
    {
        $path = SiteSetting::value('e_catalog_path');

        return filled($path) && Storage::disk('public')->exists($path) ? $path : null;
    }
}
