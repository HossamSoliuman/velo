<?php

use App\Models\Category;
use App\Models\SiteSetting;

test('seeding refreshes navigation and settings cached before the data existed', function () {
    expect(Category::navigation())->toBeEmpty()
        ->and(SiteSetting::value('site_name'))->toBeNull();

    $this->seed();

    expect(Category::navigation())->not->toBeEmpty()
        ->and(SiteSetting::value('site_name'))->toBe('Velo Printing & Gifting');

    $this->get('/')
        ->assertOk()
        ->assertSee('Gift Sets')
        ->assertSee('+91 00000 00000');
});
