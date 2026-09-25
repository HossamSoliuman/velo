<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;

test('the product page shows every product field and the enquire action', function () {
    $category = Category::factory()->create(['name' => 'Gift Sets']);
    $product = Product::factory()->create([
        'name' => 'Executive Gift Set',
        'sku' => 'VPG-GS-001',
        'price' => 1250,
        'minimum_qty' => 25,
        'description' => '<p>Corporate gifting set with a <strong>premium</strong> finish.</p>',
    ]);
    $product->categories()->attach($category);
    $product->images()->createMany([
        ['path' => 'https://cdn.example.com/front.jpg', 'alt' => 'Gift set front view', 'sort_order' => 0],
        ['path' => 'https://cdn.example.com/open.jpg', 'alt' => 'Gift set opened', 'sort_order' => 1],
    ]);

    $this->get(route('products.show', $product))
        ->assertSee('Executive Gift Set')
        ->assertSee('VPG-GS-001')
        ->assertSee('₹1,250')
        ->assertSeeInOrder(['Minimum quantity', '25'])
        ->assertSee('<strong>premium</strong>', false)
        ->assertSee(route('categories.show', $category))
        ->assertSeeInOrder(['https://cdn.example.com/front.jpg', 'https://cdn.example.com/open.jpg'])
        ->assertSee('alt="Gift set front view"', false)
        ->assertSee(route('contact', ['product' => $product->slug]).'#enquiry');
});

test('the product page shows price on request when prices are hidden', function () {
    SiteSetting::put('show_prices', '0');
    $product = Product::factory()->create(['price' => 1250]);

    $this->get(route('products.show', $product))
        ->assertSee('Price on request')
        ->assertDontSee('₹1,250');
});

test('an inactive product cannot be opened', function () {
    $product = Product::factory()->inactive()->create();

    $this->get(route('products.show', $product))->assertNotFound();
});

test('related products are other active products from the same categories', function () {
    $bags = Category::factory()->create();
    $product = Product::factory()->create(['name' => 'Laptop Backpack']);
    $product->categories()->attach($bags);
    $bags->products()->attach(Product::factory()->create(['name' => 'Canvas Tote']));
    $bags->products()->attach(Product::factory()->inactive()->create(['name' => 'Hidden Duffel']));
    Product::factory()->create(['name' => 'Metal Pen']);

    $this->get(route('products.show', $product))
        ->assertViewHas('relatedProducts', fn ($products) => $products->pluck('name')->all() === ['Canvas Tote'])
        ->assertSee('Related Products');
});

test('at most four related products are shown', function () {
    $bags = Category::factory()->create();
    $product = Product::factory()->create();
    $product->categories()->attach($bags);
    $bags->products()->attach(Product::factory()->count(5)->create());

    $this->get(route('products.show', $product))
        ->assertViewHas('relatedProducts', fn ($products) => $products->count() === 4);
});

test('breadcrumbs follow the most specific visible category', function () {
    $bags = Category::factory()->create(['name' => 'Bags']);
    $backpacks = Category::factory()->create(['name' => 'Backpacks', 'parent_id' => $bags->id]);
    $hidden = Category::factory()->inactive()->create(['name' => 'Clearance']);
    $product = Product::factory()->create(['name' => 'Anti-Theft Backpack']);
    $product->categories()->attach([$bags->id, $backpacks->id, $hidden->id]);

    $this->get(route('products.show', $product))
        ->assertViewHas('primaryCategory', fn (Category $category) => $category->is($backpacks))
        ->assertSeeInOrder(['Bags', 'Backpacks', 'Anti-Theft Backpack'])
        ->assertDontSee('Clearance');
});

test('the product page offers WhatsApp when a number is set', function () {
    SiteSetting::put('whatsapp', '+91 98765 43210');
    $product = Product::factory()->create();

    $this->get(route('products.show', $product))->assertSee('https://wa.me/919876543210?text=');
});
