<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>{{ $contract->contract_number }}</title>
    <style>
        @page { margin: 18mm; }

        body { margin: 0; padding: 0; }
        h1, h2, h3, h4, p, ul, ol, li { margin: 0; padding: 0; }
        table { margin: 0; padding: 0; border-collapse: collapse; }
        td, th { margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'notosanstc', 'DejaVu Sans', sans-serif; font-size: 12px; color: #2c3e50; line-height: 1.7; }

        /* Header */
        .doc-header { text-align: center; padding-bottom: 14px; margin-bottom: 6px; }
        .doc-title { font-size: 28px; font-weight: bold; color: #2c3e50; letter-spacing: 14px; padding-left: 14px; }
        .doc-subtitle { font-size: 10px; color: #adb5bd; letter-spacing: 4px; margin-top: 4px; }
        .doc-name { text-align: center; font-size: 16px; font-weight: bold; color: #495057; margin: 12px 0 4px; }

        .company-bar { border-top: 2px solid #2c3e50; border-bottom: 1px solid #e9ecef; padding: 10px 0; margin-bottom: 18px; text-align: center; }
        .company-bar .company-name { font-size: 15px; font-weight: bold; color: #2c3e50; letter-spacing: 1px; margin-bottom: 3px; }
        .company-bar .company-info { font-size: 10.5px; color: #6c757d; line-height: 1.7; }

        /* Info row */
        .info-row { width: 100%; margin-bottom: 18px; }
        .info-row td { vertical-align: top; padding: 0; }
        .info-cell { font-size: 11.5px; color: #6c757d; line-height: 1.9; }
        .info-cell strong { color: #2c3e50; font-weight: bold; }
        .info-cell.right { text-align: right; }

        /* Meta table */
        .meta-table { width: 100%; margin-bottom: 22px; background: #fafbfc; border: 1px solid #e9ecef; }
        .meta-table td { padding: 9px 14px; font-size: 11.5px; vertical-align: top; border-bottom: 1px solid #e9ecef; }
        .meta-table tr:last-child td { border-bottom: none; }
        .meta-table .label { font-weight: bold; width: 90px; color: #6c757d; background: #f1f3f5; }
        .meta-table .value { color: #2c3e50; }

        /* Section title */
        .section-title { font-size: 13px; font-weight: bold; color: #2c3e50; padding: 6px 10px; margin: 20px 0 10px 0; letter-spacing: 1.5px; border-left: 4px solid #2c3e50; background: #f8f9fa; }

        /* Items table */
        .items-table { width: 100%; margin-bottom: 16px; table-layout: fixed; }
        .items-table th { background-color: #2c3e50; color: #fff; padding: 10px 8px; text-align: left; font-size: 11px; font-weight: bold; letter-spacing: 0.5px; }
        .items-table th.text-right { text-align: right; }
        .items-table th.text-center { text-align: center; }
        .items-table td { padding: 9px 8px; border-bottom: 1px solid #e9ecef; font-size: 11px; word-wrap: break-word; }
        .items-table tbody tr:nth-child(even) td { background-color: #fafbfc; }
        .items-table td.text-right { text-align: right; }
        .items-table td.text-center { text-align: center; }
        .items-table .col-no { width: 6%; }
        .items-table .col-desc { width: 44%; }
        .items-table .col-qty { width: 9%; }
        .items-table .col-unit { width: 9%; }
        .items-table .col-price { width: 16%; }
        .items-table .col-amount { width: 16%; }

        /* Summary */
        .summary-table { width: 55%; margin-left: 45%; margin-bottom: 22px; }
        .summary-table td { padding: 7px 14px; font-size: 12px; }
        .summary-table tr td:first-child { color: #6c757d; text-align: right; }
        .summary-table tr td:last-child { text-align: right; width: 130px; color: #2c3e50; }
        .summary-table .total-row td { font-weight: bold; font-size: 15px; color: #fff; background: #2c3e50; border-top: 2px solid #2c3e50; padding-top: 10px; padding-bottom: 10px; }
        .summary-table .total-row td:first-child { color: #fff; }

        /* Contract content */
        .content-section { margin-bottom: 24px; }
        .content-section .content-body { white-space: pre-line; font-size: 12px; line-height: 1.95; color: #495057; }

        /* Payment info */
        .payment-section { background-color: #f8f9fa; padding: 12px 16px; margin: 18px 0; border-left: 3px solid #2c3e50; }
        .payment-section h3 { font-size: 13px; color: #2c3e50; margin-bottom: 8px; letter-spacing: 0.5px; }
        .payment-section p { font-size: 11px; margin-bottom: 4px; line-height: 1.6; }

        /* Signature */
        .signature-section { margin-top: 32px; page-break-inside: avoid; }
        .signature-row { width: 100%; margin-top: 20px; }
        .signature-col { display: table-cell; width: 48%; vertical-align: top; padding: 0 6px; }
        .signature-block { padding: 12px 14px; border: 1px solid #e9ecef; background: #fafbfc; min-height: 130px; }
        .signature-block .party-label { font-size: 11px; color: #6c757d; letter-spacing: 1px; margin-bottom: 6px; }
        .signature-block .party-name { font-size: 13px; font-weight: bold; color: #2c3e50; margin-bottom: 4px; }
        .signature-block .party-info { font-size: 10px; color: #6c757d; line-height: 1.7; margin-bottom: 12px; }
        .signature-line { border-bottom: 1px solid #2c3e50; height: 28px; margin-top: 8px; }
        .signature-line-label { font-size: 10px; color: #6c757d; margin-top: 3px; }

        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 9.5px; color: #adb5bd; }

        .badge { display: inline-block; padding: 3px 10px; font-size: 10px; font-weight: bold; letter-spacing: 0.5px; }
        .badge-type { background-color: #e3f2fd; color: #1565c0; }
        .badge-status { background-color: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    {{-- 合約書 標題 --}}
    <div class="doc-header">
        <div class="doc-title">合 約 書</div>
        <div class="doc-subtitle">C O N T R A C T</div>
        @if(!empty($contract->title))
        <div class="doc-name">{{ $contract->title }}</div>
        @endif
    </div>

    {{-- 公司資訊 Bar --}}
    <div class="company-bar">
        <div class="company-name">{{ setting('company_name', 'MH Studio') }}</div>
        <div class="company-info">
            承攬人：{{ setting('company_owner', '') }} ｜ TEL：{{ setting('company_phone', '') }} ｜ Email：{{ setting('company_email', '') }}
            @if(setting('company_website'))
                ｜ {{ setting('company_website') }}
            @endif
        </div>
    </div>

    {{-- 合約編號 / 日期 --}}
    <table class="info-row">
        <tr>
            <td class="info-cell">
                <strong>客戶 / </strong>{{ $contract->client->name }}
                @if($contract->client->company)
                <br><strong>公司 / </strong>{{ $contract->client->company }}
                @endif
                @if($contract->client->tax_id)
                <br><strong>統編 / </strong>{{ $contract->client->tax_id }}
                @endif
            </td>
            <td class="info-cell right">
                <strong>合約編號 / </strong>{{ $contract->contract_number }}<br>
                <strong>建立日期 / </strong>{{ $contract->created_at->format('Y-m-d') }}
            </td>
        </tr>
    </table>

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
                <th class="col-no">#</th>
                <th class="col-desc">項目說明</th>
                <th class="col-qty text-center">數量</th>
                <th class="col-unit text-center">單位</th>
                <th class="col-price text-right">單價</th>
                <th class="col-amount text-right">小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contract->items as $i => $item)
            <tr>
                <td class="col-no">{{ $i + 1 }}</td>
                <td class="col-desc">{{ $item->description }}</td>
                <td class="col-qty text-center">{{ $item->quantity }}</td>
                <td class="col-unit text-center">{{ $item->unit }}</td>
                <td class="col-price text-right">NT$ {{ number_format($item->unit_price) }}</td>
                <td class="col-amount text-right">NT$ {{ number_format($item->amount) }}</td>
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
        <p><strong>戶名：</strong>{{ setting('bank_account_holder') ?: setting('company_name_full', setting('company_name', 'MH Studio')) }}</p>
    </div>
    @endif

    {{-- 雙方簽名區 --}}
    <div class="signature-section">
        <div class="section-title">簽署欄</div>
        <table class="signature-row" style="width:100%;">
            <tr>
                <td class="signature-col" style="width:48%;">
                    <div class="signature-block">
                        <div class="party-label">甲方（委託方）</div>
                        <div class="party-name">{{ $contract->client_signer_name ?: $contract->client->name }}</div>
                        @if($contract->client_signer_title)
                        <div class="party-info">{{ $contract->client_signer_title }}</div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-line-label">簽名 / 蓋章 ＋ 日期</div>
                    </div>
                </td>
                <td style="width:4%;"></td>
                <td class="signature-col" style="width:48%;">
                    <div class="signature-block">
                        <div class="party-label">乙方（受託方）</div>
                        <div class="party-name">{{ $contract->company_signer_name ?: setting('company_owner', '') ?: setting('company_name', 'MH Studio') }}</div>
                        <div class="party-info">
                            {{ setting('company_name', 'MH Studio') }}<br>
                            @if(setting('company_address')){{ setting('company_address') }}<br>@endif
                            TEL: {{ setting('company_phone', '') }}
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-line-label">簽名 / 蓋章 ＋ 日期</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 頁尾 --}}
    <div class="footer">
        {{ $contract->contract_number }} | {{ setting('company_name', 'MH Studio') }} | Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
