<?php

use App\Models\Category;
use App\Models\Product;

test('every earlier slug of a product redirects to its current address', function () {
    $product = Product::factory()->create(['slug' => 'steel-bottle']);
    $product->update(['slug' => 'insulated-bottle']);
    $product->update(['slug' => 'insulated-steel-bottle']);

    foreach (['steel-bottle', 'insulated-bottle'] as $oldSlug) {
        $this->get("/product/{$oldSlug}")
            ->assertStatus(301)
            ->assertRedirect(route('products.show', 'insulated-steel-bottle'));
    }
});

test('a new product that takes an old slug is shown instead of being redirected', function () {
    $renamed = Product::factory()->create(['slug' => 'gift-set']);
    $renamed->update(['slug' => 'classic-gift-set']);

    Product::factory()->create(['slug' => 'gift-set']);

    $this->get('/product/gift-set')->assertOk();
    expect(Product::findByPreviousSlug('gift-set'))->toBeNull();
});

test('changing a slug back to an earlier value stops redirecting it', function () {
    $category = Category::factory()->create(['slug' => 'bags']);
    $category->update(['slug' => 'bags-and-backpacks']);
    $category->update(['slug' => 'bags']);

    $this->get('/category/bags')->assertOk();
    $this->get('/category/bags-and-backpacks')->assertStatus(301)->assertRedirect(route('categories.show', 'bags'));
});

test('product and category slug histories are kept apart', function () {
    $category = Category::factory()->create(['slug' => 'pens']);
    $category->update(['slug' => 'writing']);

    $this->get('/product/pens')->assertOk();
});
