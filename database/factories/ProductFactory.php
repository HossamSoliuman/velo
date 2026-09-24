<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => 'VPG-'.fake()->unique()->bothify('??-####'),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 5000),
            'minimum_qty' => fake()->randomElement([10, 25, 50, 100]),
            'is_active' => true,
            'is_featured' => false,
            'display_order' => 0,
        ];
    }

    /**
     * Indicate that the product is hidden from the public site.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is featured on the homepage.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
