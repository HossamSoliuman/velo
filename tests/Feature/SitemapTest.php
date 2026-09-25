<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

/**
 * The addresses listed in the sitemap.
 *
 * @return list<string>
 */
function sitemapUrls(): array
{
    $xml = simplexml_load_string(test()->get(route('sitemap'))->assertOk()->getContent());

    return array_map(fn (SimpleXMLElement $url) => (string) $url->loc, iterator_to_array($xml->url, false));
}

test('the sitemap is valid XML', function () {
    $response = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse()
        ->and($xml->getName())->toBe('urlset');
});

test('the sitemap lists the public pages, visible categories and active products', function () {
    $page = Page::factory()->create(['slug' => 'about-us']);
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);
    $product = Product::factory()->create();

    expect(sitemapUrls())->toContain(
        route('home'),
        route('categories.index'),
        route('price-range'),
        route('e-catalog'),
        route('contact'),
        $page->url,
        route('categories.show', $parent),
        route('categories.show', $child),
        route('products.show', $product),
    );
});

test('the sitemap gives each record its last modified date', function () {
    $product = Product::factory()->create();
    $product->forceFill(['updated_at' => '2026-09-01 10:00:00'])->saveQuietly();

    $this->get(route('sitemap'))
        ->assertSee('<loc>'.route('products.show', $product).'</loc>', false)
        ->assertSee('<lastmod>2026-09-01T10:00:00+00:00</lastmod>', false);
});

test('the sitemap leaves out hidden, no-index and duplicate records', function () {
    $inactiveProduct = Product::factory()->inactive()->create();
    $noIndexProduct = Product::factory()->create(['robots' => 'noindex,follow']);
    $duplicateProduct = Product::factory()->create(['canonical_url' => 'https://velo.example/product/original']);
    $inactiveCategory = Category::factory()->inactive()->create();
    $childOfInactive = Category::factory()->create(['parent_id' => $inactiveCategory->id]);
    $noIndexCategory = Category::factory()->create(['robots' => 'noindex,nofollow']);
    $noIndexPage = Page::factory()->create(['slug' => 'terms-and-conditions', 'robots' => 'noindex,follow']);

    $urls = sitemapUrls();

    expect($urls)->not->toContain(
        route('products.show', $inactiveProduct),
        route('products.show', $noIndexProduct),
        route('products.show', $duplicateProduct),
        route('categories.show', $inactiveCategory),
        route('categories.show', $childOfInactive),
        route('categories.show', $noIndexCategory),
        $noIndexPage->url,
        route('search'),
    );

    expect(collect($urls)->filter(fn (string $url) => str_contains($url, '/admin')))->toBeEmpty();
});

test('a product that names itself as canonical stays in the sitemap', function () {
    $product = Product::factory()->create(['slug' => 'gift-set']);
    $product->update(['canonical_url' => route('products.show', $product)]);

    expect(sitemapUrls())->toContain(route('products.show', $product));
});

test('robots.txt points crawlers to the sitemap and away from the admin panel', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.route('sitemap'));
});
