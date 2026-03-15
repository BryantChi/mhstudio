<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>您的報價請求已收到</title>
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
                            <h2 style="margin: 0 0 24px; font-size: 22px; font-weight: 700; color: #1a1a2e; text-align: center;">
                                感謝您的報價請求！
                            </h2>

                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.7; color: #333333;">
                                親愛的 {{ $quoteRequest->name }}，
                            </p>

                            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.7; color: #333333;">
                                我們已收到您的報價請求（編號：<strong style="color: #1a1a2e;">{{ $quoteRequest->request_number }}</strong>）。以下是您的報價摘要：
                            </p>

                            {{-- Service Type --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 12px 16px; background-color: #f0f4ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #666666; text-transform: uppercase; letter-spacing: 0.5px;">服務類型</p>
                                        <p style="margin: 0; font-size: 16px; font-weight: 600; color: #1a1a2e;">{{ $quoteRequest->project_type }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Selected Features --}}
                            @if(!empty($quoteRequest->selected_features))
                                <p style="margin: 0 0 8px; font-size: 14px; font-weight: 600; color: #1a1a2e;">選擇的功能</p>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px; border: 1px solid #eeeeee; border-radius: 6px; overflow: hidden;">
                                    @foreach($quoteRequest->selected_features as $feature)
                                        <tr>
                                            <td style="padding: 10px 16px; font-size: 14px; color: #333333;{{ !$loop->last ? ' border-bottom: 1px solid #eeeeee;' : '' }}">
                                                &bull; {{ $feature['name'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            {{-- Timeline & Budget --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td width="50%" style="padding: 12px 16px; background-color: #f8f9fa; border-radius: 6px 0 0 6px; border: 1px solid #eeeeee; border-right: none;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #666666;">時程</p>
                                        <p style="margin: 0; font-size: 15px; font-weight: 600; color: #333333;">{{ $quoteRequest->timeline ?? '—' }}</p>
                                    </td>
                                    <td width="50%" style="padding: 12px 16px; background-color: #f8f9fa; border-radius: 0 6px 6px 0; border: 1px solid #eeeeee;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #666666;">預算</p>
                                        <p style="margin: 0; font-size: 15px; font-weight: 600; color: #333333;">{{ $quoteRequest->budget ?? '—' }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Estimated Amount --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px; background-color: #1a1a2e; border-radius: 6px; text-align: center;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #a0a0b0; text-transform: uppercase; letter-spacing: 0.5px;">估算金額</p>
                                        <p style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">
                                            NT$ {{ number_format($quoteRequest->estimated_min) }} ~ NT$ {{ number_format($quoteRequest->estimated_max) }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Response Time Notice --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; text-align: center;">
                                        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
                                            我們通常會在 <strong>24 小時內</strong>回覆您的報價請求。
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- CTA Button --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0;">
                                        <a href="{{ $statusUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #3b82f6; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; border-radius: 6px; letter-spacing: 0.5px;">
                                            查詢報價狀態
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #fafafa; padding: 24px 40px; border-top: 1px solid #eeeeee; border-radius: 0 0 8px 8px;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #999999; text-align: center;">
                                此為系統自動發送的郵件，請勿直接回覆。
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #999999; text-align: center;">
                                &copy; {{ date('Y') }} MH Studio. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
