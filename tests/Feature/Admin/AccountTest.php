<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('the account page shows the admin details', function () {
    $this->actingAs(User::factory()->create(['email' => 'owner@velo.test']))
        ->get(route('admin.account.edit'))
        ->assertOk()
        ->assertSee('owner@velo.test');
});

test('updates the name and email without changing the password', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.account.update'), ['name' => 'Priya Shah', 'email' => 'priya@velo.test'])
        ->assertRedirect(route('admin.account.edit'))
        ->assertSessionHas('status', 'Your account has been updated.');

    expect($admin->fresh())
        ->name->toBe('Priya Shah')
        ->email->toBe('priya@velo.test')
        ->and(Hash::check('password', $admin->fresh()->password))->toBeTrue();
});

test('changes the password when the current password is correct', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)->put(route('admin.account.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'current_password' => 'password',
        'password' => 'n3w-Secret-pass',
        'password_confirmation' => 'n3w-Secret-pass',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('n3w-Secret-pass', $admin->fresh()->password))->toBeTrue();
});

test('does not change the password without the correct current password', function (?string $currentPassword) {
    $admin = User::factory()->create();

    $this->actingAs($admin)->put(route('admin.account.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'current_password' => $currentPassword,
        'password' => 'n3w-Secret-pass',
        'password_confirmation' => 'n3w-Secret-pass',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $admin->fresh()->password))->toBeTrue();
})->with([
    'missing' => null,
    'wrong' => 'not-my-password',
]);

test('rejects an email used by another admin', function () {
    User::factory()->create(['email' => 'taken@velo.test']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.account.update'), ['name' => 'Admin', 'email' => 'taken@velo.test'])
        ->assertSessionHasErrors(['email' => 'The email has already been taken.']);
});
