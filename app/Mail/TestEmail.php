<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent from Settings to check the mail setup. It uses the same sender as enquiry emails and is
 * sent straight away rather than queued, so the admin sees at once whether it worked.
 */
class TestEmail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            from: EnquiryReceived::sender(),
            subject: 'Test email from your website',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.test',
            text: 'mail.test-text',
            with: ['sentAt' => now()->setTimezone(config('app.display_timezone'))],
        );
    }
}
