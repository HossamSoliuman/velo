New website enquiry

Name: {!! $enquiry->name !!}
@if ($enquiry->company)
Company: {!! $enquiry->company !!}
@endif
Email: {!! $enquiry->email !!}
Mobile: {!! $enquiry->mobile !!}
@if ($enquiry->product_name !== null)
Product: {!! $enquiry->product_name !!}
SKU: {!! $enquiry->sku !!}
Product page: {!! $enquiry->product_url !!}
@endif
@if ($enquiry->quantity !== null)
Quantity: {!! number_format($enquiry->quantity) !!}
@endif
Received: {!! $enquiry->received_at->format('j M Y, g:i A T') !!}

Message:
{!! filled($enquiry->message) ? $enquiry->message : 'No message.' !!}

View in the admin panel: {!! route('admin.enquiries.show', $enquiry) !!}

Enquiry #{!! $enquiry->id !!}.@if ($repliesGoToCustomer) Reply to this email to answer the customer directly.@endif
