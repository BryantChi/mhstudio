@extends('layouts.admin')

@section('title', '新增用戶')

@php
    $breadcrumbs = [
        ['title' => '用戶管理', 'url' => route('admin.users.index')],
        ['title' => '新增用戶', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增用戶</h2>
        <p class="text-muted">建立新的系統用戶</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>基本資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">密碼 <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               required>
                        <div class="form-text">密碼至少需要 8 個字元</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">確認密碼 <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation"
                               required>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>角色設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">選擇角色</label>
                        @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="roles[]"
                                   value="{{ $role->name }}"
                                   id="role-{{ $role->id }}"
                                   {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role-{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                        @endforeach
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
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
