<?php

use App\Models\Category;
use App\Models\Product;

test('search finds active products by part of their name, ignoring case', function () {
    Product::factory()->create(['name' => 'Insulated Steel Bottle']);
    Product::factory()->inactive()->create(['name' => 'Hidden Steel Bottle']);
    Product::factory()->create(['name' => 'Ceramic Mug']);

    $this->get(route('search', ['q' => 'BOTTLE']))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->all() === ['Insulated Steel Bottle'])
        ->assertSee('Results for “BOTTLE”');
});

test('search finds products by SKU', function () {
    Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001']);
    Product::factory()->create(['sku' => 'VPG-PN-001']);

    $this->get(route('search', ['q' => 'gs-001']))
        ->assertViewHas('products', fn ($products) => $products->pluck('sku')->all() === ['VPG-GS-001']);
});

test('every word of the search must match the name or SKU', function () {
    Product::factory()->create(['name' => 'Insulated Steel Bottle']);
    Product::factory()->create(['name' => 'Steel Pen']);
    Product::factory()->create(['name' => 'Glass Bottle']);

    $this->get(route('search', ['q' => 'bottle  steel']))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->all() === ['Insulated Steel Bottle']);
});

test('search can be narrowed to a category and its sub-categories', function () {
    $bags = Category::factory()->create(['slug' => 'bags']);
    $backpacks = Category::factory()->create(['parent_id' => $bags->id]);
    Product::factory()->create(['name' => 'Canvas Tote Bag'])->categories()->attach($bags);
    Product::factory()->create(['name' => 'Laptop Backpack Bag'])->categories()->attach($backpacks);
    Product::factory()->create(['name' => 'Paper Bag']);

    $this->get(route('search', ['q' => 'bag', 'category' => 'bags']))
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->sort()->values()->all() === ['Canvas Tote Bag', 'Laptop Backpack Bag']);
});

test('an empty search asks for a product name or SKU', function (array $query) {
    Product::factory()->create(['name' => 'Ceramic Mug']);

    $this->get(route('search', $query))
        ->assertViewHas('products', null)
        ->assertSee('Type a product name or SKU code above')
        ->assertDontSee('Ceramic Mug');
})->with([
    'no query' => [[]],
    'blank query' => [['q' => '   ']],
    'query sent as a list' => [['q' => ['mug']]],
]);

test('a search with no matches shows an empty state', function () {
    Product::factory()->create(['name' => 'Ceramic Mug']);

    $this->get(route('search', ['q' => 'hoverboard']))
        ->assertSee('No products found for “hoverboard”')
        ->assertSee('Ask us to source it');
});

test('search text is escaped when shown back to the visitor', function () {
    $this->get(route('search', ['q' => '<script>alert(1)</script>']))
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;', false);
});
