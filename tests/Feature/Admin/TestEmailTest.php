<?php

use App\Mail\TestEmail;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

test('guests cannot send a test email', function () {
    Mail::fake();

    $this->post(route('admin.settings.test-email.store'))->assertRedirect(route('admin.login'));

    Mail::assertNothingSent();
});

test('sends a test email to the saved enquiry address and shows the result in Settings', function () {
    Mail::fake();
    config(['mail.default' => 'smtp']);
    SiteSetting::put('enquiry_email', 'sales@velo.example');
    $message = "Test email sent to sales@velo.example. If it hasn't arrived in a few minutes, check the spam folder.";

    $this->actingAs(User::factory()->create())
        ->post(route('admin.settings.test-email.store'))
        ->assertRedirect(route('admin.settings.edit').'#enquiry-emails')
        ->assertSessionHas('test_email', ['sent' => true, 'message' => $message]);

    Mail::assertSent(TestEmail::class, fn (TestEmail $mail) => $mail->hasTo('sales@velo.example'));

    $this->get(route('admin.settings.edit'))->assertSee($message);
});

test('shows the mail server error when the test email is refused', function () {
    config(['mail.default' => 'smtp']);
    SiteSetting::put('enquiry_email', 'sales@velo.example');
    Mail::shouldReceive('to->send')->andThrow(new TransportException('Failed to authenticate on SMTP server.'));

    $this->actingAs(User::factory()->create())
        ->post(route('admin.settings.test-email.store'))
        ->assertSessionHas('test_email', [
            'sent' => false,
            'message' => 'The test email could not be sent. The mail server said: Failed to authenticate on SMTP server.',
        ]);
});

test('explains that email is not set up while the server only logs emails', function () {
    Mail::fake();
    config(['mail.default' => 'log']);
    SiteSetting::put('enquiry_email', 'sales@velo.example');

    $this->actingAs(User::factory()->create())
        ->post(route('admin.settings.test-email.store'))
        ->assertSessionHas('test_email.sent', false)
        ->assertSessionHas('test_email.message', fn (string $message) => str_starts_with($message, 'Email is not set up on the server yet'));

    Mail::assertNothingSent();
});

test('asks for an enquiry address before sending a test email', function () {
    Mail::fake();
    config(['mail.default' => 'smtp']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.settings.test-email.store'))
        ->assertSessionHas('test_email', ['sent' => false, 'message' => 'Save a “Send enquiries to” address first.']);

    Mail::assertNothingSent();
});

test('the test email uses the enquiry sender', function () {
    config(['mail.from.address' => 'no-reply@velo.example']);
    SiteSetting::put('enquiry_from_name', 'Velo Website');

    (new TestEmail)
        ->assertFrom('no-reply@velo.example', 'Velo Website')
        ->assertHasSubject('Test email from your website')
        ->assertSeeInHtml('Your website can send email')
        ->assertSeeInText('This test was sent from no-reply@velo.example.');
});
