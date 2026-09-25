<?php

namespace App\Mail;

use App\Models\Enquiry;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the business about a new website enquiry. Sent in the background by the queue worker.
 */
class EnquiryReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300, 900, 3600];

    /**
     * Skip the email if the enquiry is deleted before it is sent.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Enquiry $enquiry)
    {
        $this->afterCommit();
    }

    /**
     * Sent from the server's mail address under the configured sender name. Replies go to the
     * configured reply-to address, or straight to the customer when none is set.
     */
    public function envelope(): Envelope
    {
        $replyTo = SiteSetting::value('enquiry_reply_to');

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                SiteSetting::value('enquiry_from_name') ?: SiteSetting::value('site_name', config('mail.from.name')),
            ),
            replyTo: [filled($replyTo) ? new Address($replyTo) : new Address($this->enquiry->email, $this->enquiry->name)],
            subject: $this->enquiry->product_name !== null
                ? "New enquiry: {$this->enquiry->product_name} ({$this->enquiry->sku})"
                : "New enquiry from {$this->enquiry->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.enquiries.received',
            text: 'mail.enquiries.received-text',
            with: ['repliesGoToCustomer' => blank(SiteSetting::value('enquiry_reply_to'))],
        );
    }
}
