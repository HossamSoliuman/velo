<?php

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('shows that no catalogue has been uploaded yet', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.e-catalog.edit'))
        ->assertOk()
        ->assertSee('No catalogue has been uploaded yet.');
});

test('uploads a catalogue PDF', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->put(route('admin.e-catalog.update'), ['catalog' => UploadedFile::fake()->create('catalogue.pdf', 500, 'application/pdf')])
        ->assertRedirect(route('admin.e-catalog.edit'))
        ->assertSessionHas('status', 'E-catalog uploaded.');

    Storage::disk('public')->assertExists(SiteSetting::value('e_catalog_path'));
    expect(SiteSetting::value('e_catalog_updated_at'))->not->toBeNull();

    $this->get(route('admin.e-catalog.edit'))->assertSee('Open the current e-catalog');
});

test('replacing the catalogue deletes the previous file', function () {
    Storage::fake('public');
    $previous = UploadedFile::fake()->create('old.pdf', 100, 'application/pdf')->store('catalog', 'public');
    SiteSetting::put('e_catalog_path', $previous);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.e-catalog.update'), ['catalog' => UploadedFile::fake()->create('new.pdf', 100, 'application/pdf')]);

    Storage::disk('public')->assertMissing($previous);
    Storage::disk('public')->assertExists(SiteSetting::value('e_catalog_path'));
});

test('rejects files that are not PDFs or are too large', function (UploadedFile $file) {
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->put(route('admin.e-catalog.update'), ['catalog' => $file])
        ->assertSessionHasErrors('catalog');

    expect(SiteSetting::value('e_catalog_path'))->toBeNull();
})->with([
    'image' => fn () => UploadedFile::fake()->image('catalogue.jpg'),
    'over 20 MB' => fn () => UploadedFile::fake()->create('catalogue.pdf', 20481, 'application/pdf'),
]);

test('removes the catalogue', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->create('catalogue.pdf', 100, 'application/pdf')->store('catalog', 'public');
    SiteSetting::put('e_catalog_path', $path);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.e-catalog.destroy'))
        ->assertSessionHas('status', 'E-catalog removed.');

    Storage::disk('public')->assertMissing($path);
    expect(SiteSetting::value('e_catalog_path'))->toBeNull();
});
