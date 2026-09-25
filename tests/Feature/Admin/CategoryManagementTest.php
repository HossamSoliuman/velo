<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function categoryPayload(array $overrides = []): array
{
    return [
        'name' => 'Gift Sets',
        'slug' => '',
        'parent_id' => '',
        'description' => '<div>Curated corporate gift sets.</div>',
        'display_order' => 3,
        'is_active' => '1',
        'show_in_menu' => '1',
        'robots' => 'index,follow',
        ...$overrides,
    ];
}

describe('index', function () {
    test('lists categories with their sub-categories and product counts', function () {
        $bags = Category::factory()->create(['name' => 'Bags', 'display_order' => 1]);
        $backpacks = Category::factory()->create(['name' => 'Backpacks', 'parent_id' => $bags->id]);
        Category::factory()->create(['name' => 'Pens', 'display_order' => 2]);
        Product::factory()->count(2)->hasAttached($backpacks)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bags', 'Backpacks', '2', 'Pens']);
    });
});

describe('store', function () {
    test('creates a category with a slug generated from its name and an uploaded image', function () {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload([
                'image' => UploadedFile::fake()->image('gift-sets.jpg', 400, 400),
            ]))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category “Gift Sets” created.');

        $category = Category::query()->sole();

        expect($category)
            ->slug->toBe('gift-sets')
            ->display_order->toBe(3)
            ->is_active->toBeTrue()
            ->show_in_menu->toBeTrue()
            ->description->toBe('<div>Curated corporate gift sets.</div>');

        Storage::disk('public')->assertExists($category->image);
    });

    test('adds a number to a generated slug that is already taken', function () {
        Category::factory()->create(['name' => 'Gift Sets', 'slug' => 'gift-sets']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload());

        expect(Category::query()->latest('id')->first()->slug)->toBe('gift-sets-2');
    });

    test('cleans up a slug typed by the admin', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload(['slug' => ' Corporate Gifts! ']));

        expect(Category::query()->sole()->slug)->toBe('corporate-gifts');
    });

    test('rejects a slug another category already uses', function () {
        Category::factory()->create(['slug' => 'gift-sets']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload(['slug' => 'gift-sets']))
            ->assertSessionHasErrors(['slug' => 'The slug has already been taken.']);

        expect(Category::query()->count())->toBe(1);
    });

    test('requires a name', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload(['name' => '']))
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    });

    test('only allows a top-level category as the parent', function () {
        $bags = Category::factory()->create();
        $backpacks = Category::factory()->create(['parent_id' => $bags->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload(['parent_id' => $backpacks->id]))
            ->assertSessionHasErrors(['parent_id' => 'The selected parent category is invalid.']);
    });

    test('rejects a file that is not an image', function () {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload([
                'image' => UploadedFile::fake()->create('catalogue.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('image');

        expect(Category::query()->count())->toBe(0);
    });

    test('strips scripts and unsafe attributes from the description', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), categoryPayload([
                'description' => '<div onclick="steal()">Hello<script>alert(1)</script> <a href="javascript:alert(1)">link</a></div>',
            ]));

        expect(Category::query()->sole()->description)->toBe('<div>Hello <a>link</a></div>');
    });
});

