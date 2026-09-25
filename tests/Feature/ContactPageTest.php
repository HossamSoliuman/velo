<?php

use App\Models\Product;
use App\Models\SiteSetting;

test('the contact page shows the business contact details from settings', function () {
    SiteSetting::put('phone', '+91 98765 43210');
    SiteSetting::put('whatsapp', '+91 91234 56789');
    SiteSetting::put('email', 'hello@velo.example');
    SiteSetting::put('address', '12 Market Road, Pune');
    SiteSetting::put('business_hours', 'Mon – Sat, 10 AM – 7 PM');
    SiteSetting::put('instagram_url', 'https://instagram.com/velo');

    $this->get(route('contact'))
        ->assertSee('tel:+919876543210')
        ->assertSee('https://wa.me/919123456789')
        ->assertSee('mailto:hello@velo.example')
        ->assertSee('12 Market Road, Pune')
        ->assertSee('Mon – Sat, 10 AM – 7 PM')
        ->assertSee('https://instagram.com/velo');
});

test('the contact page embeds the configured map', function () {
    SiteSetting::put('map_embed_url', 'https://www.google.com/maps/embed?pb=abc123');

    $this->get(route('contact'))->assertSee('<iframe src="https://www.google.com/maps/embed?pb=abc123"', false);
});

test('without a map the contact page links to directions for the address', function () {
    SiteSetting::put('map_embed_url', null);
    SiteSetting::put('address', '12 Market Road, Pune');

    $this->get(route('contact'))
        ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=12+Market+Road%2C+Pune', false)
        ->assertDontSee('<iframe', false);
});

test('the enquiry form has the fields from the specification', function () {
    $this->get(route('contact'))
        ->assertSee('Send us an enquiry')
        ->assertSee('name="name"', false)
        ->assertSee('name="company"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="mobile"', false)
        ->assertSee('name="quantity"', false)
        ->assertSee('name="message"', false)
        ->assertDontSee('name="product_id"', false);
});

test('an enquiry about a product attaches the product and suggests its minimum quantity', function () {
    $product = Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001', 'minimum_qty' => 25]);

    $this->get(route('contact', ['product' => $product->slug]))
        ->assertSee('Enquire about this product')
        ->assertSee('Executive Gift Set')
        ->assertSee('SKU VPG-GS-001')
        ->assertSee('<input type="hidden" name="product_id" value="'.$product->id.'">', false)
        ->assertSee('value="25" min="25"', false);
});

test('an unknown or inactive product is not attached to the enquiry', function (string $slug) {
    $this->get(route('contact', ['product' => $slug]))
        ->assertViewHas('product', null)
        ->assertSee('Send us an enquiry');
})->with([
    'unknown product' => fn () => 'no-such-product',
    'inactive product' => fn () => Product::factory()->inactive()->create()->slug,
]);

test('until online enquiries open the form points visitors to phone and email', function () {
    SiteSetting::put('phone', '+91 98765 43210');
    SiteSetting::put('email', 'hello@velo.example');

    $this->get(route('contact'))
        ->assertSee('Online enquiries open shortly.')
        ->assertSee('disabled', false);
});
