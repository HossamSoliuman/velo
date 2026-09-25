@php
    $siteName = \App\Models\SiteSetting::value('site_name', config('app.name'));
    $rows = array_filter([
        'Name' => $enquiry->name,
        'Company' => $enquiry->company,
        'Email' => $enquiry->email,
        'Mobile' => $enquiry->mobile,
        'Product' => $enquiry->product_name,
        'SKU' => $enquiry->sku,
        'Product page' => $enquiry->product_url,
        'Quantity' => $enquiry->quantity !== null ? number_format($enquiry->quantity) : null,
        'Received' => $enquiry->received_at->format('j M Y, g:i A T'),
    ], fn ($value) => filled($value));
    $label = 'padding:10px 16px 10px 0;border-bottom:1px solid #e2e8f0;color:#64748b;font-size:13px;font-weight:600;white-space:nowrap;vertical-align:top;width:120px;';
    $value = 'padding:10px 0;border-bottom:1px solid #e2e8f0;color:#1f2937;font-size:14px;vertical-align:top;word-break:break-word;';
    $link = 'color:#2f62a0;font-weight:600;text-decoration:none;';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New enquiry</title>
</head>
<body style="margin:0;padding:0;background:#eef4fb;font-family:Montserrat,Segoe UI,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#3b75ba;padding:20px 28px;color:#ffffff;font-size:18px;font-weight:800;">{{ $siteName }}</td>
                    </tr>
                    <tr>
                        <td style="height:4px;background:linear-gradient(90deg,#4b2a7b,#7b2d8e,#e6197d,#0b6db7,#1ba1e2,#5bc5f2,#c5d82f,#ffe600);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0;color:#22467a;font-size:22px;font-weight:800;">New website enquiry</h1>
                            <p style="margin:8px 0 20px;color:#475569;font-size:14px;line-height:1.5;">
                                {{ $enquiry->name }}@if ($enquiry->company) from {{ $enquiry->company }}@endif sent
                                {{ $enquiry->product_name !== null ? 'an enquiry about '.$enquiry->product_name : 'a general enquiry' }}.
                                @if ($repliesGoToCustomer)
                                    Reply to this email to answer them directly.
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach ($rows as $heading => $text)
                                    <tr>
                                        <td style="{{ $label }}">{{ $heading }}</td>
                                        <td style="{{ $value }}">
                                            @switch($heading)
                                                @case('Email')
                                                    <a href="mailto:{{ $text }}" style="{{ $link }}">{{ $text }}</a>
                                                    @break
                                                @case('Mobile')
                                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $text) }}" style="{{ $link }}">{{ $text }}</a>
                                                    @break
                                                @case('Product page')
                                                    <a href="{{ $text }}" style="{{ $link }}">{{ $text }}</a>
                                                    @break
                                                @default
                                                    {{ $text }}
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <h2 style="margin:24px 0 8px;color:#22467a;font-size:15px;font-weight:700;">Message</h2>
                            <p style="margin:0;padding:14px 16px;background:#eef4fb;border-radius:10px;color:#1f2937;font-size:14px;line-height:1.6;white-space:pre-line;">{{ filled($enquiry->message) ? $enquiry->message : 'No message.' }}</p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:28px;">
                                <tr>
                                    <td style="border-radius:999px;background:#e6197d;">
                                        <a href="{{ route('admin.enquiries.show', $enquiry) }}" style="display:inline-block;padding:12px 26px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">View in the admin panel</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:12px;">Enquiry #{{ $enquiry->id }} · sent from the {{ $siteName }} website.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
