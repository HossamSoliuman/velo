<?php

use App\Models\Category;
use App\Models\SiteSetting;

test('navigation lists only active menu categories in display order', function () {
    Category::factory()->create(['name' => 'Pens', 'display_order' => 2]);
    Category::factory()->create(['name' => 'Gift Sets', 'display_order' => 1]);
    Category::factory()->inactive()->create(['name' => 'Retired Range']);
    Category::factory()->hiddenFromMenu()->create(['name' => 'Seasonal Specials']);

    expect(Category::navigation()->pluck('name')->all())->toBe(['Gift Sets', 'Pens']);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Gift Sets', 'Pens'])
        ->assertDontSee('Retired Range')
        ->assertDontSee('Seasonal Specials');
});

test('sub-categories appear beneath their parent in the mega menu', function () {
    $bags = Category::factory()->create(['name' => 'Bags']);
    Category::factory()->create(['name' => 'Laptop Bags', 'parent_id' => $bags->id]);
    Category::factory()->inactive()->create(['name' => 'Discontinued Bags', 'parent_id' => $bags->id]);

    $navigation = Category::navigation();

    expect($navigation->pluck('name')->all())->toBe(['Bags'])
        ->and($navigation->first()->children->pluck('name')->all())->toBe(['Laptop Bags']);

    $this->get('/')->assertSee('Laptop Bags')->assertDontSee('Discontinued Bags');
});

test('navigation cache is refreshed when a category changes', function () {
    $category = Category::factory()->create(['name' => 'Diaries']);

    expect(Category::navigation()->pluck('name')->all())->toBe(['Diaries']);

    $category->update(['is_active' => false]);

    expect(Category::navigation())->toBeEmpty();
});

test('price range links come from site settings', function () {
    SiteSetting::put('price_ranges', json_encode([
        ['min' => 0, 'max' => 500],
        ['min' => 500, 'max' => null],
    ]));

    $this->get('/')
        ->assertOk()
        ->assertSee('Under ₹500')
        ->assertSee('Above ₹500')
        ->assertSee(route('price-range', ['min' => 500]), false);
});

test('admin layout renders its navigation', function () {
    $this->blade('<x-layouts.admin title="Dashboard">Body</x-layouts.admin>')
        ->assertSee('Velo Admin')
        ->assertSee('Categories')
        ->assertSee('Enquiries')
        ->assertSee('Body');
});

test('navigation can be read back from a serializing cache store', function () {
    config(['cache.default' => 'database']);
    $bags = Category::factory()->create(['name' => 'Bags']);
    Category::factory()->create(['name' => 'Backpacks', 'parent_id' => $bags->id]);

    Category::navigation();
    $cached = Category::navigation();

    expect($cached->first())->toBeInstanceOf(Category::class)
        ->and($cached->first()->children->first()->name)->toBe('Backpacks');
});

test('the menu marks the top-level category being browsed as current', function () {
    $bags = Category::factory()->create(['name' => 'Bags']);
    $backpacks = Category::factory()->create(['name' => 'Backpacks', 'parent_id' => $bags->id]);
    $pens = Category::factory()->create(['name' => 'Pens']);
    $currentLink = fn (Category $category) => '~href="'.preg_quote(route('categories.show', $category), '~').'"[^>]*aria-current="page"[^>]*>'.$category->name.'</a>~';

    $html = $this->get(route('categories.show', $backpacks))->getContent();

    expect($html)->toMatch($currentLink($bags))
        ->not->toMatch($currentLink($pens));
});
