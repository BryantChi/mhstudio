@extends('layouts.admin')

@section('title', '新增服務')

@php
    $breadcrumbs = [
        ['title' => '服務管理', 'url' => route('admin.services.index')],
        ['title' => '新增服務', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增服務</h2>
        <p class="text-muted">建立新的服務項目</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.services.store') }}">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>基本資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">服務標題 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title') }}"
                               required
                               maxlength="200">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">網址別名 (Slug)</label>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug') }}"
                               maxlength="200">
                        <div class="form-text">留空將自動根據標題生成</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">圖示</label>
                        @include('admin.media.partials.icon-picker-field', [
                            'fieldName'  => 'icon',
                            'fieldId'    => 'icon',
                            'fieldValue' => old('icon'),
                        ])
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="form-label">服務摘要</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                  id="excerpt"
                                  name="excerpt"
                                  rows="3"
                                  maxlength="500">{{ old('excerpt') }}</textarea>
                        <div class="form-text">簡短描述此服務</div>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">服務詳細描述</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="15">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>功能特色與常見問題</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">功能特色</label>
                        <div id="features-fields">
                            @foreach(old('features_items', []) as $item)
                            <div class="input-group mb-2 feature-item">
                                <input type="text" name="features_items[]" class="form-control" value="{{ $item }}" placeholder="功能特色描述">
                                <button type="button" class="btn btn-outline-danger remove-feature">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-feature">
                            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                            新增功能
                        </button>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">常見問題</label>
                        <div id="faq-fields">
                            @foreach(old('faq_questions', []) as $i => $q)
                            <div class="card mb-2 faq-item">
                                <div class="card-body py-2">
                                    <input type="text" name="faq_questions[]" class="form-control mb-2" value="{{ $q }}" placeholder="問題">
                                    <textarea name="faq_answers[]" class="form-control" rows="2" placeholder="答案">{{ old('faq_answers')[$i] ?? '' }}</textarea>
                                </div>
                                <div class="card-footer py-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-faq">移除</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-faq">
                            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                            新增問答
                        </button>
                    </div>
                </div>
            </div>

            {{-- 包含項目 --}}
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>包含項目</strong>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-item">
                        <svg class="icon me-1" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                        新增項目
                    </button>
                </div>
                <div class="card-body">
                    <div id="items-container">
                        @foreach(old('items', []) as $index => $item)
                        <div class="item-row d-flex gap-2 mb-2 align-items-center">
                            <input type="text" name="items[{{ $index }}][name]" class="form-control form-control-sm"
                                   value="{{ $item['name'] ?? '' }}" placeholder="項目名稱" required>
                            <select name="items[{{ $index }}][type]" class="form-select form-select-sm" style="width: 120px; min-width: 120px;">
                                <option value="included" {{ ($item['type'] ?? '') === 'included' ? 'selected' : '' }}>包含</option>
                                <option value="highlighted" {{ ($item['type'] ?? '') === 'highlighted' ? 'selected' : '' }}>亮點</option>
                                <option value="optional" {{ ($item['type'] ?? '') === 'optional' ? 'selected' : '' }}>選配</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                <svg class="icon" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-x"></use></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-0 mt-2">方案卡片上顯示的功能清單，「亮點」項目會以粗體標示</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- 方案定價 --}}
            <div class="card">
                <div class="card-header">
                    <strong>方案定價</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">服務類型</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">-- 不分類 --</option>
                            <option value="website" {{ old('type') === 'website' ? 'selected' : '' }}>網站方案</option>
                            <option value="hosting" {{ old('type') === 'hosting' ? 'selected' : '' }}>主機代管</option>
                            <option value="maintenance" {{ old('type') === 'maintenance' ? 'selected' : '' }}>維護服務</option>
                            <option value="addon" {{ old('type') === 'addon' ? 'selected' : '' }}>加值服務</option>
                            <option value="consulting" {{ old('type') === 'consulting' ? 'selected' : '' }}>顧問服務</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subtitle" class="form-label">副標題</label>
                        <input type="text"
                               class="form-control @error('subtitle') is-invalid @enderror"
                               id="subtitle"
                               name="subtitle"
                               value="{{ old('subtitle') }}"
                               placeholder="例：功能完整的專業方案">
                        @error('subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">價格</label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number"
                                       class="form-control @error('price') is-invalid @enderror"
                                       id="price"
                                       name="price"
                                       value="{{ old('price', 0) }}"
                                       min="0" step="100">
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="billing_cycle" class="form-label">收費週期</label>
                            <select class="form-select" id="billing_cycle" name="billing_cycle">
                                <option value="">不適用</option>
                                <option value="once" {{ old('billing_cycle') === 'once' ? 'selected' : '' }}>一次性</option>
                                <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>年繳</option>
                                <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>月繳</option>
                                <option value="hourly" {{ old('billing_cycle') === 'hourly' ? 'selected' : '' }}>按時計費</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="price_label" class="form-label">價格標籤</label>
                        <input type="text"
                               class="form-control @error('price_label') is-invalid @enderror"
                               id="price_label"
                               name="price_label"
                               value="{{ old('price_label') }}"
                               placeholder="例：NT$ 5,000 起">
                        <div class="form-text">如有填寫將取代數字價格顯示</div>
                        @error('price_label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="pricing_category_id" class="form-label">關聯定價分類</label>
                        <select class="form-select" id="pricing_category_id" name="pricing_category_id">
                            <option value="">-- 不關聯 --</option>
                            @foreach($pricingCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('pricing_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} (NT$ {{ number_format($cat->base_price_min) }} ~ {{ number_format($cat->base_price_max) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">關聯後，前台「取得報價」按鈕自動導向報價計算器</div>
                    </div>

                    <div class="mb-3">
                        <label for="tech_tags" class="form-label">技術標籤</label>
                        <input type="text"
                               class="form-control @error('tech_tags') is-invalid @enderror"
                               id="tech_tags"
                               name="tech_tags"
                               value="{{ old('tech_tags') }}"
                               placeholder="Laravel, Vue.js, MySQL">
                        <div class="form-text">多個標籤請用逗號分隔</div>
                        @error('tech_tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price_range" class="form-label">價格區間文字</label>
                        <input type="text"
                               class="form-control @error('price_range') is-invalid @enderror"
                               id="price_range"
                               name="price_range"
                               value="{{ old('price_range') }}"
                               placeholder="例如：NT$ 30,000 ~ 80,000">
                        <div class="form-text">手動覆寫價格文字，留空則自動從定價分類取得</div>
                        @error('price_range')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 方案規格 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <a class="text-decoration-none" data-coreui-toggle="collapse" href="#specCollapse" role="button">
                        <strong>方案規格</strong> <small class="text-muted">（選填）</small>
                    </a>
                </div>
                <div class="collapse" id="specCollapse">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">最少頁面</label>
                                <input type="number" name="pages_min" class="form-control" value="{{ old('pages_min') }}" min="0">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">最多頁面</label>
                                <input type="number" name="pages_max" class="form-control" value="{{ old('pages_max') }}" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">修改次數</label>
                                <input type="number" name="revisions" class="form-control" value="{{ old('revisions') }}" min="0">
                                <div class="form-text">留空=不限</div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">保固月數</label>
                                <input type="number" name="warranty_months" class="form-control" value="{{ old('warranty_months') }}" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">最少工期（天）</label>
                                <input type="number" name="work_days_min" class="form-control" value="{{ old('work_days_min') }}" min="0">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">最多工期（天）</label>
                                <input type="number" name="work_days_max" class="form-control" value="{{ old('work_days_max') }}" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">設計方式</label>
                            <input type="text" name="design_method" class="form-control" value="{{ old('design_method') }}" placeholder="例：客製化設計">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 服務設定 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>服務設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number"
                               class="form-control @error('order') is-invalid @enderror"
                               id="order"
                               name="order"
                               value="{{ old('order', 0) }}"
                               min="0">
                        <div class="form-text">數字越小越前面</div>
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">啟用服務</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">推薦方案</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="show_on_homepage" name="show_on_homepage" value="1"
                                   {{ old('show_on_homepage', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_on_homepage">首頁顯示</label>
                            <div class="form-text">勾選後此服務會出現在前台首頁服務區</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存
                        </button>
                        <a href="{{ route('admin.services.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('admin.media.partials.picker-modal')

@push('scripts')
<script>
    // 自動生成 Slug
    document.getElementById('title').addEventListener('blur', function() {
        const slugInput = document.getElementById('slug');
        if (!slugInput.value) {
            const title = this.value;
            const slug = title
                .toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(/[^\w\-\u4e00-\u9fa5]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
            slugInput.value = slug;
        }
    });

    // Features 動態新增/移除
    document.getElementById('add-feature').addEventListener('click', function() {
        const container = document.getElementById('features-fields');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 feature-item';
        div.innerHTML = `
            <input type="text" name="features_items[]" class="form-control" placeholder="功能特色描述">
            <button type="button" class="btn btn-outline-danger remove-feature">
                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
            </button>
        `;
        container.appendChild(div);
        div.querySelector('input').focus();
    });

    document.getElementById('features-fields').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-feature');
        if (btn) btn.closest('.feature-item').remove();
    });

    // FAQ 動態新增/移除
    document.getElementById('add-faq').addEventListener('click', function() {
        const container = document.getElementById('faq-fields');
        const card = document.createElement('div');
        card.className = 'card mb-2 faq-item';
        card.innerHTML = `
            <div class="card-body py-2">
                <input type="text" name="faq_questions[]" class="form-control mb-2" placeholder="問題">
                <textarea name="faq_answers[]" class="form-control" rows="2" placeholder="答案"></textarea>
            </div>
            <div class="card-footer py-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-faq">移除</button>
            </div>
        `;
        container.appendChild(card);
        card.querySelector('input').focus();
    });

    document.getElementById('faq-fields').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-faq');
        if (btn) btn.closest('.faq-item').remove();
    });

    // Items（包含項目）動態新增/移除
    var itemIndex = {{ count(old('items', [])) }};
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'item-row d-flex gap-2 mb-2 align-items-center';
        row.innerHTML = `
            <input type="text" name="items[${itemIndex}][name]" class="form-control form-control-sm" placeholder="項目名稱" required>
            <select name="items[${itemIndex}][type]" class="form-select form-select-sm" style="width: 120px; min-width: 120px;">
                <option value="included">包含</option>
                <option value="highlighted">亮點</option>
                <option value="optional">選配</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                <svg class="icon" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-x"></use></svg>
            </button>
        `;
        container.appendChild(row);
        itemIndex++;
        row.querySelector('input').focus();
    });

    document.getElementById('items-container').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-item-btn');
        if (btn) btn.closest('.item-row').remove();
    });
</script>
@endpush
@endsection
