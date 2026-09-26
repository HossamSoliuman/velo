<?php

namespace Database\Seeders;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoEnquirySeeder extends Seeder
{
    /**
     * Sample enquiries spread over the last few weeks, so the admin inbox and dashboard have something to show.
     */
    public function run(): void
    {
        if (Enquiry::query()->where('email', 'like', '%@example.%')->exists()) {
            return;
        }

        $products = Product::query()->active()->get();

        foreach (range(1, 30) as $index) {
            $receivedAt = now()->subDays(fake()->numberBetween(0, 45))->subMinutes(fake()->numberBetween(0, 1440));
            $status = $index <= 8 ? EnquiryStatus::New : fake()->randomElement(EnquiryStatus::cases());

            $enquiry = Enquiry::factory()->make([
                'company' => fake()->boolean(80) ? fake()->company() : null,
                'message' => fake()->randomElement([
                    'Please share a quote with our logo printed. We need delivery within two weeks.',
                    'Looking for this for our annual sales conference. Can you do custom packaging?',
                    'Need samples before placing a bulk order. What are the branding options?',
                    'Diwali gifting for our clients – please send pricing for different quantities.',
                    'Is engraving available? Also, what is the lead time for 200 units?',
                    'We are onboarding new employees next month and want welcome kits.',
                    null,
                ]),
                'status' => $status,
                'read_at' => $status === EnquiryStatus::New && $index <= 5 ? null : $receivedAt->copy()->addHours(2),
            ]);

            if ($products->isNotEmpty() && fake()->boolean(75)) {
                $product = $products->random();
                $enquiry->attachProduct($product);
                $enquiry->quantity = fake()->randomElement([1, 2, 4, 10]) * $product->minimum_qty;
            }

            $enquiry->created_at = $receivedAt;
            $enquiry->updated_at = $receivedAt;
            $enquiry->save();
        }
    }
}
