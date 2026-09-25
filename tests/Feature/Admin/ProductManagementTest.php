<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function productPayload(array $categoryIds, array $overrides = []): array
{
    return [
        'name' => 'Executive Gift Set',
        'slug' => '',
        'sku' => 'VPG-GS-001',
        'categories' => $categoryIds,
        'description' => '<div>Corporate gifting set suitable for events.</div>',
        'price' => '1250',
        'minimum_qty' => '25',
        'display_order' => '',
        'is_active' => '1',
        'is_featured' => '0',
        'robots' => 'index,follow',
        ...$overrides,
    ];
}

describe('index', function () {
    test('finds products by name or SKU', function (string $search) {
        Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001']);
        Product::factory()->create(['name' => 'Metal Pen', 'sku' => 'VPG-PN-001']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.index', ['search' => $search]))
            ->assertOk()
            ->assertSee('Executive Gift Set')
            ->assertDontSee('Metal Pen');
    })->with([
        'name' => 'executive',
        'SKU' => 'GS-001',
    ]);

    test('filters by category and status', function () {
        $pens = Category::factory()->create();
        Product::factory()->hasAttached($pens)->create(['name' => 'Metal Pen']);
        Product::factory()->inactive()->hasAttached($pens)->create(['name' => 'Retired Pen']);
        Product::factory()->create(['name' => 'Laptop Bag']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.index', ['category' => $pens->id, 'status' => 'active']))
            ->assertOk()
            ->assertSee('Metal Pen')
            ->assertDontSee('Retired Pen')
            ->assertDontSee('Laptop Bag');
    });

    test('shows an empty state when nothing matches', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.index', ['search' => 'nothing']))
            ->assertOk()
            ->assertSee('No products match these filters.');
    });
});

describe('store', function () {
    test('creates a product in several categories with uploaded images', function () {
        Storage::fake('public');
        $gifts = Category::factory()->create();
        $kits = Category::factory()->create(['parent_id' => $gifts->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.store'), productPayload([$gifts->id, $kits->id], [
                'new_images' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('back.png')],
            ]))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'Product “Executive Gift Set” created.');

        $product = Product::query()->sole();

        expect($product)
            ->slug->toBe('executive-gift-set')
            ->sku->toBe('VPG-GS-001')
            ->price->toBe('1250.00')
            ->minimum_qty->toBe(25)
            ->is_active->toBeTrue()
            ->is_featured->toBeFalse()
            ->and($product->categories->pluck('id')->sort()->values()->all())->toBe([$gifts->id, $kits->id])
            ->and($product->images->pluck('sort_order')->all())->toBe([0, 1]);

        $product->images->each(fn (ProductImage $image) => Storage::disk('public')->assertExists($image->path));
    });

    test('rejects invalid product details', function (array $overrides, string $field, string $message) {
        $category = Category::factory()->create();
        Product::factory()->create(['sku' => 'VPG-TAKEN-1']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.store'), productPayload([$category->id], $overrides))
            ->assertSessionHasErrors([$field => $message]);

        expect(Product::query()->count())->toBe(1);
    })->with([
        'negative price' => [['price' => '-5'], 'price', 'The price field must be at least 0.'],
        'price with three decimals' => [['price' => '10.555'], 'price', 'The price field must have 0-2 decimal places.'],
        'zero minimum quantity' => [['minimum_qty' => '0'], 'minimum_qty', 'The minimum quantity field must be at least 1.'],
        'fractional minimum quantity' => [['minimum_qty' => '2.5'], 'minimum_qty', 'The minimum quantity field must be an integer.'],
        'duplicate SKU' => [['sku' => 'VPG-TAKEN-1'], 'sku', 'The SKU has already been taken.'],
        'SKU with spaces' => [['sku' => 'VPG GS 001'], 'sku', 'The SKU field must only contain letters, numbers, dashes, and underscores.'],
        'no categories' => [['categories' => []], 'categories', 'Choose at least one category.'],
        'empty rich text' => [['description' => '<div><br></div>'], 'description', 'The description field is required.'],
    ]);

    test('rejects uploads that are not images', function () {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.store'), productPayload([$category->id], [
                'new_images' => [UploadedFile::fake()->create('script.php', 10, 'text/x-php')],
            ]))
            ->assertSessionHasErrors('new_images.0');

        expect(Product::query()->count())->toBe(0);
    });

    test('flashes an SEO warning when the meta title is already used', function () {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Classic Gift Set', 'meta_title' => 'Gift sets for teams']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.store'), productPayload([$category->id], ['meta_title' => 'Gift sets for teams']))
            ->assertSessionHas('warnings', ['“Classic Gift Set” already uses this meta title. A unique meta title helps search engines tell the pages apart.']);
    });
});

