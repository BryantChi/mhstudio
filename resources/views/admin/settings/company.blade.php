@extends('layouts.admin')

@section('title', '公司資訊設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#'],
        ['title' => '公司資訊', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">公司資訊設定</h2>
        <p class="text-muted">管理公司基本資訊與匯款帳戶，這些資訊將顯示於報價單、合約書等正式文件</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.company.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>公司基本資訊</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">公司名稱（英文） <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name"
                                   name="company_name"
                                   value="{{ old('company_name', setting('company_name', '')) }}"
                                   required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_name_full" class="form-label">公司全名（中文）</label>
                            <input type="text"
                                   class="form-control @error('company_name_full') is-invalid @enderror"
                                   id="company_name_full"
                                   name="company_name_full"
                                   value="{{ old('company_name_full', setting('company_name_full', '')) }}">
                            @error('company_name_full')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_owner" class="form-label">負責人 <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('company_owner') is-invalid @enderror"
                                   id="company_owner"
                                   name="company_owner"
                                   value="{{ old('company_owner', setting('company_owner', '')) }}"
                                   required>
                            @error('company_owner')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_phone" class="form-label">聯絡電話 <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('company_phone') is-invalid @enderror"
                                   id="company_phone"
                                   name="company_phone"
                                   value="{{ old('company_phone', setting('company_phone', '')) }}"
                                   required>
                            @error('company_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_email" class="form-label">公司信箱 <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('company_email') is-invalid @enderror"
                                   id="company_email"
                                   name="company_email"
                                   value="{{ old('company_email', setting('company_email', '')) }}"
                                   required>
                            @error('company_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_website" class="form-label">公司網站</label>
                            <input type="text"
                                   class="form-control @error('company_website') is-invalid @enderror"
                                   id="company_website"
                                   name="company_website"
                                   value="{{ old('company_website', setting('company_website', '')) }}">
                            @error('company_website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_address" class="form-label">公司地址 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('company_address') is-invalid @enderror"
                               id="company_address"
                               name="company_address"
                               value="{{ old('company_address', setting('company_address', '')) }}"
                               required>
                        @error('company_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="company_id_number" class="form-label">統一編號</label>
                        <input type="text"
                               class="form-control @error('company_id_number') is-invalid @enderror"
                               id="company_id_number"
                               name="company_id_number"
                               value="{{ old('company_id_number', setting('company_id_number', '')) }}">
                        <div class="form-text">此欄位為非公開資訊，僅用於合約與發票</div>
                        @error('company_id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>匯款資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">銀行名稱</label>
                        <input type="text"
                               class="form-control @error('bank_name') is-invalid @enderror"
                               id="bank_name"
                               name="bank_name"
                               value="{{ old('bank_name', setting('bank_name', '')) }}">
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bank_code" class="form-label">銀行代碼</label>
                        <input type="text"
                               class="form-control @error('bank_code') is-invalid @enderror"
                               id="bank_code"
                               name="bank_code"
                               value="{{ old('bank_code', setting('bank_code', '')) }}">
                        @error('bank_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bank_account" class="form-label">銀行帳號</label>
                        <input type="text"
                               class="form-control @error('bank_account') is-invalid @enderror"
                               id="bank_account"
                               name="bank_account"
                               value="{{ old('bank_account', setting('bank_account', '')) }}">
                        @error('bank_account')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bank_branch" class="form-label">銀行分行</label>
                        <input type="text"
                               class="form-control @error('bank_branch') is-invalid @enderror"
                               id="bank_branch"
                               name="bank_branch"
                               value="{{ old('bank_branch', setting('bank_branch', '')) }}">
                        @error('bank_branch')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bank_account_holder" class="form-label">匯款戶名</label>
                        <input type="text"
                               class="form-control @error('bank_account_holder') is-invalid @enderror"
                               id="bank_account_holder"
                               name="bank_account_holder"
                               value="{{ old('bank_account_holder', setting('bank_account_holder', '')) }}"
                               placeholder="留空則使用公司全名">
                        <small class="text-muted">顯示於報價單／合約 PDF 的匯款資訊區塊</small>
                        @error('bank_account_holder')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            儲存設定
                        </button>
                        <button type="reset" class="btn btn-light">重置</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
