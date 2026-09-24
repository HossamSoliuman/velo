<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;

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
    $regular = Product::factory()->create(['name' => 'Regular Pen']);

    $this->get('/')
        ->assertOk()
        ->assertSee($featured->name)
        ->assertSee('₹1,250')
        ->assertSee($featured->sku)
        ->assertDontSee($inactive->name)
        ->assertDontSee($regular->name);
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

test('placeholder pages linked from the navigation respond', function (string $uri) {
    $this->get($uri)->assertOk();
})->with(['/categories', '/category/gift-sets', '/product/executive-gift-set', '/price-range', '/search', '/about-us', '/e-catalog', '/contact', '/privacy-policy', '/terms-and-conditions']);