describe('update', function () {
    test('saves changes and keeps the old address working with a permanent redirect', function () {
        $category = Category::factory()->create(['name' => 'Diaries', 'slug' => 'diaries']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.categories.update', $category), categoryPayload(['name' => 'Notebooks', 'slug' => 'notebooks']))
            ->assertRedirect(route('admin.categories.edit', 'notebooks'))
            ->assertSessionHas('status', 'Category saved.');

        expect($category->fresh())->name->toBe('Notebooks')->slug->toBe('notebooks');

        $this->get('/category/diaries')->assertRedirect(route('categories.show', 'notebooks'))->assertStatus(301);
    });

    test('replaces the image and deletes the old file', function () {
        Storage::fake('public');
        $oldImage = UploadedFile::fake()->image('old.jpg')->store('categories', 'public');
        $category = Category::factory()->create(['image' => $oldImage]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.categories.update', $category), categoryPayload(['image' => UploadedFile::fake()->image('new.jpg')]));

        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($category->fresh()->image);
    });

    test('removes the image when asked', function () {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('old.jpg')->store('categories', 'public');
        $category = Category::factory()->create(['image' => $image]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.categories.update', $category), categoryPayload(['remove_image' => '1']));

        expect($category->fresh()->image)->toBeNull();
        Storage::disk('public')->assertMissing($image);
    });

    test('does not let a category with sub-categories become a sub-category', function () {
        $bags = Category::factory()->create();
        Category::factory()->create(['parent_id' => $bags->id]);
        $gifts = Category::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.categories.update', $bags), categoryPayload(['parent_id' => $gifts->id]))
            ->assertSessionHasErrors(['parent_id' => 'This category has sub-categories, so it cannot be placed under another category.']);

        expect($bags->fresh()->parent_id)->toBeNull();
    });

    test('warns on the edit page when another category uses the same meta title', function () {
        Category::factory()->create(['name' => 'Pens', 'meta_title' => 'Corporate gifts in India']);
        $bags = Category::factory()->create(['meta_title' => 'Corporate gifts in India']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories.edit', $bags))
            ->assertOk()
            ->assertSee('“Pens” already uses this meta title.', false);
    });
});

describe('status and order', function () {
    test('hiding a category removes it from the website menu', function () {
        $category = Category::factory()->create(['name' => 'Keychains']);
        expect(Category::navigation()->pluck('name')->all())->toBe(['Keychains']);

        $this->actingAs(User::factory()->create())
            ->from(route('admin.categories.index'))
            ->patch(route('admin.categories.status.update', $category), ['is_active' => '0'])
            ->assertRedirect(route('admin.categories.index'));

        expect($category->fresh()->is_active)->toBeFalse()
            ->and(Category::navigation())->toBeEmpty();
    });

    test('removing a category from the menu keeps it active', function () {
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.categories.status.update', $category), ['show_in_menu' => '0']);

        expect($category->fresh())->show_in_menu->toBeFalse()->is_active->toBeTrue();
    });

    test('saves a new menu order', function () {
        $pens = Category::factory()->create(['name' => 'Pens', 'display_order' => 1]);
        $bags = Category::factory()->create(['name' => 'Bags', 'display_order' => 2]);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.category-order.update'), ['order' => [$pens->id => 5, $bags->id => 1]])
            ->assertSessionHas('status', 'Category order saved.');

        expect(Category::navigation()->pluck('name')->all())->toBe(['Bags', 'Pens']);
    });
});

describe('destroy', function () {
    test('deletes an empty category and moves its sub-categories up a level', function () {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('bags.jpg')->store('categories', 'public');
        $bags = Category::factory()->create(['image' => $image]);
        $backpacks = Category::factory()->create(['parent_id' => $bags->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $bags))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertModelMissing($bags);
        expect($backpacks->fresh()->parent_id)->toBeNull();
        Storage::disk('public')->assertMissing($image);
    });

    test('requires choosing where to move the products before deleting', function () {
        $pens = Category::factory()->create();
        Product::factory()->hasAttached($pens)->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $pens))
            ->assertSessionHasErrors(['reassign_to' => 'Choose a category to move this category’s products to before deleting it.']);

        $this->assertModelExists($pens);
    });

    test('moves the products to the chosen category and deletes the category', function () {
        $pens = Category::factory()->create();
        $stationery = Category::factory()->create(['name' => 'Stationery']);
        $products = Product::factory()->count(2)->hasAttached($pens)->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $pens), ['reassign_to' => $stationery->id])
            ->assertSessionHas('status', "Category “{$pens->name}” deleted. 2 products moved to “Stationery”.");

        $this->assertModelMissing($pens);
        expect($stationery->products()->pluck('products.id')->sort()->values()->all())
            ->toBe($products->pluck('id')->sort()->values()->all());
    });

    test('cannot move products to the category being deleted', function () {
        $pens = Category::factory()->create();
        Product::factory()->hasAttached($pens)->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $pens), ['reassign_to' => $pens->id])
            ->assertSessionHasErrors('reassign_to');

        $this->assertModelExists($pens);
    });
});

test('the create and edit forms render', function () {
    $category = Category::factory()->create(['name' => 'Drinkware']);
    $admin = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.categories.create'))->assertOk()->assertSee('Create category');
    $this->actingAs($admin)->get(route('admin.categories.edit', $category))->assertOk()->assertSee('Drinkware')->assertSee('Delete category');
});
