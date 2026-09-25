<?php

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

test('the email contains the customer, product and requirement details with a link to the admin panel', function () {
    $product = Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001']);
    Carbon::setTestNow('2026-09-26 09:15:00');

    $enquiry = Enquiry::factory()->forProduct($product)->create([
        'name' => 'Priya Sharma',
        'company' => 'Acme Industries',
        'email' => 'priya@acme.example',
        'mobile' => '+91 98765 43210',
        'quantity' => 1500,
        'message' => 'Need these with our logo & tagline.',
    ]);

    $mail = new EnquiryReceived($enquiry);

    $mail->assertHasSubject('New enquiry: Executive Gift Set (VPG-GS-001)');

    foreach (['Priya Sharma', 'Acme Industries', 'priya@acme.example', '+91 98765 43210', 'Executive Gift Set', 'VPG-GS-001',
        route('products.show', $product), '1,500', 'Need these with our logo &amp; tagline.', '26 Sep 2026, 2:45 PM IST', route('admin.enquiries.show', $enquiry)] as $text) {
        $mail->assertSeeInHtml($text, false);
    }

    $mail->assertSeeInText('Need these with our logo & tagline.', false)
        ->assertSeeInText('Product page: '.route('products.show', $product), false)
        ->assertSeeInText('View in the admin panel: '.route('admin.enquiries.show', $enquiry), false);
});

test('a general enquiry email names the customer in the subject', function () {
    $enquiry = Enquiry::factory()->create(['name' => 'Rahul Verma']);

    (new EnquiryReceived($enquiry))
        ->assertHasSubject('New enquiry from Rahul Verma')
        ->assertSeeInHtml('a general enquiry')
        ->assertDontSeeInHtml('SKU');
});

test('replies go to the customer unless a reply-to address is set', function () {
    $enquiry = Enquiry::factory()->create(['name' => 'Priya Sharma', 'email' => 'priya@acme.example']);

    (new EnquiryReceived($enquiry))
        ->assertHasReplyTo('priya@acme.example', 'Priya Sharma')
        ->assertSeeInHtml('Reply to this email to answer them directly.');

    SiteSetting::put('enquiry_reply_to', 'sales-team@velo.example');

    (new EnquiryReceived($enquiry))
        ->assertHasReplyTo('sales-team@velo.example')
        ->assertDontSeeInHtml('Reply to this email');
});

test('the email is sent from the server mail address under the configured sender name', function () {
    config(['mail.from.address' => 'no-reply@velo.example']);
    SiteSetting::put('site_name', 'Velo Printing & Gifting');
    $enquiry = Enquiry::factory()->create();

    (new EnquiryReceived($enquiry))->assertFrom('no-reply@velo.example', 'Velo Printing & Gifting');

    SiteSetting::put('enquiry_from_name', 'Velo Website');

    (new EnquiryReceived($enquiry))->assertFrom('no-reply@velo.example', 'Velo Website');
});
