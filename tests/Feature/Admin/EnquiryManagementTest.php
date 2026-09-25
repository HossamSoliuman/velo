<?php

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\User;

test('guests cannot see enquiries', function () {
    $enquiry = Enquiry::factory()->create();

    $this->get(route('admin.enquiries.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.enquiries.show', $enquiry))->assertRedirect(route('admin.login'));
});

describe('index', function () {
    test('lists enquiries newest first with date, customer, product, quantity and status', function () {
        $product = Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001']);
        Enquiry::factory()->create(['name' => 'Older Customer', 'created_at' => '2026-09-20 06:00:00']);
        Enquiry::factory()->forProduct($product)->status(EnquiryStatus::Quoted)->create([
            'name' => 'Priya Sharma',
            'quantity' => 1500,
            'created_at' => '2026-09-25 20:00:00',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSeeInOrder(['Priya Sharma', 'Executive Gift Set', 'VPG-GS-001', '1,500', 'Quoted', 'Older Customer', 'General enquiry'])
            ->assertSee('26 Sep 2026')
            ->assertSee('1:30 AM');
    });

    test('finds enquiries by customer name, email, mobile, product or SKU', function (string $search) {
        Enquiry::factory()->forProduct(Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001']))
            ->create(['name' => 'Priya Sharma', 'email' => 'priya@acme.example', 'mobile' => '+91 98765 43210']);
        Enquiry::factory()->forProduct(Product::factory()->create(['name' => 'Metal Pen', 'sku' => 'VPG-PN-001']))
            ->create(['name' => 'Rahul Verma', 'email' => 'rahul@globex.example', 'mobile' => '+91 91234 56789']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.index', ['search' => $search]))
            ->assertOk()
            ->assertSee('Priya Sharma')
            ->assertDontSee('Rahul Verma');
    })->with([
        'name' => 'priya',
        'email' => 'acme.example',
        'mobile' => '98765',
        'product' => 'gift set',
        'SKU' => 'GS-001',
    ]);

    test('filters by status and read state', function () {
        Enquiry::factory()->status(EnquiryStatus::Contacted)->create(['name' => 'Unread Contacted']);
        Enquiry::factory()->status(EnquiryStatus::Contacted)->read()->create(['name' => 'Read Contacted']);
        Enquiry::factory()->create(['name' => 'Unread New']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.index', ['status' => 'contacted', 'unread' => 1]))
            ->assertOk()
            ->assertSee('Unread Contacted')
            ->assertDontSee('Read Contacted')
            ->assertDontSee('Unread New')
            ->assertSee('Contacted (2)');
    });

    test('filters by the day received in the business time zone', function () {
        // 20:00 UTC on 25 September is 1:30 AM on 26 September in India.
        Enquiry::factory()->create(['name' => 'Late Night', 'created_at' => '2026-09-25 20:00:00']);
        Enquiry::factory()->create(['name' => 'Same Evening', 'created_at' => '2026-09-25 18:00:00']);
        Enquiry::factory()->create(['name' => 'Next Week', 'created_at' => '2026-10-02 06:00:00']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.index', ['from' => '2026-09-26', 'to' => '2026-09-30']))
            ->assertOk()
            ->assertSee('Late Night')
            ->assertDontSee('Same Evening')
            ->assertDontSee('Next Week');
    });

    test('rejects invalid filters', function (array $filters, string $field) {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.index', $filters))
            ->assertSessionHasErrors($field);
    })->with([
        'unknown status' => [['status' => 'pending'], 'status'],
        'end before start' => [['from' => '2026-09-26', 'to' => '2026-09-01'], 'to'],
        'malformed date' => [['from' => '26/09/2026'], 'from'],
    ]);

    test('shows the number of unread enquiries in the sidebar', function () {
        Enquiry::factory()->count(3)->create();
        Enquiry::factory()->read()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('3<span class="sr-only"> unread</span>', false);
    });
});

describe('show', function () {
    test('shows the full enquiry and marks it as read', function () {
        $product = Product::factory()->create(['name' => 'Executive Gift Set', 'sku' => 'VPG-GS-001']);
        $enquiry = Enquiry::factory()->forProduct($product)->create([
            'name' => 'Priya Sharma',
            'company' => 'Acme Industries',
            'email' => 'priya@acme.example',
            'mobile' => '+91 98765 43210',
            'quantity' => 1500,
            'message' => 'Need these with our logo.',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee(['Priya Sharma', 'Acme Industries', 'mailto:priya@acme.example', 'tel:+919876543210', 'https://wa.me/919876543210'], false)
            ->assertSee(['Executive Gift Set', 'SKU VPG-GS-001', '1,500', 'Need these with our logo.'])
            ->assertSee(route('admin.products.edit', $product))
            ->assertSee(route('products.show', $product));

        expect($enquiry->fresh()->isUnread())->toBeFalse();
    });

    test('still reads correctly after the product is deleted', function () {
        $product = Product::factory()->create(['name' => 'Executive Gift Set']);
        $enquiry = Enquiry::factory()->forProduct($product)->create();
        $product->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('Executive Gift Set')
            ->assertSee('This product has since been deleted.');
    });
});

test('updates the follow-up status', function () {
    $enquiry = Enquiry::factory()->create(['name' => 'Priya Sharma']);

    $this->actingAs(User::factory()->create())
        ->from(route('admin.enquiries.show', $enquiry))
        ->patch(route('admin.enquiries.update', $enquiry), ['status' => 'converted'])
        ->assertRedirect(route('admin.enquiries.show', $enquiry))
        ->assertSessionHas('status', 'Enquiry from Priya Sharma marked as Converted.');

    expect($enquiry->fresh()->status)->toBe(EnquiryStatus::Converted);
});

test('rejects an unknown status', function () {
    $enquiry = Enquiry::factory()->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.enquiries.update', $enquiry), ['status' => 'pending'])
        ->assertSessionHasErrors('status');

    expect($enquiry->fresh()->status)->toBe(EnquiryStatus::New);
});

test('marks an enquiry as unread again', function () {
    $enquiry = Enquiry::factory()->read()->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.enquiries.read.update', $enquiry), ['read' => '0'])
        ->assertRedirect(route('admin.enquiries.index'));

    expect($enquiry->fresh()->isUnread())->toBeTrue();
});

test('deletes an enquiry', function () {
    $enquiry = Enquiry::factory()->create(['name' => 'Spam Bot']);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.enquiries.destroy', $enquiry))
        ->assertRedirect(route('admin.enquiries.index'))
        ->assertSessionHas('status', 'Enquiry from Spam Bot deleted.');

    expect(Enquiry::query()->count())->toBe(0);
});
