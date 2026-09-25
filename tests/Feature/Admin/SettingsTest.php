<?php

use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function settingsPayload(array $overrides = []): array
{
    return [
        'site_name' => 'Velo Printing & Gifting',
        'tagline' => 'Gifts that carry your brand.',
        'hero_title' => 'Branded gifts for every occasion',
        'hero_highlight' => 'Delivered.',
        'hero_subtitle' => 'Bulk orders with your logo.',
        'phone' => '+91 98765 43210',
        'whatsapp' => '',
        'email' => 'hello@velo.example',
        'enquiry_email' => 'sales@velo.example',
        'address' => 'Mumbai',
        'business_hours' => 'Mon – Sat',
        'map_embed_url' => '',
        'facebook_url' => '',
        'instagram_url' => 'https://instagram.com/velo',
        'linkedin_url' => '',
        'currency_symbol' => '₹',
        'show_prices' => '1',
        'nav_category_limit' => '6',
        'price_ranges' => [],
        ...$overrides,
    ];
}

test('the settings form shows the current values', function () {
    SiteSetting::put('phone', '+91 11111 22222');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('+91 11111 22222');
});

test('saves the settings and the website uses them', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload())
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('status', 'Settings saved.');

    expect(SiteSetting::value('enquiry_email'))->toBe('sales@velo.example')
        ->and(SiteSetting::value('nav_category_limit'))->toBe('6')
        ->and(SiteSetting::value('whatsapp'))->toBeNull();

    $this->get('/')
        ->assertSee('+91 98765 43210')
        ->assertSee('Branded gifts for every occasion')
        ->assertSee('Delivered.')
        ->assertSee('Bulk orders with your logo.');
});

test('saves the home page promotion wording', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload([
            'promo_title' => 'Festive gifting, sorted',
            'promo_text' => 'Hampers for every team.',
        ]));

    expect(SiteSetting::value('promo_title'))->toBe('Festive gifting, sorted')
        ->and(SiteSetting::value('promo_text'))->toBe('Hampers for every team.');

    $this->get('/')->assertSee('Festive gifting, sorted')->assertSee('Hampers for every team.');
});

test('a blank promotion heading falls back to the default wording', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload(['promo_title' => '']));

    $this->get('/')->assertSee('Corporate gifting, handled end to end');
});

test('turning prices off shows price on request on the website', function () {
    Product::factory()->featured()->create(['price' => 1250]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload(['show_prices' => '0']));

    expect(SiteSetting::value('show_prices'))->toBe('0');
    $this->get('/')->assertSee('Price on request')->assertDontSee('₹1,250');
});

test('saves price ranges sorted by minimum and skips empty rows', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload(['price_ranges' => [
            ['min' => '1000', 'max' => ''],
            ['min' => '', 'max' => ''],
            ['min' => '0', 'max' => '500'],
            ['min' => '500', 'max' => '1000'],
        ]]))
        ->assertSessionHasNoErrors();

    expect(json_decode(SiteSetting::value('price_ranges'), true))->toBe([
        ['min' => 0, 'max' => 500],
        ['min' => 500, 'max' => 1000],
        ['min' => 1000, 'max' => null],
    ]);

    $this->get('/')->assertSee('Under ₹500')->assertSee('Above ₹1,000');
});

test('rejects a price range whose maximum is not above its minimum', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload(['price_ranges' => [['min' => '500', 'max' => '200']]]))
        ->assertSessionHasErrors(['price_ranges.0.max' => 'The maximum price field must be greater than 500.']);
});

test('rejects invalid contact settings', function (array $overrides, string $field) {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'missing enquiry email' => [['enquiry_email' => ''], 'enquiry_email'],
    'invalid enquiry email' => [['enquiry_email' => 'sales'], 'enquiry_email'],
    'map that is not https' => [['map_embed_url' => 'http://maps.example/embed'], 'map_embed_url'],
    'social link that is not a URL' => [['facebook_url' => 'velo-page'], 'facebook_url'],
    'menu limit of zero' => [['nav_category_limit' => '0'], 'nav_category_limit'],
]);

test('saves the enquiry email sender name and reply-to address', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload([
            'enquiry_from_name' => 'Velo Website',
            'enquiry_reply_to' => 'sales-team@velo.example',
        ]))
        ->assertSessionHasNoErrors();

    expect(SiteSetting::value('enquiry_from_name'))->toBe('Velo Website')
        ->and(SiteSetting::value('enquiry_reply_to'))->toBe('sales-team@velo.example');
});

test('rejects an invalid enquiry reply-to address', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('admin.settings.update'), settingsPayload(['enquiry_reply_to' => 'sales']))
        ->assertSessionHasErrors('enquiry_reply_to');
});
