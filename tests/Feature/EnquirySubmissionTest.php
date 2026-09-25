<?php

use App\Enums\EnquiryStatus;
use App\Http\Controllers\EnquiryController;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Mail;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function enquiryPayload(array $overrides = []): array
{
    return [
        'name' => 'Priya Sharma',
        'company' => 'Acme Industries',
        'email' => 'priya@acme.example',
        'mobile' => '+91 98765 43210',
        'quantity' => '',
        'message' => 'Need these with our logo by 15 October.',
        'fax' => '',
        ...$overrides,
    ];
}

beforeEach(function () {
    Mail::fake();
    SiteSetting::put('enquiry_email', 'sales@velo.example');
});

test('a product enquiry is saved with the product details and emailed to the enquiry address', function () {
    $product = Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001', 'minimum_qty' => 25]);

    $this->post(route('enquiries.store'), enquiryPayload(['product_id' => $product->id, 'quantity' => '100']))
        ->assertRedirect(route('contact').'#enquiry')
        ->assertSessionHas('enquiry_sent');

    $enquiry = Enquiry::query()->sole();

    expect($enquiry)
        ->product_id->toBe($product->id)
        ->product_name->toBe('Executive Gift Set')
        ->sku->toBe('VPG-GS-001')
        ->product_url->toBe(route('products.show', $product))
        ->name->toBe('Priya Sharma')
        ->company->toBe('Acme Industries')
        ->email->toBe('priya@acme.example')
        ->mobile->toBe('+91 98765 43210')
        ->quantity->toBe(100)
        ->message->toBe('Need these with our logo by 15 October.')
        ->status->toBe(EnquiryStatus::New)
        ->read_at->toBeNull();

    Mail::assertQueued(EnquiryReceived::class, fn (EnquiryReceived $mail) => $mail->hasTo('sales@velo.example') && $mail->enquiry->is($enquiry));
});

test('the contact page sends a general enquiry without a product or quantity', function () {
    $this->post(route('enquiries.store'), enquiryPayload(['company' => '', 'message' => '']))
        ->assertSessionHasNoErrors();

    expect(Enquiry::query()->sole())
        ->product_id->toBeNull()
        ->product_name->toBeNull()
        ->quantity->toBeNull()
        ->company->toBeNull()
        ->message->toBeNull();

    Mail::assertQueued(EnquiryReceived::class);
});

test('after sending, the contact page thanks the visitor instead of showing the form again', function () {
    $this->followingRedirects()
        ->post(route('enquiries.store'), enquiryPayload())
        ->assertOk()
        ->assertSee('Enquiry sent')
        ->assertSee(EnquiryController::SENT_MESSAGE);
});

test('the enquiry modal gets a JSON response', function () {
    $product = Product::factory()->create(['minimum_qty' => 10]);

    $this->postJson(route('enquiries.store'), enquiryPayload(['product_id' => $product->id, 'quantity' => '10']))
        ->assertCreated()
        ->assertJson(['message' => EnquiryController::SENT_MESSAGE]);

    expect(Enquiry::query()->sole()->product_id)->toBe($product->id);
});

test('invalid enquiries are rejected with a message for each field', function (array $overrides, string $field) {
    $this->from(route('contact'))
        ->post(route('enquiries.store'), enquiryPayload($overrides))
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors($field);

    expect(Enquiry::query()->count())->toBe(0);
    Mail::assertNothingQueued();
})->with([
    'missing name' => [['name' => ''], 'name'],
    'missing email' => [['email' => ''], 'email'],
    'invalid email' => [['email' => 'priya'], 'email'],
    'missing mobile' => [['mobile' => ''], 'mobile'],
    'mobile with letters' => [['mobile' => 'call me'], 'mobile'],
    'mobile that is too short' => [['mobile' => '12345'], 'mobile'],
    'quantity of zero' => [['quantity' => '0'], 'quantity'],
    'quantity that is not a number' => [['quantity' => 'lots'], 'quantity'],
    'message that is too long' => [['message' => str_repeat('a', 2001)], 'message'],
]);

test('the modal receives validation errors as JSON', function () {
    $this->postJson(route('enquiries.store'), enquiryPayload(['name' => '', 'mobile' => 'abc']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'mobile' => 'Please enter a valid mobile number.']);
});

test('a product enquiry needs a quantity of at least the product minimum', function (string $quantity, string $message) {
    $product = Product::factory()->create(['minimum_qty' => 25]);

    $this->postJson(route('enquiries.store'), enquiryPayload(['product_id' => $product->id, 'quantity' => $quantity]))
        ->assertJsonValidationErrors(['quantity' => $message]);
})->with([
    'missing' => ['', 'Please enter the quantity you need.'],
    'below the minimum' => ['24', 'The minimum order for this product is 25.'],
]);

test('an enquiry cannot be attached to a hidden or unknown product', function (Closure $productId) {
    $this->postJson(route('enquiries.store'), enquiryPayload(['product_id' => $productId(), 'quantity' => '100']))
        ->assertJsonValidationErrors('product_id');

    expect(Enquiry::query()->count())->toBe(0);
})->with([
    'hidden product' => fn () => fn () => Product::factory()->inactive()->create()->id,
    'unknown product' => fn () => fn () => 999,
]);

test('submissions that fill in the hidden spam trap look successful but are discarded', function () {
    $this->postJson(route('enquiries.store'), enquiryPayload(['fax' => 'https://spam.example']))
        ->assertCreated();

    expect(Enquiry::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

test('an enquiry is still saved when no enquiry email address is set', function () {
    SiteSetting::put('enquiry_email', '');

    $this->post(route('enquiries.store'), enquiryPayload())->assertSessionHasNoErrors();

    expect(Enquiry::query()->count())->toBe(1);
    Mail::assertNothingQueued();
});

test('visitors are limited to a few enquiries a minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('enquiries.store'), enquiryPayload())->assertCreated();
    }

    $this->postJson(route('enquiries.store'), enquiryPayload())
        ->assertTooManyRequests()
        ->assertJsonPath('message', 'You have sent several enquiries in a short time. Please wait a few minutes, or call us instead.');

    $this->from(route('contact'))
        ->post(route('enquiries.store'), enquiryPayload())
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors('enquiry');

    expect(Enquiry::query()->count())->toBe(5);
});

test('the product page opens the enquiry form with the product attached', function () {
    $product = Product::factory()->create(['name' => 'Executive Gift Set', 'minimum_qty' => 25]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('x-on:click.prevent="$dispatch(\'open-enquiry\')"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('Enquire about this product')
        ->assertSee('<input type="hidden" name="product_id" value="'.$product->id.'">', false)
        ->assertSee('action="'.route('enquiries.store').'"', false);
});
