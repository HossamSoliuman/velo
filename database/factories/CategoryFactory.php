<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'display_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'show_in_menu' => true,
        ];
    }

    /**
     * Indicate that the category is hidden from the public site.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the category is excluded from the navigation menu.
     */
    public function hiddenFromMenu(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_menu' => false,
        ]);
    }
}
