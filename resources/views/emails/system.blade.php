<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e9ef;">
                    <tr>
                        <td style="background:#0f2a4a;padding:18px 28px;text-align:center;">
                            <a href="{{ config('app.url') }}" style="color:#ffffff;font-size:20px;font-weight:bold;text-decoration:none;">
                                DeliveringParcel
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;color:#1f2937;font-size:15px;line-height:1.65;">
                            {!! $body !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;border-top:1px solid #e6e9ef;color:#6b7280;font-size:12px;text-align:center;">
                            &copy; {{ date('Y') }} DeliveringParcel — International parcel forwarding &amp; shipping.<br>
                            <a href="{{ config('app.url') }}" style="color:#2563eb;">{{ config('app.url') }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