describe('update', function () {
    test('saves changes and redirects the old product address to the new one', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->hasAttached($category)->create(['name' => 'Steel Bottle', 'slug' => 'steel-bottle', 'sku' => 'VPG-DW-001']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.products.update', $product), productPayload([$category->id], [
                'name' => 'Insulated Steel Bottle',
                'slug' => 'insulated-steel-bottle',
                'sku' => 'VPG-DW-001',
                'is_featured' => '1',
            ]))
            ->assertRedirect(route('admin.products.edit', 'insulated-steel-bottle'))
            ->assertSessionHas('status', 'Product saved.');

        expect($product->fresh())->name->toBe('Insulated Steel Bottle')->is_featured->toBeTrue();

        $this->get('/product/steel-bottle')
            ->assertStatus(301)
            ->assertRedirect(route('products.show', 'insulated-steel-bottle'));
    });

    test('updates alt text, removes images, adds uploads and puts the chosen main image first', function () {
        Storage::fake('public');
        $category = Category::factory()->create();
        $product = Product::factory()->hasAttached($category)->create(['sku' => 'VPG-BG-001']);
        [$first, $second, $third] = collect(['a.jpg', 'b.jpg', 'c.jpg'])->map(fn (string $name, int $index) => $product->images()->create([
            'path' => UploadedFile::fake()->image($name)->store('products', 'public'),
            'sort_order' => $index,
        ]))->all();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.products.update', $product), productPayload([$category->id], [
                'sku' => 'VPG-BG-001',
                'primary_image' => $third->id,
                'images' => [
                    $first->id => ['alt' => 'Front view', 'sort_order' => 0, 'remove' => '0'],
                    $second->id => ['alt' => '', 'sort_order' => 1, 'remove' => '1'],
                    $third->id => ['alt' => 'Side view', 'sort_order' => 2, 'remove' => '0'],
                ],
                'new_images' => [UploadedFile::fake()->image('d.jpg')],
            ]))
            ->assertSessionHasNoErrors();

        $images = $product->fresh()->images;

        expect($images->pluck('id')->take(2)->all())->toBe([$third->id, $first->id])
            ->and($images->pluck('sort_order')->all())->toBe([0, 1, 2])
            ->and($images->pluck('alt')->all())->toBe(['Side view', 'Front view', null])
            ->and($product->fresh()->primaryImage->id)->toBe($third->id);

        $this->assertModelMissing($second);
        Storage::disk('public')->assertMissing($second->path);
    });

    test('ignores image fields for images that belong to another product', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->hasAttached($category)->create(['sku' => 'VPG-BG-001']);
        $otherImage = Product::factory()->create()->images()->create(['path' => 'products/other.jpg']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.products.update', $product), productPayload([$category->id], [
                'sku' => 'VPG-BG-001',
                'images' => [$otherImage->id => ['alt' => 'Hijacked', 'remove' => '1']],
            ]));

        expect($otherImage->fresh())->not->toBeNull()->alt->toBeNull();
    });

    test('shows the edit form with the product and a duplicate meta title warning', function () {
        Product::factory()->create(['name' => 'Classic Gift Set', 'meta_title' => 'Gift sets for teams']);
        $product = Product::factory()->create(['name' => 'Premium Gift Set', 'meta_title' => 'Gift sets for teams']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Premium Gift Set')
            ->assertSee('“Classic Gift Set” already uses this meta title.', false);
    });
});

describe('status and order', function () {
    test('features and hides a product from the list', function () {
        $product = Product::factory()->create();
        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.products.status.update', $product), ['is_featured' => '1']);
        $this->actingAs($admin)->patch(route('admin.products.status.update', $product), ['is_active' => '0']);

        expect($product->fresh())->is_featured->toBeTrue()->is_active->toBeFalse();
        $this->get('/')->assertDontSee($product->name);
    });

    test('saves the order of products within a category', function () {
        $pens = Category::factory()->create();
        $metal = Product::factory()->hasAttached($pens)->create(['name' => 'Metal Pen']);
        $stylus = Product::factory()->hasAttached($pens)->create(['name' => 'Stylus Pen']);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.product-order.update'), ['category' => $pens->id, 'order' => [$metal->id => 2, $stylus->id => 1]])
            ->assertSessionHas('status', "Product order in “{$pens->name}” saved.");

        expect($pens->products()->orderByPivot('display_order')->pluck('name')->all())->toBe(['Stylus Pen', 'Metal Pen'])
            ->and($metal->fresh()->display_order)->toBe(0);
    });

    test('saves the overall product order when no category is chosen', function () {
        $metal = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.product-order.update'), ['order' => [$metal->id => 7]]);

        expect($metal->fresh()->display_order)->toBe(7);
    });
});

describe('destroy', function () {
    test('deletes the product with its images and old addresses', function () {
        Storage::fake('public');
        $product = Product::factory()->create(['slug' => 'old-name']);
        $product->update(['slug' => 'new-name']);
        $image = $product->images()->create(['path' => UploadedFile::fake()->image('a.jpg')->store('products', 'public')]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertModelMissing($product);
        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing($image->path);
        $this->assertDatabaseMissing('slug_redirects', ['old_slug' => 'old-name']);
    });
});

test('the create form lists the categories to choose from', function () {
    Category::factory()->create(['name' => 'Drinkware']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Drinkware')
        ->assertSee('Create product');
});
