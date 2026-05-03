<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>{{ $quote->quote_number }}</title>
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

        /* Quote info row (number + dates) */
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

        /* Section title (with left accent) */
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

        /* Summary table */
        .summary-table { width: 55%; margin-left: 45%; margin-bottom: 22px; }
        .summary-table td { padding: 7px 14px; font-size: 12px; }
        .summary-table tr td:first-child { color: #6c757d; text-align: right; }
        .summary-table tr td:last-child { text-align: right; width: 130px; color: #2c3e50; }
        .summary-table .total-row td { font-weight: bold; font-size: 15px; color: #fff; background: #2c3e50; border-top: 2px solid #2c3e50; padding-top: 10px; padding-bottom: 10px; }
        .summary-table .total-row td:first-child { color: #fff; }

        /* Clauses */
        .clause-section { margin-bottom: 14px; }
        .clause-section h3 { font-size: 12.5px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; letter-spacing: 0.5px; }
        .clause-section p, .clause-section li { font-size: 11px; line-height: 1.85; color: #495057; }
        .clause-section ul { padding-left: 18px; }

        /* Payment info */
        .payment-info { background-color: #f8f9fa; padding: 12px 16px; margin: 18px 0; border-left: 3px solid #2c3e50; }
        .payment-info h3 { font-size: 13px; color: #2c3e50; margin-bottom: 8px; letter-spacing: 0.5px; }
        .payment-info p { font-size: 11px; margin-bottom: 4px; line-height: 1.6; }

        /* Notes */
        .notes-section { margin-top: 18px; padding: 12px 16px; background: #fafafa; border: 1px solid #e9ecef; }
        .notes-section h3 { font-size: 12px; color: #2c3e50; margin-bottom: 8px; letter-spacing: 0.5px; }
        .notes-section ol { padding-left: 20px; }
        .notes-section li { font-size: 10.5px; color: #6c757d; line-height: 1.9; }

        /* Signature */
        .signature-section { margin-top: 32px; page-break-inside: avoid; }
        .signature-row { width: 100%; margin-top: 20px; }
        .signature-col { display: table-cell; width: 48%; vertical-align: top; padding: 0 6px; }
        .signature-col.right { padding-left: 4%; }
        .signature-block { padding: 12px 14px; border: 1px solid #e9ecef; background: #fafbfc; min-height: 130px; }
        .signature-block .party-label { font-size: 11px; color: #6c757d; letter-spacing: 1px; margin-bottom: 6px; }
        .signature-block .party-name { font-size: 13px; font-weight: bold; color: #2c3e50; margin-bottom: 4px; }
        .signature-block .party-info { font-size: 10px; color: #6c757d; line-height: 1.7; margin-bottom: 12px; }
        .signature-line { border-bottom: 1px solid #2c3e50; height: 28px; margin-top: 8px; }
        .signature-line-label { font-size: 10px; color: #6c757d; margin-top: 3px; }

        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 9.5px; color: #adb5bd; }
    </style>
</head>
<body>
    {{-- 報價單 標題 --}}
    <div class="doc-header">
        <div class="doc-title">報 價 單</div>
        <div class="doc-subtitle">Q U O T A T I O N</div>
        @if(!empty($quote->title))
        <div class="doc-name">{{ $quote->title }}</div>
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

    {{-- 報價編號 / 日期 --}}
    <table class="info-row">
        <tr>
            <td class="info-cell">
                <strong>客戶 / </strong>{{ $quote->client->name }}
                @if($quote->client->company)
                <br><strong>公司 / </strong>{{ $quote->client->company }}
                @endif
                @if($quote->client->tax_id)
                <br><strong>統編 / </strong>{{ $quote->client->tax_id }}
                @endif
            </td>
            <td class="info-cell right">
                <strong>報價編號 / </strong>{{ $quote->quote_number }}<br>
                <strong>報價日期 / </strong>{{ $quote->created_at->format('Y-m-d') }}
                @if($quote->valid_until)
                <br><strong>有效期限 / </strong>{{ $quote->valid_until->format('Y-m-d') }}
                @endif
            </td>
        </tr>
    </table>

    {{-- 客戶/合約元資料 --}}
    <table class="meta-table">
        <tr>
            <td class="label">客戶名稱</td>
            <td class="value">{{ $quote->client->name }}</td>
            <td class="label">簽約日期</td>
            <td class="value">{{ $quote->created_at->format('Y-m-d') }}</td>
        </tr>
        @if($quote->client->company || $quote->client->tax_id)
        <tr>
            <td class="label">公司</td>
            <td class="value">{{ $quote->client->company ?: '-' }}</td>
            <td class="label">統一編號</td>
            <td class="value">{{ $quote->client->tax_id ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">幣別</td>
            <td class="value" colspan="3">{{ $quote->currency ?? 'TWD' }}</td>
        </tr>
        @endif
        @if($quote->project)
        <tr>
            <td class="label">專案名稱</td>
            <td class="value" colspan="3">{{ $quote->project->title }}</td>
        </tr>
        @endif
    </table>

    {{-- 報價項目明細 --}}
    <div class="section-title">項目明細</div>
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-no">#</th>
                <th class="col-desc">服務項目 / 內容</th>
                <th class="col-qty text-center">數量</th>
                <th class="col-unit text-center">單位</th>
                <th class="col-price text-right">單價</th>
                <th class="col-amount text-right">小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $i => $item)
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
            <td style="text-align: right; width: 120px;">NT$ {{ number_format($quote->subtotal) }}</td>
        </tr>
        @if($quote->discount > 0)
        <tr>
            <td style="text-align: right;">折扣</td>
            <td style="text-align: right; color: #c62828;">- NT$ {{ number_format($quote->discount) }}</td>
        </tr>
        @endif
        @if($quote->tax_rate > 0)
        <tr>
            <td style="text-align: right;">營業稅 ({{ $quote->tax_rate }}%)</td>
            <td style="text-align: right;">NT$ {{ number_format($quote->tax_amount) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td style="text-align: right;">金額合計</td>
            <td style="text-align: right;">NT$ {{ number_format($quote->total) }}</td>
        </tr>
    </table>

    {{-- 標的物總價 --}}
    <div class="clause-section">
        <h3>一、標的物總價</h3>
        <p>本報價單所列服務項目，總金額為新台幣 {{ number_format($quote->total) }} 元整（{{ $quote->tax_rate > 0 ? '含稅' : '未稅' }}）。</p>
    </div>

    {{-- 驗收方式 --}}
    <div class="clause-section">
        <h3>二、驗收方式</h3>
        <ul>
            <li>乙方完成製作後，應通知甲方於 7 個工作日內進行驗收。</li>
            <li>驗收期間如有 Bug 或功能異常，乙方應於 7 日內修正完畢。</li>
            <li>網站上線後 7 日內，乙方應提供甲方後台操作之教育訓練。</li>
        </ul>
    </div>

    {{-- 付款辦法 --}}
    <div class="clause-section">
        <h3>三、付款辦法</h3>
        <ul>
            <li>簽約時：支付總額之 50% 作為訂金，計新台幣 {{ number_format(round($quote->total * 0.5)) }} 元整。</li>
            <li>尾款：驗收完成後 7 日內，支付剩餘 50% 尾款。</li>
        </ul>
    </div>

    {{-- 技術支援 --}}
    <div class="clause-section">
        <h3>四、技術支援</h3>
        <p>網站驗收完成後 7 日內，乙方提供免費修改服務。超過免費修改期間之修改需求，依雙方另行議定之費用計算。</p>
    </div>

    {{-- 規格變更 --}}
    <div class="clause-section">
        <h3>五、規格變更</h3>
        <p>甲方修改次數依合約方案規定，超出修改次數之需求，乙方得另行報價。重大規格變更（如新增頁面、功能模組等），雙方應另行議定費用與時程。</p>
    </div>

    {{-- 保固維護 --}}
    <div class="clause-section">
        <h3>六、保固維護</h3>
        <p>乙方提供免費保固服務。保固範圍包含：Bug 修復、安全性更新、瀏覽器相容性問題。保固不含：甲方自行修改程式碼導致之問題、第三方外掛衝突、主機商問題。</p>
    </div>

    {{-- 保密約定 --}}
    <div class="clause-section">
        <h3>七、保密約定</h3>
        <p>雙方對於合作過程中知悉之對方商業機密，負有保密義務。未經對方書面同意，不得將保密資訊揭露予第三方。</p>
    </div>

    {{-- 其他 --}}
    <div class="clause-section">
        <h3>八、其他</h3>
        <p>甲方全額付清後，網站設計之智慧財產權歸甲方所有，乙方保留作品集展示權利。</p>
    </div>

    {{-- 備註 --}}
    <div class="notes-section">
        <h3>備註</h3>
        <ol>
            <li>以上報價有效期為 30 天。</li>
            <li>網站設計製作不含文案撰寫，如需文案服務請另行報價。</li>
            <li>網站圖片如需購買圖庫素材，費用由甲方負擔。</li>
            <li>網域名稱註冊費用不包含在本報價中。</li>
            <li>如需多語系版本，依語系數量另行報價。</li>
            <li>本報價未含營業稅，如需開立發票另加 5% 營業稅。</li>
        </ol>
    </div>

    {{-- 匯款資訊 --}}
    @if(setting('bank_name') || setting('bank_account'))
    <div class="payment-info">
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

    {{-- 報價備註 --}}
    @if($quote->notes)
    <div class="clause-section" style="margin-top: 15px;">
        <h3>附加備註</h3>
        <p style="white-space: pre-line;">{{ $quote->notes }}</p>
    </div>
    @endif

    {{-- 雙方簽名區 --}}
    <div class="signature-section">
        <div class="section-title">確認簽署</div>
        <table class="signature-row" style="width:100%;">
            <tr>
                <td class="signature-col" style="width:48%;">
                    <div class="signature-block">
                        <div class="party-label">甲方（委託方）</div>
                        <div class="party-name">{{ $quote->client->name }}</div>
                        @if($quote->client->company)
                        <div class="party-info">{{ $quote->client->company }}</div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-line-label">簽名 / 蓋章 ＋ 日期</div>
                    </div>
                </td>
                <td style="width:4%;"></td>
                <td class="signature-col" style="width:48%;">
                    <div class="signature-block">
                        <div class="party-label">乙方（受託方）</div>
                        <div class="party-name">{{ setting('company_owner', '') ?: setting('company_name', 'MH Studio') }}</div>
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
        {{ $quote->quote_number }} | {{ setting('company_name', 'MH Studio') }} | Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
