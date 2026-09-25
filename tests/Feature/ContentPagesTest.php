<?php

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('content pages show the wording edited in the admin panel', function (string $routeName, string $slug) {
    Page::factory()->create([
        'slug' => $slug,
        'title' => 'Page Heading',
        'content' => '<p>Approved wording with <strong>emphasis</strong>.</p>',
    ]);

    $this->get(route($routeName))
        ->assertSee('Page Heading')
        ->assertSee('Approved wording with <strong>emphasis</strong>.', false);
})->with([
    'about us' => ['about', 'about-us'],
    'privacy policy' => ['privacy', 'privacy-policy'],
    'terms and conditions' => ['terms', 'terms-and-conditions'],
]);

test('policy pages show when they were last updated', function () {
    $this->travelTo('2026-03-15 10:00:00');
    Page::factory()->create(['slug' => 'privacy-policy']);
    $this->travelBack();

    $this->get(route('privacy'))->assertSee('Last updated 15 March 2026');
});

test('a content page that has not been created is not found', function () {
    $this->get(route('about'))->assertNotFound();
});

test('unknown addresses show the branded not found page', function () {
    $this->get('/no-such-page')
        ->assertNotFound()
        ->assertSee("We couldn't find that page", false)
        ->assertSee(route('categories.index'));
});

test('the e-catalog page offers the uploaded catalogue for viewing and download', function () {
    Storage::fake('public');
    Storage::disk('public')->put('catalog/catalogue.pdf', str_repeat('a', 1536 * 1024));
    SiteSetting::put('e_catalog_path', 'catalog/catalogue.pdf');
    SiteSetting::put('e_catalog_updated_at', '2026-09-01T09:00:00+00:00');

    $this->get(route('e-catalog'))
        ->assertSee(route('e-catalog.download'))
        ->assertSee(Storage::disk('public')->url('catalog/catalogue.pdf'))
        ->assertSee('1.5 MB')
        ->assertSee('Updated 1 Sep 2026');
});

test('the e-catalog downloads under a readable file name', function () {
    Storage::fake('public');
    SiteSetting::put('site_name', 'Velo Printing & Gifting');
    SiteSetting::put('e_catalog_path', UploadedFile::fake()->create('x7Qa.pdf', 10, 'application/pdf')->store('catalog', 'public'));

    $this->get(route('e-catalog.download'))
        ->assertDownload('velo-printing-gifting-e-catalog.pdf');
});

test('the e-catalog page explains when no catalogue has been uploaded', function (?string $path) {
    Storage::fake('public');
    SiteSetting::put('e_catalog_path', $path);

    $this->get(route('e-catalog'))
        ->assertSee('Our new catalogue is on its way')
        ->assertDontSee(route('e-catalog.download'));

    $this->get(route('e-catalog.download'))->assertNotFound();
})->with([
    'nothing uploaded' => [null],
    'file missing from storage' => ['catalog/deleted.pdf'],
]);
