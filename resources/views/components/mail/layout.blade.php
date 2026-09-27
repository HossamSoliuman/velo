@props(['title', 'footer'])

{{-- The branded shell shared by every HTML email: a blue header, the logo-fan stripe, the body and a grey footer. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#eef4fb;font-family:Montserrat,Segoe UI,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#3b75ba;padding:20px 28px;color:#ffffff;font-size:18px;font-weight:800;">{{ \App\Models\SiteSetting::value('site_name', config('app.name')) }}</td>
                    </tr>
                    <tr>
                        <td style="height:4px;background:linear-gradient(90deg,#4b2a7b,#7b2d8e,#e6197d,#0b6db7,#1ba1e2,#5bc5f2,#c5d82f,#ffe600);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:12px;">{{ $footer }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
