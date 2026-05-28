<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:'Noto Sans TC','Microsoft JhengHei',Arial,sans-serif; color:#2c3e50;">
    <div style="max-width:600px; margin:0 auto; padding:24px;">
        <div style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
            <div style="background:#2c3e50; padding:20px 28px;">
                <div style="color:#ffffff; font-size:18px; font-weight:bold; letter-spacing:1px;">
                    {{ setting('company_name', 'MH Studio') }}
                </div>
            </div>
            <div style="padding:28px;">
                <p style="font-size:15px; line-height:1.8; margin:0 0 16px;">
                    {{ $contract->client->name }} 您好：
                </p>
                <p style="font-size:15px; line-height:1.8; margin:0 0 16px;">
                    附件為您的合約文件「<strong>{{ $contract->title }}</strong>」（編號 {{ $contract->contract_number }}），請查閱。
                </p>
                <p style="font-size:15px; line-height:1.8; margin:0 0 16px;">
                    煩請於確認內容無誤後簽署，並將已簽署的合約（PDF 或掃描檔）回傳給我們，以利後續作業。
                </p>
                <table style="width:100%; border-collapse:collapse; margin:20px 0; font-size:14px;">
                    <tr>
                        <td style="padding:8px 0; color:#6c757d; width:110px;">合約編號</td>
                        <td style="padding:8px 0;">{{ $contract->contract_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#6c757d;">合約金額</td>
                        <td style="padding:8px 0;">{{ $contract->currency }} {{ number_format($contract->total) }}</td>
                    </tr>
                    @if($contract->start_date)
                    <tr>
                        <td style="padding:8px 0; color:#6c757d;">合約期間</td>
                        <td style="padding:8px 0;">
                            {{ $contract->start_date->format('Y-m-d') }}
                            @if($contract->end_date) ~ {{ $contract->end_date->format('Y-m-d') }} @endif
                        </td>
                    </tr>
                    @endif
                </table>
                <p style="font-size:14px; line-height:1.8; color:#6c757d; margin:24px 0 0;">
                    若有任何問題，歡迎直接回信或來電 {{ setting('company_phone', '') }}。<br>
                    感謝您的合作！
                </p>
            </div>
            <div style="background:#f8f9fa; padding:16px 28px; font-size:12px; color:#adb5bd; text-align:center;">
                {{ setting('company_name', 'MH Studio') }}
                @if(setting('company_email')) ｜ {{ setting('company_email') }} @endif
            </div>
        </div>
    </div>
</body>
</html>
