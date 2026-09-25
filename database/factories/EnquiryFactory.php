<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * Define the model's default state: an unread general enquiry without a product.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->safeEmail(),
            'mobile' => '+91 '.fake()->numerify('9#### #####'),
            'quantity' => fake()->optional()->numberBetween(10, 500),
            'message' => fake()->optional()->sentence(12),
            'status' => EnquiryStatus::New,
            'read_at' => null,
        ];
    }

    /**
     * Indicate that the enquiry is about the given product, or a new one.
     */
    public function forProduct(?Product $product = null): static
    {
        return $this->afterMaking(fn (Enquiry $enquiry) => $enquiry->attachProduct($product ?? Product::factory()->create()))
            ->state(fn (array $attributes) => ['quantity' => $attributes['quantity'] ?? 50]);
    }

    /**
     * Indicate that an admin has opened the enquiry.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    public function status(EnquiryStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
