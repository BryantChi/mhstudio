@extends('layouts.admin')

@section('title', '新增合約範本')

@php
    $breadcrumbs = [
        ['title' => '合約管理', 'url' => route('admin.contracts.index')],
        ['title' => '合約範本', 'url' => route('admin.contract-templates.index')],
        ['title' => '新增範本', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增合約範本</h2>
        <p class="text-muted">建立可重複使用的合約範本</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.contract-templates.store') }}" onsubmit="showLoading()">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>範本內容</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">範本名稱 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required placeholder="例如：標準服務合約">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">範本說明</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="簡要說明此範本的使用場景">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">範本內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="16" required
                                  placeholder="合約正文。可使用佔位符：&#10;{{client_name}} - 客戶名稱&#10;{{project_name}} - 專案名稱&#10;{{start_date}} - 開始日期&#10;{{end_date}} - 結束日期&#10;{{amount}} - 金額">{{ old('content') }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">
                            可用佔位符：<code>@{{client_name}}</code>、<code>@{{project_name}}</code>、<code>@{{start_date}}</code>、<code>@{{end_date}}</code>、<code>@{{amount}}</code>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><strong>範本設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">合約類型 <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            @foreach(['service' => '服務合約', 'maintenance' => '維護合約', 'retainer' => '長期顧問', 'nda' => '保密協議', 'other' => '其他'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type', 'service') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="default_amount" class="form-label">預設金額</label>
                        <div class="input-group">
                            <span class="input-group-text">NT$</span>
                            <input type="number" class="form-control" id="default_amount" name="default_amount"
                                   value="{{ old('default_amount') }}" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order', 0) }}" min="0">
                        <div class="form-text">數字越小越前面，也可從列表頁使用拖曳排序</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">啟用</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 儲存範本
                        </button>
                        <a href="{{ route('admin.contract-templates.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@push('scripts')
@include('admin.partials.tinymce', ['selector' => 'content'])
@endpush
@endsection
