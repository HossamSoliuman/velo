<?php

use App\Models\Category;
use App\Models\Product;

test('the categories page lists active top-level categories with their active sub-categories', function () {
    $bags = Category::factory()->create(['name' => 'Bags', 'display_order' => 1]);
    Category::factory()->create(['name' => 'Laptop Bags', 'parent_id' => $bags->id]);
    Category::factory()->inactive()->create(['name' => 'Discontinued Bags', 'parent_id' => $bags->id]);
    Category::factory()->create(['name' => 'Pens', 'display_order' => 2]);
    Category::factory()->hiddenFromMenu()->create(['name' => 'Seasonal Specials', 'display_order' => 3]);
    Category::factory()->inactive()->create(['name' => 'Retired Range']);

    $this->get(route('categories.index'))
        ->assertViewHas('categories', fn ($categories) => $categories->pluck('name')->all() === ['Bags', 'Pens', 'Seasonal Specials']
            && $categories->first()->children->pluck('name')->all() === ['Laptop Bags'])
        ->assertSee('Seasonal Specials')
        ->assertDontSee('Discontinued Bags')
        ->assertDontSee('Retired Range');
});

test('category product counts include active sub-categories and count each product once', function () {
    $bags = Category::factory()->create(['name' => 'Bags']);
    $laptopBags = Category::factory()->create(['parent_id' => $bags->id]);
    $discontinued = Category::factory()->inactive()->create(['parent_id' => $bags->id]);
    Product::factory()->create()->categories()->attach([$bags->id, $laptopBags->id]);
    Product::factory()->create()->categories()->attach($laptopBags);
    Product::factory()->inactive()->create()->categories()->attach($bags);
    Product::factory()->create()->categories()->attach($discontinued);

    $this->get(route('categories.index'))
        ->assertViewHas('productCounts', fn ($counts) => $counts[$bags->id] === 2)
        ->assertSee('2 products');
});

test('a category page lists active products from the category and its active sub-categories', function () {
    $bags = Category::factory()->create(['name' => 'Bags', 'description' => '<p>Bags for <strong>every</strong> team.</p>']);
    $laptopBags = Category::factory()->create(['parent_id' => $bags->id]);
    $discontinued = Category::factory()->inactive()->create(['parent_id' => $bags->id]);
    Product::factory()->create(['name' => 'Canvas Tote'])->categories()->attach($bags);
    Product::factory()->create(['name' => 'Laptop Sleeve'])->categories()->attach($laptopBags);
    Product::factory()->inactive()->create(['name' => 'Hidden Duffel'])->categories()->attach($bags);
    Product::factory()->create(['name' => 'Old Satchel'])->categories()->attach($discontinued);
    Product::factory()->create(['name' => 'Metal Pen']);

    $this->get(route('categories.show', $bags))
        ->assertSee('<strong>every</strong>', false)
        ->assertSee('Canvas Tote')
        ->assertSee('Laptop Sleeve')
        ->assertDontSee('Hidden Duffel')
        ->assertDontSee('Old Satchel')
        ->assertDontSee('Metal Pen');
});

test('a hidden category cannot be opened', function (Category $category) {
    $this->get(route('categories.show', $category))->assertNotFound();
})->with([
    'inactive category' => fn () => Category::factory()->inactive()->create(),
    'sub-category of an inactive category' => fn () => Category::factory()->create([
        'parent_id' => Category::factory()->inactive()->create()->id,
    ]),
]);

test('a category hidden from the menu can still be opened', function () {
    $category = Category::factory()->hiddenFromMenu()->create(['name' => 'Seasonal Specials']);

    $this->get(route('categories.show', $category))->assertSee('Seasonal Specials');
});

test('an unknown category is not found', function () {
    $this->get('/category/no-such-category')->assertNotFound();
});

test('a sub-category page links to its parent and sibling sub-categories', function () {
    $bags = Category::factory()->create(['name' => 'Bags']);
    $laptopBags = Category::factory()->create(['name' => 'Laptop Bags', 'parent_id' => $bags->id, 'display_order' => 1]);
    Category::factory()->create(['name' => 'Backpacks', 'parent_id' => $bags->id, 'display_order' => 2]);

    $this->get(route('categories.show', $laptopBags))
        ->assertSee(route('categories.show', $bags))
        ->assertSeeInOrder(['All Bags', 'Laptop Bags', 'Backpacks']);
});

test('recommended order follows the product order set for the category', function () {
    $bags = Category::factory()->create();
    $bags->products()->attach(Product::factory()->create(['name' => 'Second Bag', 'display_order' => 0]), ['display_order' => 2]);
    $bags->products()->attach(Product::factory()->create(['name' => 'First Bag', 'display_order' => 9]), ['display_order' => 1]);

    $this->get(route('categories.show', $bags))->assertSeeInOrder(['First Bag', 'Second Bag']);
});

test('a category page applies the price filter', function () {
    $bags = Category::factory()->create();
    $bags->products()->attach(Product::factory()->create(['name' => 'Budget Tote', 'price' => 150]));
    $bags->products()->attach(Product::factory()->create(['name' => 'Leather Backpack', 'price' => 2400]));

    $this->get(route('categories.show', ['category' => $bags, 'min' => 0, 'max' => 250]))
        ->assertSee('Budget Tote')
        ->assertDontSee('Leather Backpack');
});

test('category products are split into pages of 24', function () {
    $bags = Category::factory()->create();
    $bags->products()->attach(Product::factory()->count(25)->create());

    $this->get(route('categories.show', $bags))
        ->assertViewHas('products', fn ($products) => $products->count() === 24 && $products->total() === 25);

    $this->get(route('categories.show', ['category' => $bags, 'page' => 2]))
        ->assertViewHas('products', fn ($products) => $products->count() === 1);
});

test('an empty category explains that products are on the way', function () {
    $category = Category::factory()->create();

    $this->get(route('categories.show', $category))->assertSee('No products in this category yet');
});
