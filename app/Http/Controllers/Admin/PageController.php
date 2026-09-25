<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    use ManagesUploads;

    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->orderBy('title')->get(),
        ]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'seoWarnings' => $page->seoWarnings(),
        ]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $previousOgImage = $page->og_image;

        $page->update([
            ...$request->safe()->except(['og_image', 'remove_og_image']),
            'og_image' => $this->uploadedPath($request, 'og_image', $previousOgImage, 'seo', 'remove_og_image'),
        ]);

        $this->deleteReplacedUpload($previousOgImage, $page->og_image);

        return redirect()->route('admin.pages.edit', $page)->with('status', "“{$page->title}” saved.");
    }
}
