<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>{{ $contract->contract_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'notosanstc', 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.6; }

        .header { text-align: center; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #2c3e50; margin-bottom: 5px; }
        .header .company-name { font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 3px; }
        .header .company-info { font-size: 10px; color: #666; }

        .contract-number { text-align: right; font-size: 12px; color: #666; margin-bottom: 20px; }
        .contract-number strong { color: #333; }

        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 5px 10px; font-size: 11px; vertical-align: top; }
        .meta-table .label { font-weight: bold; width: 100px; color: #555; }
        .meta-table .value { color: #333; }

        .section-title { font-size: 14px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin: 20px 0 10px 0; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background-color: #2c3e50; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; }
        .items-table th.text-right { text-align: right; }
        .items-table th.text-center { text-align: center; }
        .items-table td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
        .items-table td.text-right { text-align: right; }
        .items-table td.text-center { text-align: center; }
        .items-table tfoot td { border-top: 2px solid #ddd; font-size: 11px; }
        .items-table tfoot .total-row td { font-weight: bold; font-size: 12px; border-top: 2px solid #2c3e50; }

        .summary-table { width: 50%; margin-left: auto; margin-bottom: 20px; border-collapse: collapse; }
        .summary-table td { padding: 4px 10px; font-size: 11px; }
        .summary-table .total-row td { font-weight: bold; font-size: 13px; border-top: 2px solid #2c3e50; padding-top: 8px; }

        .content-section { margin-bottom: 20px; }
        .content-section .content-body { white-space: pre-line; font-size: 11px; line-height: 1.8; }

        .payment-section { background-color: #f8f9fa; padding: 10px 15px; margin-bottom: 20px; border-radius: 4px; }
        .payment-section h3 { font-size: 12px; color: #2c3e50; margin-bottom: 5px; }
        .payment-section p { font-size: 10px; margin-bottom: 3px; }

        .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .signature-row { display: table; width: 100%; margin-top: 30px; }
        .signature-col { display: table-cell; width: 48%; vertical-align: top; }
        .signature-col.right { text-align: right; padding-left: 4%; }
        .signature-line { border-bottom: 1px solid #333; width: 200px; margin: 30px 0 5px 0; display: inline-block; }
        .signature-label { font-size: 10px; color: #666; }
        .signature-name { font-size: 11px; font-weight: bold; margin-top: 5px; }

        .footer { position: fixed; bottom: 20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-type { background-color: #e3f2fd; color: #1565c0; }
        .badge-status { background-color: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    {{-- 公司 Header --}}
    <div class="header">
        <div class="company-name">{{ setting('company_name', 'MH Studio') }}</div>
        <div class="company-info">
            Email: {{ setting('company_email', 'contact@mhstudio.tw') }} | Web: {{ setting('company_website', 'mhstudio.tw') }}
        </div>
        <h1>合 約 書</h1>
    </div>

    {{-- 合約編號 --}}
    <div class="contract-number">
        <strong>合約編號：</strong>{{ $contract->contract_number }}
        <br>
        <strong>建立日期：</strong>{{ $contract->created_at->format('Y-m-d') }}
    </div>

    {{-- 合約元資料 --}}
    <table class="meta-table">
        <tr>
            <td class="label">客戶</td>
            <td class="value">{{ $contract->client->name }}</td>
            <td class="label">合約類型</td>
            <td class="value"><span class="badge badge-type">{{ $contract->type_label }}</span></td>
        </tr>
        <tr>
            <td class="label">合約期間</td>
            <td class="value">
                {{ $contract->start_date?->format('Y-m-d') ?? '-' }}
                @if($contract->end_date) ~ {{ $contract->end_date->format('Y-m-d') }} @endif
            </td>
            <td class="label">狀態</td>
            <td class="value"><span class="badge badge-status">{{ $contract->status_label }}</span></td>
        </tr>
        @if($contract->client_signer_name || $contract->company_signer_name)
        <tr>
            <td class="label">甲方代表</td>
            <td class="value">{{ $contract->client_signer_name ?? '-' }} {{ $contract->client_signer_title ? '(' . $contract->client_signer_title . ')' : '' }}</td>
            <td class="label">乙方代表</td>
            <td class="value">{{ $contract->company_signer_name ?? '-' }}</td>
        </tr>
        @endif
        @if($contract->project)
        <tr>
            <td class="label">關聯專案</td>
            <td class="value" colspan="3">{{ $contract->project->title }}</td>
        </tr>
        @endif
    </table>

    {{-- 合約項目明細 --}}
    @if($contract->items->isNotEmpty())
    <div class="section-title">合約項目明細</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="30">#</th>
                <th>項目說明</th>
                <th class="text-center" width="60">數量</th>
                <th class="text-center" width="50">單位</th>
                <th class="text-right" width="100">單價</th>
                <th class="text-right" width="100">小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contract->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ $item->unit }}</td>
                <td class="text-right">NT$ {{ number_format($item->unit_price) }}</td>
                <td class="text-right">NT$ {{ number_format($item->amount) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 金額匯總 --}}
    <table class="summary-table">
        <tr>
            <td style="text-align: right;">小計</td>
            <td style="text-align: right; width: 120px;">NT$ {{ number_format($contract->subtotal) }}</td>
        </tr>
        @if($contract->discount > 0)
        <tr>
            <td style="text-align: right;">折扣</td>
            <td style="text-align: right; color: #c62828;">- NT$ {{ number_format($contract->discount) }}</td>
        </tr>
        @endif
        <tr>
            <td style="text-align: right;">稅額 ({{ $contract->tax_rate }}%)</td>
            <td style="text-align: right;">NT$ {{ number_format($contract->tax_amount) }}</td>
        </tr>
        <tr class="total-row">
            <td style="text-align: right;">總計</td>
            <td style="text-align: right;">NT$ {{ number_format($contract->total) }}</td>
        </tr>
    </table>
    @endif

    {{-- 合約正文 --}}
    <div class="section-title">合約條款</div>
    <div class="content-section">
        <div class="content-body">{!! $contract->content !!}</div>
    </div>

    {{-- 付款條件 --}}
    <div class="payment-section">
        <h3>付款條件</h3>
        <p><strong>付款方式：</strong>{{ $contract->payment_terms_label }}</p>
        @if($contract->payment_method)
        <p><strong>付款管道：</strong>{{ $contract->payment_method }}</p>
        @endif
        @if($contract->due_date)
        <p><strong>付款到期日：</strong>{{ $contract->due_date->format('Y-m-d') }}</p>
        @endif
        @if($contract->total > 0)
        <p><strong>合約總金額：</strong>{{ $contract->currency }} {{ number_format($contract->total) }}</p>
        @endif
    </div>

    {{-- 匯款資訊 --}}
    @if(setting('bank_name') || setting('bank_account'))
    <div class="payment-section">
        <h3>匯款資訊</h3>
        @if(setting('bank_name'))
        <p><strong>銀行：</strong>{{ setting('bank_name') }}（{{ setting('bank_code') }}）{{ setting('bank_branch') ? '— ' . setting('bank_branch') : '' }}</p>
        @endif
        @if(setting('bank_account'))
        <p><strong>帳號：</strong>{{ setting('bank_account') }}</p>
        @endif
        <p><strong>戶名：</strong>{{ setting('company_name_full', setting('company_name', 'MH Studio')) }}</p>
    </div>
    @endif

    {{-- 雙方簽名區 --}}
    <div class="signature-section">
        <div class="section-title">簽署欄</div>
        <div class="signature-row">
            <div class="signature-col">
                <p><strong>甲方（委託方）</strong></p>
                @if($contract->client_signer_name)
                <div class="signature-name">{{ $contract->client_signer_name }}</div>
                @if($contract->client_signer_title)
                <div class="signature-label">{{ $contract->client_signer_title }}</div>
                @endif
                @endif
                <div class="signature-line"></div>
                <div class="signature-label">簽名 / 蓋章</div>
                <br>
                <div class="signature-line"></div>
                <div class="signature-label">日期</div>
            </div>
            <div class="signature-col right">
                <p><strong>乙方（受託方）</strong></p>
                @if($contract->company_signer_name)
                <div class="signature-name">{{ $contract->company_signer_name }}</div>
                @else
                <div class="signature-name">{{ setting('company_owner', '') }}</div>
                @endif
                <div class="signature-label">{{ setting('company_name', 'MH Studio') }}</div>
                <div class="signature-label">{{ setting('company_address', '') }}</div>
                <div class="signature-label">TEL: {{ setting('company_phone', '') }}</div>
                <div class="signature-line"></div>
                <div class="signature-label">簽名 / 蓋章</div>
                <br>
                <div class="signature-line"></div>
                <div class="signature-label">日期</div>
            </div>
        </div>
    </div>

    {{-- 頁尾 --}}
    <div class="footer">
        {{ $contract->contract_number }} | {{ setting('company_name', 'MH Studio') }} | Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
