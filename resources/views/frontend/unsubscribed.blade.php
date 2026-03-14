@extends('frontend.layouts.app')

@section('title', '取消訂閱 | MH Studio')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: #0a0a1a; color: #ffffff; font-family: 'Noto Sans TC', sans-serif;">
    <div style="text-align: center; max-width: 480px; padding: 40px 24px;">
        <div style="font-size: 48px; margin-bottom: 24px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#a0a0b0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-6l-2 3h-4l-2-3H2"></path>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
            </svg>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 16px; color: #ffffff;">已取消訂閱</h1>
        <p style="font-size: 16px; line-height: 1.7; color: #a0a0b0; margin-bottom: 32px;">
            您已成功取消電子報訂閱。<br>
            我們不會再寄送電子報給您。
        </p>
        <a href="{{ route('home') }}"
           style="display: inline-block; padding: 12px 32px; background-color: #ffffff; color: #0a0a1a; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;">
            返回首頁
        </a>
    </div>
</div>
@endsection
