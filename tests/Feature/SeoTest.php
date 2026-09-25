<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

/**
 * The JSON-LD blocks on a page, keyed by their schema.org type.
 *
 * @return array<string, array<string, mixed>>
 */
function structuredData(TestResponse $response): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);

    return collect($matches[1])
        ->map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
        ->keyBy('@type')
        ->all();
}

beforeEach(function () {
    SiteSetting::put('site_name', 'Velo Printing & Gifting');
});

test('a product page falls back to its name, description and first image', function () {
    $product = Product::factory()->create([
        'name' => 'Executive Gift Set',
        'description' => '<p>Premium corporate gifting set.</p>',
    ]);
    $product->images()->create(['path' => 'https://cdn.example.com/front.jpg', 'alt' => 'Front', 'sort_order' => 0]);

    $this->get(route('products.show', $product))
        ->assertSee('<title>Executive Gift Set | Velo Printing &amp; Gifting</title>', false)
        ->assertSee('<meta name="description" content="Premium corporate gifting set.">', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('products.show', $product).'">', false)
        ->assertSee('<meta property="og:type" content="product">', false)
        ->assertSee('<meta property="og:title" content="Executive Gift Set">', false)
        ->assertSee('<meta property="og:image" content="https://cdn.example.com/front.jpg">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertDontSee('<meta name="keywords"', false);
});

test('a product page uses the SEO fields set in the admin panel', function () {
    Storage::fake('public');
    $product = Product::factory()->create([
        'meta_title' => 'Branded Gift Sets for Teams | Velo',
        'meta_description' => 'Custom gift sets with your logo.',
        'meta_keywords' => 'gift sets, corporate gifts',
        'canonical_url' => 'https://velo.example/product/main-gift-set',
        'og_title' => 'Gift sets your team will love',
        'og_description' => 'Share-friendly summary.',
        'og_image' => 'seo/gift-set.jpg',
        'robots' => 'noindex,follow',
    ]);

    $this->get(route('products.show', $product))
        ->assertSee('<title>Branded Gift Sets for Teams | Velo</title>', false)
        ->assertSee('<meta name="description" content="Custom gift sets with your logo.">', false)
        ->assertSee('<meta name="keywords" content="gift sets, corporate gifts">', false)
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="https://velo.example/product/main-gift-set">', false)
        ->assertSee('<meta property="og:title" content="Gift sets your team will love">', false)
        ->assertSee('<meta property="og:description" content="Share-friendly summary.">', false)
        ->assertSee('<meta property="og:image" content="'.url(Storage::disk('public')->url('seo/gift-set.jpg')).'">', false);
});

test('a product page describes the product as structured data', function () {
    $category = Category::factory()->create(['name' => 'Gift Sets']);
    $product = Product::factory()->create([
        'name' => 'Executive Gift Set',
        'sku' => 'VPG-GS-001',
        'price' => 1250,
        'minimum_qty' => 25,
        'description' => '<p>Premium corporate gifting set.</p>',
    ]);
    $product->categories()->attach($category);
    $product->images()->createMany([
        ['path' => 'https://cdn.example.com/front.jpg', 'sort_order' => 0],
        ['path' => 'https://cdn.example.com/open.jpg', 'sort_order' => 1],
    ]);

    $data = structuredData($this->get(route('products.show', $product)))['Product'];

    expect($data)
        ->toMatchArray([
            'name' => 'Executive Gift Set',
            'sku' => 'VPG-GS-001',
            'description' => 'Premium corporate gifting set.',
            'image' => ['https://cdn.example.com/front.jpg', 'https://cdn.example.com/open.jpg'],
            'category' => 'Gift Sets',
            'url' => route('products.show', $product),
        ])
        ->and($data['offers'])->toMatchArray([
            'price' => '1250.00',
            'priceCurrency' => 'INR',
            'availability' => 'https://schema.org/InStock',
            'eligibleQuantity' => ['@type' => 'QuantitativeValue', 'minValue' => 25],
        ]);
});

test('structured data uses the configured currency code', function () {
    SiteSetting::put('currency_code', 'USD');
    $product = Product::factory()->create();

    expect(structuredData($this->get(route('products.show', $product)))['Product']['offers']['priceCurrency'])->toBe('USD');
});

test('structured data leaves out the price while prices are hidden', function () {
    SiteSetting::put('show_prices', '0');
    $product = Product::factory()->create(['price' => 1250]);

    expect(structuredData($this->get(route('products.show', $product)))['Product'])->not->toHaveKey('offers');
});

test('structured data cannot close its script tag early', function () {
    $product = Product::factory()->create(['name' => 'Gift </script><script>alert(1)</script>']);

    $response = $this->get(route('products.show', $product))->assertDontSee('<script>alert(1)', false);

    expect(structuredData($response)['Product']['name'])->toBe('Gift </script><script>alert(1)</script>');
});

test('breadcrumbs are described as structured data', function () {
    $parent = Category::factory()->create(['name' => 'Gifts']);
    $category = Category::factory()->create(['name' => 'Gift Sets', 'parent_id' => $parent->id]);
    $product = Product::factory()->create(['name' => 'Executive Gift Set']);
    $product->categories()->attach($category);

    $items = structuredData($this->get(route('products.show', $product)))['BreadcrumbList']['itemListElement'];

    expect($items)->toBe([
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Gifts', 'item' => route('categories.show', $parent)],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Gift Sets', 'item' => route('categories.show', $category)],
        ['@type' => 'ListItem', 'position' => 4, 'name' => 'Executive Gift Set'],
    ]);
});

