<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('lists the content pages', function () {
    Page::factory()->create(['title' => 'About Us', 'slug' => 'about-us']);
    Page::factory()->create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.pages.index'))
        ->assertOk()
        ->assertSeeInOrder(['About Us', 'Privacy Policy']);
});

test('the edit form shows the page content', function () {
    $page = Page::factory()->create(['title' => 'About Us', 'slug' => 'about-us', 'content' => '<div>Founded in Mumbai.</div>']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.pages.edit', $page))
        ->assertOk()
        ->assertSee('Founded in Mumbai.');
});

test('saves the title, sanitised content and SEO fields', function () {
    Storage::fake('public');
    $page = Page::factory()->create(['slug' => 'about-us']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.pages.update', $page), [
            'title' => 'About Velo',
            'content' => '<h1>Our story</h1><div>Since 2010.<iframe src="https://evil.test"></iframe></div>',
            'meta_title' => 'About Velo Printing & Gifting',
            'meta_description' => 'Who we are.',
            'robots' => 'noindex,follow',
            'og_image' => UploadedFile::fake()->image('share.jpg', 1200, 630),
        ])
        ->assertRedirect(route('admin.pages.edit', 'about-us'))
        ->assertSessionHas('status', '“About Velo” saved.');

    expect($page->fresh())
        ->title->toBe('About Velo')
        ->content->toBe('<h1>Our story</h1><div>Since 2010.</div>')
        ->meta_title->toBe('About Velo Printing & Gifting')
        ->robots->toBe('noindex,follow');

    Storage::disk('public')->assertExists($page->fresh()->og_image);
});

test('rejects an unknown robots value', function () {
    $page = Page::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.pages.update', $page), ['title' => 'About', 'robots' => 'index,follow,archive'])
        ->assertSessionHasErrors(['robots' => 'The selected robots is invalid.']);
});
