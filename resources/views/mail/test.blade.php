<x-mail.layout title="Test email" :footer="'Sent '.$sentAt->format('j M Y, g:i A T').' from the Settings page of the admin panel.'">
    <h1 style="margin:0;color:#22467a;font-size:22px;font-weight:800;">Your website can send email</h1>
    <p style="margin:8px 0 0;color:#475569;font-size:14px;line-height:1.5;">
        This test was sent from {{ config('mail.from.address') }}. New enquiries will be emailed to this address in the same way.
    </p>
</x-mail.layout>
