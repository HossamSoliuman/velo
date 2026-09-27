<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestEmail;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class TestEmailController extends Controller
{
    /**
     * Mailers that accept an email without delivering it anywhere.
     */
    private const NON_DELIVERING_MAILERS = ['log', 'array'];

    /**
     * Send a test email to the saved enquiry address and report whether the mail server accepted it.
     */
    public function store(): RedirectResponse
    {
        $recipient = SiteSetting::value('enquiry_email');

        if (blank($recipient)) {
            return $this->result(false, 'Save a “Send enquiries to” address first.');
        }

        if (in_array(config('mail.default'), self::NON_DELIVERING_MAILERS, true)) {
            return $this->result(false, 'Email is not set up on the server yet: emails are written to the log file instead of being sent. Add the SMTP details (MAIL_*) to the .env file.');
        }

        try {
            Mail::to($recipient)->send(new TestEmail);
        } catch (TransportExceptionInterface $exception) {
            return $this->result(false, 'The test email could not be sent. The mail server said: '.$exception->getMessage());
        }

        return $this->result(true, "Test email sent to {$recipient}. If it hasn't arrived in a few minutes, check the spam folder.");
    }

    private function result(bool $sent, string $message): RedirectResponse
    {
        return redirect()->to(route('admin.settings.edit').'#enquiry-emails')
            ->with('test_email', ['sent' => $sent, 'message' => $message]);
    }
}