test('a category page uses its SEO fields', function () {
    Storage::fake('public');
    $category = Category::factory()->create([
        'name' => 'Diaries',
        'meta_title' => 'Custom Diaries with Your Logo',
        'meta_description' => 'Branded diaries and notebooks.',
        'og_image' => 'seo/diaries.jpg',
        'robots' => 'noindex,nofollow',
    ]);

    $this->get(route('categories.show', $category))
        ->assertSee('<title>Custom Diaries with Your Logo</title>', false)
        ->assertSee('<meta name="description" content="Branded diaries and notebooks.">', false)
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false)
        ->assertSee('<meta property="og:image" content="'.url(Storage::disk('public')->url('seo/diaries.jpg')).'">', false);
});

test('a category listing is canonical without its sorting and filters but keeps the page number', function () {
    $category = Category::factory()->create();

    $this->get(route('categories.show', [$category, 'sort' => 'price-asc', 'min' => 500]))
        ->assertSee('<link rel="canonical" href="'.route('categories.show', $category).'">', false);

    $this->get(route('categories.show', [$category, 'sort' => 'price-asc', 'page' => 2]))
        ->assertSee('<link rel="canonical" href="'.route('categories.show', $category).'?page=2">', false);
});

test('a price range listing keeps its range in the canonical address', function () {
    $this->get(route('price-range', ['min' => 500, 'max' => 1000, 'sort' => 'newest']))
        ->assertSee('<link rel="canonical" href="'.route('price-range').'?min=500&amp;max=1000">', false);
});

test('a content page uses its SEO fields', function () {
    $page = Page::factory()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'meta_title' => 'About Velo Printing & Gifting',
        'meta_description' => 'Our story.',
        'og_title' => 'Meet Velo',
    ]);

    $this->get(route('about'))
        ->assertSee('<title>About Velo Printing &amp; Gifting</title>', false)
        ->assertSee('<meta name="description" content="Our story.">', false)
        ->assertSee('<link rel="canonical" href="'.$page->url.'">', false)
        ->assertSee('<meta property="og:title" content="Meet Velo">', false);
});

test('the home page uses the SEO settings and describes the business as structured data', function () {
    SiteSetting::put('home_meta_title', 'Corporate Gifts & Printing in India | Velo');
    SiteSetting::put('home_meta_description', 'Branded corporate gifts delivered in bulk.');
    SiteSetting::put('email', 'info@velo.example');
    SiteSetting::put('instagram_url', 'https://instagram.com/velo');
    SiteSetting::put('facebook_url', '');

    $response = $this->get('/')
        ->assertSee('<title>Corporate Gifts &amp; Printing in India | Velo</title>', false)
        ->assertSee('<meta name="description" content="Branded corporate gifts delivered in bulk.">', false);

    expect(structuredData($response)['Organization'])->toMatchArray([
        'name' => 'Velo Printing & Gifting',
        'url' => route('home'),
        'email' => 'info@velo.example',
        'sameAs' => ['https://instagram.com/velo'],
    ]);
});

test('the home page falls back to the business name and tagline', function () {
    SiteSetting::put('tagline', 'Corporate gifting, made memorable.');

    $this->get('/')
        ->assertSee('<title>Velo Printing &amp; Gifting</title>', false)
        ->assertSee('<meta name="description" content="Corporate gifting, made memorable.">', false);
});

test('pages without their own image share the default image', function () {
    Storage::fake('public');
    SiteSetting::put('default_og_image', 'seo/default.jpg');

    $this->get(route('contact'))
        ->assertSee('<meta property="og:image" content="'.url(Storage::disk('public')->url('seo/default.jpg')).'">', false);
});

test('search results are kept out of search engines', function () {
    $this->get(route('search', ['q' => 'diary']))
        ->assertSee('<meta name="robots" content="noindex,follow">', false);
});

test('the not found page is kept out of search engines', function () {
    $this->get('/product/does-not-exist')
        ->assertNotFound()
        ->assertSee('<meta name="robots" content="noindex,follow">', false);
});
