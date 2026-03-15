<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>{{ $quote->quote_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.6; }

        .header { text-align: center; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #2c3e50; margin-bottom: 5px; }
        .header .company-name { font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 3px; }
        .header .company-info { font-size: 10px; color: #666; }

        .quote-number { text-align: right; font-size: 12px; color: #666; margin-bottom: 15px; }
        .quote-number strong { color: #333; }

        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 5px 10px; font-size: 11px; vertical-align: top; }
        .meta-table .label { font-weight: bold; width: 100px; color: #555; }
        .meta-table .value { color: #333; }

        .section-title { font-size: 13px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin: 18px 0 8px 0; }

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

        .clause-section { margin-bottom: 12px; }
        .clause-section h3 { font-size: 11px; font-weight: bold; color: #2c3e50; margin-bottom: 4px; }
        .clause-section p, .clause-section li { font-size: 10px; line-height: 1.7; color: #555; }
        .clause-section ul { padding-left: 16px; margin: 0; }

        .payment-info { background-color: #f8f9fa; padding: 10px 15px; margin: 15px 0; border-radius: 4px; border-left: 3px solid #2c3e50; }
        .payment-info h3 { font-size: 12px; color: #2c3e50; margin-bottom: 5px; }
        .payment-info p { font-size: 10px; margin-bottom: 2px; }

        .notes-section { margin-top: 15px; padding: 10px 15px; background: #fafafa; border: 1px solid #eee; }
        .notes-section h3 { font-size: 11px; color: #2c3e50; margin-bottom: 6px; }
        .notes-section ol { padding-left: 16px; margin: 0; }
        .notes-section li { font-size: 9px; color: #777; line-height: 1.8; }

        .signature-section { margin-top: 30px; page-break-inside: avoid; }
        .signature-row { display: table; width: 100%; margin-top: 20px; }
        .signature-col { display: table-cell; width: 48%; vertical-align: top; }
        .signature-col.right { text-align: right; padding-left: 4%; }
        .signature-line { border-bottom: 1px solid #333; width: 200px; margin: 30px 0 5px 0; display: inline-block; }
        .signature-label { font-size: 10px; color: #666; }
        .signature-name { font-size: 11px; font-weight: bold; margin-top: 5px; }

        .footer { position: fixed; bottom: 20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    {{-- 公司 Header --}}
    <div class="header">
        <div class="company-name">{{ setting('company_name', 'MH Studio') }}</div>
        <div class="company-info">
            承攬人：{{ setting('company_owner', '') }} | TEL：{{ setting('company_phone', '') }}
            <br>
            Email: {{ setting('company_email', '') }} | Web: {{ setting('company_website', '') }}
        </div>
        <h1>報 價 單</h1>
    </div>

    {{-- 報價編號 --}}
    <div class="quote-number">
        <strong>報價編號：</strong>{{ $quote->quote_number }}
        <br>
        <strong>報價日期：</strong>{{ $quote->created_at->format('Y-m-d') }}
        @if($quote->valid_until)
        <br>
        <strong>有效期限：</strong>{{ $quote->valid_until->format('Y-m-d') }}
        @endif
    </div>

    {{-- 客戶/合約元資料 --}}
    <table class="meta-table">
        <tr>
            <td class="label">客戶名稱</td>
            <td class="value">{{ $quote->client->name }}</td>
            <td class="label">簽約日期</td>
            <td class="value">{{ $quote->created_at->format('Y-m-d') }}</td>
        </tr>
        @if($quote->client->company)
        <tr>
            <td class="label">公司/統編</td>
            <td class="value">{{ $quote->client->company }}</td>
            <td class="label">幣別</td>
            <td class="value">{{ $quote->currency ?? 'TWD' }}</td>
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
                <th width="30">#</th>
                <th>服務項目 / 內容</th>
                <th class="text-center" width="60">數量</th>
                <th class="text-center" width="50">單位</th>
                <th class="text-right" width="100">單價</th>
                <th class="text-right" width="100">小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $i => $item)
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
        <p><strong>戶名：</strong>{{ setting('company_name_full', setting('company_name', 'MH Studio')) }}</p>
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
        <div class="signature-row">
            <div class="signature-col">
                <p><strong>甲方（委託方）</strong></p>
                <div class="signature-name">{{ $quote->client->name }}</div>
                <div class="signature-line"></div>
                <div class="signature-label">簽名 / 蓋章</div>
                <br>
                <div class="signature-line"></div>
                <div class="signature-label">日期</div>
            </div>
            <div class="signature-col right">
                <p><strong>乙方（受託方）</strong></p>
                <div class="signature-name">{{ setting('company_owner', '') }}</div>
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
        {{ $quote->quote_number }} | {{ setting('company_name', 'MH Studio') }} | Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
