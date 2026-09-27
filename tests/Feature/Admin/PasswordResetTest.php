<?php

use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\ResetAdminPassword;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

const NEUTRAL_RESET_MESSAGE = 'If an account exists for that email address, we have sent a password reset link to it.';

test('the forgot password page renders', function () {
    $this->get(route('admin.password.request'))
        ->assertOk()
        ->assertSee('Email reset link');
});

test('an active admin is emailed a reset link that opens the admin reset page', function () {
    Notification::fake();
    $admin = User::factory()->create(['email' => 'owner@velo.test']);

    $this->post(route('admin.password.email'), ['email' => 'owner@velo.test'])
        ->assertSessionHas('status', NEUTRAL_RESET_MESSAGE);

    Notification::assertSentTo($admin, ResetAdminPassword::class, function (ResetAdminPassword $notification) use ($admin) {
        $url = $notification->toMail($admin)->viewData['url'];

        return str_starts_with($url, route('admin.password.reset', $notification->token));
    });
});

test('the reset email is queued, so the page never waits for the mail server', function () {
    Queue::fake([SendQueuedNotifications::class]);
    User::factory()->create(['email' => 'owner@velo.test']);

    $this->post(route('admin.password.email'), ['email' => 'owner@velo.test'])
        ->assertSessionHas('status', NEUTRAL_RESET_MESSAGE);

    Queue::assertPushed(SendQueuedNotifications::class, fn (SendQueuedNotifications $job) => $job->notification instanceof ResetAdminPassword);
});

test('the reset email is branded, greets the admin and says when the link expires', function () {
    config(['mail.from.address' => 'no-reply@velo.example']);
    SiteSetting::put('site_name', 'Velo Printing & Gifting');
    $admin = User::factory()->create(['name' => 'Asha Rao', 'email' => 'owner@velo.test']);

    $mail = (new ResetAdminPassword('reset-token'))->toMail($admin);

    expect($mail->subject)->toBe('Reset your Velo Printing & Gifting admin password')
        ->and($mail->from)->toBe(['no-reply@velo.example', 'Velo Printing & Gifting'])
        ->and((string) $mail->render())->toContain(
            'Hi Asha Rao',
            route('admin.password.reset', ['token' => 'reset-token', 'email' => 'owner@velo.test']),
            'This link expires in 60 minutes',
        );
});

test('the response does not reveal whether an email address has an account', function (string $email) {
    Notification::fake();
    User::factory()->inactive()->create(['email' => 'former@velo.test']);

    $this->post(route('admin.password.email'), ['email' => $email])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', NEUTRAL_RESET_MESSAGE);

    Notification::assertNothingSent();
})->with([
    'unknown email' => 'nobody@velo.test',
    'deactivated admin' => 'former@velo.test',
]);

test('reset link requests are rate limited', function () {
    Notification::fake();

    foreach (range(1, 3) as $request) {
        $this->post(route('admin.password.email'), ['email' => 'nobody@velo.test'])->assertSessionHasNoErrors();
    }

    $this->post(route('admin.password.email'), ['email' => 'nobody@velo.test'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toStartWith('Too many reset requests.');
});

test('an admin can set a new password with the emailed token, and the token works only once', function () {
    Notification::fake();
    $admin = User::factory()->create(['email' => 'owner@velo.test']);
    $this->post(route('admin.password.email'), ['email' => 'owner@velo.test']);

    $token = null;
    Notification::assertSentTo($admin, ResetAdminPassword::class, function (ResetAdminPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->get(route('admin.password.reset', ['token' => $token, 'email' => 'owner@velo.test']))
        ->assertOk()
        ->assertSee('Choose a new password');

    $payload = [
        'token' => $token,
        'email' => 'owner@velo.test',
        'password' => 'n3w-Secret-pass',
        'password_confirmation' => 'n3w-Secret-pass',
    ];

    $this->post(route('admin.password.store'), $payload)
        ->assertRedirect(route('admin.login'))
        ->assertSessionHas('status', 'Your password has been reset. You can now log in.');

    expect(Hash::check('n3w-Secret-pass', $admin->fresh()->password))->toBeTrue();

    $this->post(route('admin.password.store'), [...$payload, 'password' => 'an0ther-pass', 'password_confirmation' => 'an0ther-pass'])
        ->assertSessionHasErrors(['email' => 'This password reset link is invalid or has expired. Please request a new one.']);

    expect(Hash::check('n3w-Secret-pass', $admin->fresh()->password))->toBeTrue();
});

test('a weak new password is rejected', function () {
    User::factory()->create(['email' => 'owner@velo.test']);

    $this->post(route('admin.password.store'), [
        'token' => 'any-token',
        'email' => 'owner@velo.test',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');
});
