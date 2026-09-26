<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Sample categories and products. Real catalogue data is managed from the admin panel.
     *
     * Each product row: [name, price, minimum quantity, featured, sub-category].
     *
     * @var array<int, array{name: string, code: string, children: list<string>, products: list<array{0: string, 1: float, 2: int, 3: bool, 4?: string}>}>
     */
    private array $catalog = [
        ['name' => 'Gift Sets', 'code' => 'GS', 'children' => ['Welcome Kits', 'Festive Hampers'], 'products' => [
            ['Executive Gift Set', 1250, 25, true, 'Welcome Kits'],
            ['New Joiner Welcome Kit', 1850, 20, true, 'Welcome Kits'],
            ['Diwali Celebration Hamper', 2400, 10, false, 'Festive Hampers'],
            ['Eco-Friendly Bamboo Gift Set', 1350, 25, false, 'Welcome Kits'],
            ['Dry Fruit & Chocolate Hamper', 1950, 10, true, 'Festive Hampers'],
        ]],
        ['name' => 'Diaries', 'code' => 'DR', 'children' => ['Leather Diaries', 'Planners'], 'products' => [
            ['A5 PU Leather Diary', 320, 50, true, 'Leather Diaries'],
            ['Magnetic Flap Diary', 410, 50, false, 'Leather Diaries'],
            ['Undated Weekly Planner', 280, 50, false, 'Planners'],
            ['Kraft Paper Eco Notebook', 160, 100, false, 'Planners'],
            ['Diary & Pen Combo Box', 590, 25, false, 'Leather Diaries'],
        ]],
        ['name' => 'Bags', 'code' => 'BG', 'children' => ['Laptop Bags', 'Backpacks', 'Tote Bags'], 'products' => [
            ['Anti-Theft Laptop Backpack', 1450, 20, true, 'Backpacks'],
            ['Slim Laptop Sleeve 15.6"', 690, 25, false, 'Laptop Bags'],
            ['Natural Jute Tote Bag', 180, 100, false, 'Tote Bags'],
            ['Canvas Cotton Tote Bag', 150, 100, false, 'Tote Bags'],
            ['Leather Messenger Laptop Bag', 2200, 10, false, 'Laptop Bags'],
            ['Travel Duffel Bag', 1250, 20, false, 'Backpacks'],
        ]],
        ['name' => 'Pens', 'code' => 'PN', 'children' => ['Metal Pens', 'Pen Sets'], 'products' => [
            ['Metal Ballpoint Pen', 95, 100, false, 'Metal Pens'],
            ['Stylus Twist Pen', 120, 100, false, 'Metal Pens'],
            ['Premium Pen & Keyring Set', 540, 25, true, 'Pen Sets'],
            ['Fountain Pen Gift Box', 890, 25, false, 'Pen Sets'],
            ['Seed Paper Eco Pen', 35, 250, false],
        ]],
        ['name' => 'Keychains', 'code' => 'KC', 'children' => [], 'products' => [
            ['Metal Bottle Opener Keychain', 85, 100, false],
            ['Leather Loop Keychain', 140, 100, false],
            ['Wooden Engraved Keychain', 75, 100, false],
        ]],
        ['name' => 'Electronics', 'code' => 'EL', 'children' => ['Power Banks', 'Speakers'], 'products' => [
            ['10000 mAh Wireless Power Bank', 1650, 10, true, 'Power Banks'],
            ['Mini Bluetooth Speaker', 1150, 10, false, 'Speakers'],
            ['Bamboo Wireless Charger', 890, 20, false],
            ['Wireless Earbuds', 1790, 10, true],
            ['Smart Fitness Band', 1990, 10, false],
            ['Custom Logo Pen Drive 32GB', 450, 25, false],
        ]],
        ['name' => 'Card Holders', 'code' => 'CH', 'children' => [], 'products' => [
            ['Aluminium Business Card Holder', 190, 50, false],
            ['RFID Leather Card Wallet', 360, 50, false],
            ['Leather Passport Holder', 480, 25, false],
        ]],
        ['name' => 'Drinkware', 'code' => 'DW', 'children' => ['Bottles', 'Mugs'], 'products' => [
            ['Insulated Steel Bottle 750ml', 480, 25, true, 'Bottles'],
            ['Ceramic Coffee Mug', 160, 50, false, 'Mugs'],
            ['Temperature Display Bottle', 620, 25, false, 'Bottles'],
            ['Glass Bottle with Bamboo Lid', 340, 50, false, 'Bottles'],
            ['Travel Coffee Tumbler', 520, 25, false, 'Mugs'],
            ['Magic Colour-Changing Mug', 260, 50, false, 'Mugs'],
        ]],
        ['name' => 'Apparel', 'code' => 'AP', 'children' => ['T-Shirts', 'Caps'], 'products' => [
            ['Cotton Polo T-Shirt', 380, 50, false, 'T-Shirts'],
            ['Embroidered Baseball Cap', 220, 50, false, 'Caps'],
            ['Round Neck Event T-Shirt', 260, 100, false, 'T-Shirts'],
            ['Fleece Hoodie', 890, 25, true],
        ]],
        ['name' => 'Stationery', 'code' => 'ST', 'children' => [], 'products' => [
            ['Desk Organiser Set', 750, 25, false],
            ['Custom Sticky Note Pad', 90, 100, false],
            ['Wooden Desk Calendar', 280, 50, false],
            ['Printed Mouse Pad', 150, 50, false],
        ]],
        ['name' => 'Awards & Trophies', 'code' => 'AW', 'children' => [], 'products' => [
            ['Crystal Star Award', 1350, 5, false],
            ['Wooden Plaque with Metal Plate', 850, 5, false],
            ['Gold Metal Trophy Cup', 1150, 5, false],
            ['Engraved Achievement Medal', 180, 25, false],
        ]],
        ['name' => 'Printing Services', 'code' => 'PS', 'children' => ['Visiting Cards', 'Brochures', 'Stickers'], 'products' => [
            ['Premium Matte Visiting Cards', 2.5, 500, false, 'Visiting Cards'],
            ['Tri-Fold A4 Brochure', 12, 250, false, 'Brochures'],
            ['Die-Cut Vinyl Stickers', 6, 500, false, 'Stickers'],
            ['Roll-Up Standee Banner', 1600, 1, false],
            ['Custom Printed Paper Bags', 28, 250, false],
        ]],
    ];

    /**
     * Seed the sample catalogue.
     */
    public function run(): void
    {
        foreach ($this->catalog as $position => $row) {
            $category = Category::query()->updateOrCreate(['slug' => Str::slug($row['name'])], [
                'name' => $row['name'],
                'description' => "Customised {$row['name']} for corporate gifting, events and promotions.",
                'display_order' => $position + 1,
                'is_active' => true,
                'show_in_menu' => true,
            ]);

            $this->attachCategoryImage($category);

            $children = collect($row['children'])->mapWithKeys(fn (string $name, int $childPosition) => [
                $name => Category::query()->updateOrCreate(['slug' => Str::slug($name)], [
                    'parent_id' => $category->id,
                    'name' => $name,
                    'display_order' => $childPosition + 1,
                    'is_active' => true,
                    'show_in_menu' => true,
                ]),
            ]);

            foreach ($row['products'] as $index => [$name, $price, $minimumQty, $isFeatured]) {
                $product = Product::query()->updateOrCreate(['sku' => sprintf('VPG-%s-%03d', $row['code'], $index + 1)], [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "<p>{$name} – ideal for corporate gifting, employee engagement and events. Available with your logo through printing, engraving or embroidery.</p>",
                    'price' => $price,
                    'minimum_qty' => $minimumQty,
                    'is_active' => true,
                    'is_featured' => $isFeatured,
                    'display_order' => $index + 1,
                ]);

                $subCategory = $children->get($row['products'][$index][4] ?? '');

                $product->categories()->sync(array_filter([$category->id, $subCategory?->id]));

                $this->attachProductImages($product);
            }
        }
    }

    /**
     * Copy the bundled sample photo onto the public disk, unless the category already has an image.
     */
    private function attachCategoryImage(Category $category): void
    {
        $source = database_path("seeders/images/categories/{$category->slug}-1.jpg");

        if (filled($category->image) || ! File::exists($source)) {
            return;
        }

        $path = "categories/{$category->slug}.jpg";
        Storage::disk('public')->put($path, File::get($source));

        $category->update(['image' => $path]);
    }

    /**
     * Copy the bundled sample photos onto the public disk, unless the product already has images.
     */
    private function attachProductImages(Product $product): void
    {
        if ($product->images()->exists()) {
            return;
        }

        foreach (File::glob(database_path("seeders/images/products/{$product->slug}-*.jpg")) as $position => $source) {
            $path = 'products/'.basename($source);
            Storage::disk('public')->put($path, File::get($source));

            $product->images()->create([
                'path' => $path,
                'alt' => $product->name,
                'sort_order' => $position,
            ]);
        }
    }
}
