<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;

test('a price range includes its minimum and excludes its maximum', function () {
    foreach ([249.99, 250, 499.99, 500] as $price) {
        Product::factory()->create(['name' => "Gift at {$price}", 'price' => $price]);
    }

    $this->get(route('price-range', ['min' => 250, 'max' => 500]))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->sort()->values()->all() === ['Gift at 250', 'Gift at 499.99'])
        ->assertSee('₹250 – ₹500');
});

test('a range without a maximum shows everything from the minimum up', function () {
    Product::factory()->create(['name' => 'Desk Clock', 'price' => 2499]);
    Product::factory()->create(['name' => 'Crystal Award', 'price' => 2500]);
    Product::factory()->create(['name' => 'Smart Speaker', 'price' => 9000]);

    $this->get(route('price-range', ['min' => 2500]))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->sort()->values()->all() === ['Crystal Award', 'Smart Speaker'])
        ->assertSee('Above ₹2,500');
});

test('the price range page shows all active products when no range is chosen', function () {
    Product::factory()->create(['name' => 'Metal Pen', 'price' => 95]);
    Product::factory()->create(['name' => 'Smart Speaker', 'price' => 9000]);
    Product::factory()->inactive()->create(['name' => 'Hidden Mug']);

    $this->get(route('price-range'))
        ->assertViewHas('heading', 'Shop by Price')
        ->assertViewHas('products', fn ($products) => $products->total() === 2)
        ->assertDontSee('Hidden Mug');
});

test('the configured price ranges are offered on the page', function () {
    SiteSetting::put('price_ranges', json_encode([['min' => 0, 'max' => 500], ['min' => 500, 'max' => null]]));

    $this->get(route('price-range', ['min' => 0, 'max' => 500]))
        ->assertViewHas('heading', 'Under ₹500')
        ->assertSee(route('price-range', ['min' => 500]))
        ->assertSee('Above ₹500');
});

test('invalid filter values are ignored rather than rejected', function () {
    Product::factory()->count(2)->create();

    $this->get(route('price-range', ['min' => 'cheap', 'max' => '-5', 'sort' => 'random', 'page' => 'x']))
        ->assertViewHas('products', fn ($products) => $products->total() === 2)
        ->assertViewHas('heading', 'Shop by Price');
});

test('a maximum that is not above the minimum is ignored', function () {
    Product::factory()->create(['name' => 'Metal Pen', 'price' => 95]);
    Product::factory()->create(['name' => 'Leather Diary', 'price' => 600]);

    $this->get(route('price-range', ['min' => 500, 'max' => 100]))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->all() === ['Leather Diary']);
});

test('products can be sorted', function (string $sort, array $expectedOrder) {
    Product::factory()->create(['name' => 'Bamboo Charger', 'price' => 890, 'created_at' => now()->subDays(3)]);
    Product::factory()->create(['name' => 'Ceramic Mug', 'price' => 160, 'created_at' => now()->subDay()]);
    Product::factory()->create(['name' => 'Anti-Theft Backpack', 'price' => 1450, 'created_at' => now()->subDays(2)]);

    $this->get(route('price-range', ['sort' => $sort]))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->all() === $expectedOrder);
})->with([
    'newest first' => ['newest', ['Ceramic Mug', 'Anti-Theft Backpack', 'Bamboo Charger']],
    'price low to high' => ['price-asc', ['Ceramic Mug', 'Bamboo Charger', 'Anti-Theft Backpack']],
    'price high to low' => ['price-desc', ['Anti-Theft Backpack', 'Bamboo Charger', 'Ceramic Mug']],
    'name A to Z' => ['name-asc', ['Anti-Theft Backpack', 'Bamboo Charger', 'Ceramic Mug']],
]);

test('price browsing can be narrowed to a category', function () {
    $pens = Category::factory()->create(['slug' => 'pens']);
    Product::factory()->create(['name' => 'Metal Pen', 'price' => 95])->categories()->attach($pens);
    Product::factory()->create(['name' => 'Sticky Notes', 'price' => 90]);

    $this->get(route('price-range', ['max' => 250, 'category' => 'pens']))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->all() === ['Metal Pen'])
        ->assertSee(route('price-range', ['max' => 250]));
});

test('an unknown or hidden category filter is ignored', function () {
    Category::factory()->inactive()->create(['slug' => 'retired']);
    Product::factory()->count(2)->create();

    $this->get(route('price-range', ['category' => 'retired']))
        ->assertViewHas('selectedCategory', null)
        ->assertViewHas('products', fn ($products) => $products->total() === 2);
});

test('the listing keeps other filters in its links and resets the page', function () {
    Product::factory()->count(2)->create(['price' => 100]);

    $this->get(route('price-range', ['max' => 250, 'sort' => 'newest', 'page' => 1]))
        ->assertSee(e(route('price-range', ['sort' => 'newest'])), false)
        ->assertSee('<input type="hidden" name="max" value="250">', false);
});
