<x-mail.layout title="Reset your password" footer="If you did not ask to reset your password, you can ignore this email. Your password will not change.">
    <h1 style="margin:0;color:#22467a;font-size:22px;font-weight:800;">Reset your password</h1>
    <p style="margin:8px 0 0;color:#475569;font-size:14px;line-height:1.5;">
        Hi {{ $name }}, we received a request to reset the password for your admin panel account. Use the button below to choose a new one.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:24px;">
        <tr>
            <td style="border-radius:999px;background:#e6197d;">
                <a href="{{ $url }}" style="display:inline-block;padding:12px 26px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">Choose a new password</a>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0;color:#64748b;font-size:13px;line-height:1.5;">
        This link expires in {{ $expiresInMinutes }} minutes and works once. If the button does not work, copy this address into your browser:<br>
        <a href="{{ $url }}" style="color:#2f62a0;word-break:break-all;">{{ $url }}</a>
    </p>
</x-mail.layout>
