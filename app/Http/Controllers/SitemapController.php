<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    /**
     * The XML sitemap, built on every request so it always matches the live catalogue. It lists the fixed
     * public pages and every visible, indexable content page, category and product.
     */
    public function __invoke(): Response
    {
        return response()
            ->view('sitemap', ['urls' => $this->urls()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string|null}>
     */
    private function urls(): Collection
    {
        $fixedPages = collect(['home', 'categories.index', 'price-range', 'e-catalog', 'contact'])
            ->map(fn (string $route) => ['loc' => route($route), 'lastmod' => null]);

        $pages = Page::query()->indexable()->orderBy('id')->get(['slug', 'updated_at'])
            ->map(fn (Page $page) => ['loc' => $page->url, 'lastmod' => $page->updated_at?->toAtomString()]);

        $categories = Category::query()->visible()->indexable()->ordered()->get(['id', 'parent_id', 'slug', 'updated_at'])
            ->map(fn (Category $category) => [
                'loc' => route('categories.show', $category),
                'lastmod' => $category->updated_at?->toAtomString(),
            ]);

        // A product whose canonical URL points elsewhere is a duplicate, so only the main version is listed.
        $products = Product::query()->active()->indexable()->ordered()->get(['id', 'slug', 'canonical_url', 'updated_at'])
            ->map(fn (Product $product) => [
                'loc' => route('products.show', $product),
                'canonical' => $product->canonical_url,
                'lastmod' => $product->updated_at?->toAtomString(),
            ])
            ->filter(fn (array $url) => blank($url['canonical']) || $url['canonical'] === $url['loc'])
            ->map(fn (array $url) => ['loc' => $url['loc'], 'lastmod' => $url['lastmod']]);

        return $fixedPages->concat($pages)->concat($categories)->concat($products)->values();
    }
}
