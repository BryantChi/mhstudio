@extends('layouts.admin')

@section('title', '編輯客戶')

@php
    $breadcrumbs = [
        ['title' => '客戶管理', 'url' => route('admin.clients.index')],
        ['title' => '編輯客戶', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯客戶</h2>
        <p class="text-muted">修改客戶資料</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-light">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
            查看
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.clients.update', $client) }}" onsubmit="showLoading()" data-form-confirm>
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>基本資訊</strong></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">客戶名稱 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $client->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">公司</label>
                            <input type="text" class="form-control @error('company') is-invalid @enderror"
                                   id="company" name="company" value="{{ old('company', $client->company) }}">
                            @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">聯繫人</label>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                                   id="contact_person" name="contact_person" value="{{ old('contact_person', $client->contact_person) }}" placeholder="主要聯繫人姓名">
                            @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="industry" class="form-label">產業類別</label>
                            <select class="form-select @error('industry') is-invalid @enderror" id="industry" name="industry">
                                <option value="">-- 請選擇 --</option>
                                @foreach(['科技', '餐飲', '教育', '零售', '醫療', '金融', '製造', '設計', '其他'] as $ind)
                                    <option value="{{ $ind }}" {{ old('industry', $client->industry) == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                                @endforeach
                            </select>
                            @error('industry') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $client->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">電話</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $client->phone) }}" placeholder="例如：0912-345-678">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label">網站</label>
                        <input type="url" class="form-control @error('website') is-invalid @enderror"
                               id="website" name="website" value="{{ old('website', $client->website) }}">
                        @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">地址</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                               id="address" name="address" value="{{ old('address', $client->address) }}">
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">備註</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes', $client->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>分類與標籤</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">客戶狀態 <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="lead" {{ old('status', $client->status) == 'lead' ? 'selected' : '' }}>潛在客戶</option>
                            <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>活躍</option>
                            <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>不活躍</option>
                            <option value="archived" {{ old('status', $client->status) == 'archived' ? 'selected' : '' }}>已歸檔</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tier" class="form-label">客戶等級 <span class="text-danger">*</span></label>
                        <select class="form-select @error('tier') is-invalid @enderror" id="tier" name="tier" required>
                            <option value="standard" {{ old('tier', $client->tier) == 'standard' ? 'selected' : '' }}>標準</option>
                            <option value="premium" {{ old('tier', $client->tier) == 'premium' ? 'selected' : '' }}>高級</option>
                            <option value="vip" {{ old('tier', $client->tier) == 'vip' ? 'selected' : '' }}>VIP</option>
                        </select>
                        @error('tier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="source" class="form-label">來源管道 <span class="text-danger">*</span></label>
                        <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                            <option value="">-- 請選擇 --</option>
                            <option value="website" {{ old('source', $client->source) == 'website' ? 'selected' : '' }}>網站</option>
                            <option value="referral" {{ old('source', $client->source) == 'referral' ? 'selected' : '' }}>推薦</option>
                            <option value="social" {{ old('source', $client->source) == 'social' ? 'selected' : '' }}>社群媒體</option>
                            <option value="cold_outreach" {{ old('source', $client->source) == 'cold_outreach' ? 'selected' : '' }}>主動開發</option>
                            <option value="other" {{ old('source', $client->source) == 'other' ? 'selected' : '' }}>其他</option>
                        </select>
                        @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tags-input" class="form-label">自定義標籤</label>
                        <input type="hidden" id="tags" name="tags" value="{{ old('tags', is_array($client->tags) ? implode(',', $client->tags) : $client->tags) }}">
                        <div id="tags-container" class="d-flex flex-wrap gap-1 mb-2"></div>
                        <input type="text" class="form-control" id="tags-input" placeholder="輸入標籤後按 Enter">
                        <small class="text-muted">按 Enter 新增標籤</small>
                        @error('tags') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="user_id" class="form-label">關聯系統帳號</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                            <option value="">不關聯</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $client->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">如果此客戶已有系統帳號，可在此關聯</small>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>
                            更新
                        </button>
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenInput = document.getElementById('tags');
    const container = document.getElementById('tags-container');
    const input = document.getElementById('tags-input');
    let tags = hiddenInput.value ? hiddenInput.value.split(',').map(t => t.trim()).filter(t => t) : [];

    function renderTags() {
        container.innerHTML = '';
        tags.forEach(function (tag, index) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary d-flex align-items-center gap-1';
            badge.textContent = tag;
            const removeBtn = document.createElement('span');
            removeBtn.innerHTML = '&times;';
            removeBtn.style.cursor = 'pointer';
            removeBtn.style.marginLeft = '4px';
            removeBtn.addEventListener('click', function () {
                tags.splice(index, 1);
                updateHidden();
                renderTags();
            });
            badge.appendChild(removeBtn);
            container.appendChild(badge);
        });
    }

    function updateHidden() {
        hiddenInput.value = tags.join(',');
    }

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = input.value.replace(/,/g, '').trim();
            if (val && !tags.includes(val)) {
                tags.push(val);
                updateHidden();
                renderTags();
            }
            input.value = '';
        }
    });

    renderTags();
});
</script>
@endpush
