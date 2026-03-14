@extends('layouts.guest')

@section('title', '登入')

@section('content')
<div class="card">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h1 class="h3 mb-1">{{ config('app.name') }}</h1>
            <p class="text-muted">登入您的帳號</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>登入失敗</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="login" class="form-label">Email 或使用者名稱</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-user"></use>
                        </svg>
                    </span>
                    <input
                        type="text"
                        class="form-control @error('login') is-invalid @enderror"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="請輸入 Email 或使用者名稱"
                        required
                        autofocus
                    >
                    @error('login')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">密碼</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-lock-locked"></use>
                        </svg>
                    </span>
                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        placeholder="請輸入密碼"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    記住我
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-account-logout"></use>
                    </svg>
                    登入
                </button>
            </div>
        </form>
    </div>
    <div class="card-footer text-center py-3">
        <small class="text-muted">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </small>
    </div>
</div>
@endsection
