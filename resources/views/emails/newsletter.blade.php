<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? '' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7;">
        <tr>
            <td align="center" style="padding: 24px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color: #1a1a2e; padding: 32px 40px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 2px;">
                                MH Studio
                            </h1>
                            <p style="margin: 4px 0 0; font-size: 12px; color: #a0a0b0; letter-spacing: 1px;">
                                Balance &bull; Precision &bull; Innovation
                            </p>
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td style="background-color: #ffffff; padding: 40px;">
                            <div style="font-size: 16px; line-height: 1.7; color: #333333;">
                                {!! $content !!}
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #fafafa; padding: 24px 40px; border-top: 1px solid #eeeeee; border-radius: 0 0 8px 8px;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #999999; text-align: center;">
                                &copy; {{ date('Y') }} MH Studio. All rights reserved.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #bbbbbb; text-align: center;">
                                您收到此郵件是因為您訂閱了我們的電子報。<br>
                                <a href="{{ $unsubscribeUrl }}" style="color: #999999; text-decoration: underline;">取消訂閱</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
