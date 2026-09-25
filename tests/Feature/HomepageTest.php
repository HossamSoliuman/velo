<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Database\Seeders\PageSeeder;

test('homepage renders with site settings', function () {
    SiteSetting::put('site_name', 'Velo Printing & Gifting');
    SiteSetting::put('phone', '+91 98765 43210');

    $this->get('/')
        ->assertOk()
        ->assertSee('Velo Printing &amp; Gifting', false)
        ->assertSee('+91 98765 43210');
});

test('homepage shows only active featured products', function () {
    $featured = Product::factory()->featured()->create(['name' => 'Executive Gift Set', 'price' => 1250]);
    $inactive = Product::factory()->featured()->inactive()->create(['name' => 'Hidden Gift Set']);
    Product::factory()->create(['name' => 'Regular Pen']);

    $this->get('/')
        ->assertSee($featured->name)
        ->assertSee('₹1,250')
        ->assertSee($featured->sku)
        ->assertDontSee($inactive->name)
        ->assertViewHas('featuredProducts', fn ($products) => $products->modelKeys() === [$featured->id]);
});

test('product prices can be hidden from settings', function () {
    SiteSetting::put('show_prices', '0');
    Product::factory()->featured()->create(['price' => 1250]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Price on request')
        ->assertDontSee('₹1,250');
});

test('homepage shows popular categories', function () {
    Category::factory()->create(['name' => 'Gift Sets']);

    $this->get('/')->assertOk()->assertSee('Gift Sets');
});

test('pages linked from the navigation respond', function (string $uri) {
    $this->seed(PageSeeder::class);
    Category::factory()->create(['slug' => 'gift-sets']);
    Product::factory()->create(['slug' => 'executive-gift-set']);

    $this->get($uri)->assertOk();
})->with(['/categories', '/category/gift-sets', '/product/executive-gift-set', '/price-range', '/search', '/about-us', '/e-catalog', '/contact', '/privacy-policy', '/terms-and-conditions']);

test('homepage lists the four newest active products as new arrivals', function () {
    Product::factory()->create(['name' => 'Oldest Mug', 'created_at' => now()->subDays(10)]);
    foreach (['Newer Pen', 'Newer Diary', 'Newer Bag'] as $days => $name) {
        Product::factory()->create(['name' => $name, 'created_at' => now()->subDays($days + 1)]);
    }
    Product::factory()->create(['name' => 'Newest Bottle']);
    Product::factory()->inactive()->create(['name' => 'Hidden Speaker', 'created_at' => now()->addMinute()]);

    $this->get('/')
        ->assertViewHas('newArrivals', fn ($products) => $products->pluck('name')->all() === ['Newest Bottle', 'Newer Pen', 'Newer Diary', 'Newer Bag'])
        ->assertSeeInOrder(['New Arrivals', 'Newest Bottle', 'Newer Pen', 'Newer Diary', 'Newer Bag'])
        ->assertDontSee('Hidden Speaker');
});

test('the corporate gifting promotion uses the wording from settings', function () {
    SiteSetting::put('promo_title', 'Diwali gifting made simple');
    SiteSetting::put('promo_text', 'Hampers packed and delivered to every office.');

    $this->get('/')
        ->assertSee('Diwali gifting made simple')
        ->assertSee('Hampers packed and delivered to every office.');
});
