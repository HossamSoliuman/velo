<?php

use App\Models\Category;
use App\Models\User;

test('guests are redirected to the login page from every admin page', function (string $uri) {
    $this->get($uri)->assertRedirect(route('admin.login'));
})->with([
    'dashboard' => '/admin',
    'categories' => '/admin/categories',
    'new category' => '/admin/categories/create',
    'products' => '/admin/products',
    'new product' => '/admin/products/create',
    'pages' => '/admin/pages',
    'settings' => '/admin/settings',
    'e-catalog' => '/admin/e-catalog',
    'account' => '/admin/account',
]);

test('guests cannot change data through admin actions', function () {
    $category = Category::factory()->create(['name' => 'Pens']);

    $this->post(route('admin.categories.store'), ['name' => 'Hacked'])->assertRedirect(route('admin.login'));
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('admin.login'));

    expect(Category::query()->pluck('name')->all())->toBe(['Pens']);
});

test('the login page renders', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSee('Admin login')
        ->assertSee('Forgot password?');
});

test('an admin can log in and is sent to the dashboard', function () {
    $admin = User::factory()->create(['email' => 'owner@velo.test']);

    $this->post(route('admin.login.store'), ['email' => 'owner@velo.test', 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('login is rejected with a wrong password', function () {
    User::factory()->create(['email' => 'owner@velo.test']);

    $this->from(route('admin.login'))
        ->post(route('admin.login.store'), ['email' => 'owner@velo.test', 'password' => 'wrong-password'])
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

    $this->assertGuest();
});

test('a deactivated admin cannot log in even with the right password', function () {
    User::factory()->inactive()->create(['email' => 'former@velo.test']);

    $this->post(route('admin.login.store'), ['email' => 'former@velo.test', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

    $this->assertGuest();
});

test('login is locked after five failed attempts', function () {
    User::factory()->create(['email' => 'owner@velo.test']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('admin.login.store'), ['email' => 'owner@velo.test', 'password' => 'wrong-password']);
    }

    $this->post(route('admin.login.store'), ['email' => 'owner@velo.test', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toStartWith('Too many login attempts.');
    $this->assertGuest();
});

test('an admin deactivated while logged in is signed out on the next request', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $admin->update(['is_active' => false]);

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['email' => 'Your account does not have access to the admin panel.']);

    $this->assertGuest();
});

test('an admin can log out', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});

test('a logged-in admin visiting the login page is sent to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.login'))
        ->assertRedirect(route('admin.dashboard'));
});
