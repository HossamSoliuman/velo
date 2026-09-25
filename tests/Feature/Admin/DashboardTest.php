<?php

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\User;

test('shows enquiry and catalogue totals, latest enquiries and recently updated products', function () {
    Enquiry::factory()->create(['name' => 'Priya Sharma']);
    Enquiry::factory()->read()->create();
    Product::factory()->count(2)->create();
    Product::factory()->featured()->create(['name' => 'Executive Gift Set']);
    Product::factory()->inactive()->create();
    Category::factory()->count(2)->create();
    Category::factory()->inactive()->create();

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Executive Gift Set')
        ->assertSee('Priya Sharma');

    expect($response->viewData('stats'))->toBe([
        'enquiries' => 2,
        'unreadEnquiries' => 1,
        'products' => 4,
        'activeProducts' => 3,
        'featuredProducts' => 1,
        'categories' => 3,
        'activeCategories' => 2,
    ]);
});

test('highlights only the matching section in the sidebar', function (string $routeName, string $section) {
    $html = $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertOk()
        ->getContent();

    preg_match_all('/<a [^>]*aria-current="page"[^>]*>(.*?)<\/a>/s', $html, $currentLinks);

    expect($currentLinks[1])->toHaveCount(1)
        ->and(trim(strip_tags($currentLinks[1][0])))->toBe($section);
})->with([
    'dashboard' => ['admin.dashboard', 'Dashboard'],
    'new product form' => ['admin.products.create', 'Products'],
]);
