@extends('frontend.layouts.app')

@section('title', $legalPage->effective_meta_title)
@section('meta_description', $legalPage->effective_meta_description)

@section('content')
<section class="legal-page">
    <div class="legal-container">
        <header class="legal-header">
            <h1>{{ $legalPage->title }}</h1>
            <p class="legal-meta">
                最後更新日期：{{ $legalPage->updated_at->format('Y 年 m 月 d 日') }}
            </p>
        </header>

        <div class="legal-content">
            {!! $legalPage->content !!}
        </div>

        @if($otherPages->count() > 0)
        <nav class="legal-nav">
            <h4>其他法律文件</h4>
            <ul>
                @foreach($otherPages as $page)
                <li>
                    <a href="{{ route('legal.show', $page->slug) }}">{{ $page->title }}</a>
                </li>
                @endforeach
            </ul>
        </nav>
        @endif

        <div class="legal-back">
            <a href="{{ route('home') }}">&larr; 返回首頁</a>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.legal-page {
    padding: 6rem 1.5rem 4rem;
    min-height: 80vh;
}
.legal-container {
    max-width: 800px;
    margin: 0 auto;
}
.legal-header {
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,.1);
}
.legal-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: .5rem;
}
.legal-meta {
    color: rgba(255,255,255,.5);
    font-size: .9rem;
}
.legal-content {
    line-height: 1.8;
    color: rgba(255,255,255,.85);
}
.legal-content h2 {
    font-size: 1.4rem;
    font-weight: 600;
    margin: 2rem 0 1rem;
    color: #fff;
}
.legal-content h3 {
    font-size: 1.15rem;
    font-weight: 600;
    margin: 1.5rem 0 .75rem;
    color: #fff;
}
.legal-content p {
    margin-bottom: 1rem;
}
.legal-content ul, .legal-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}
.legal-content li {
    margin-bottom: .4rem;
}
.legal-content a {
    color: var(--accent);
    text-decoration: underline;
}
.legal-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}
.legal-content th, .legal-content td {
    padding: .6rem .8rem;
    border: 1px solid rgba(255,255,255,.15);
    text-align: left;
}
.legal-content th {
    background: rgba(255,255,255,.05);
    font-weight: 600;
}
.legal-nav {
    margin-top: 3rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,.1);
}
.legal-nav h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: .75rem;
    color: rgba(255,255,255,.6);
}
.legal-nav ul {
    list-style: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem 1.5rem;
}
.legal-nav a {
    color: var(--accent);
    text-decoration: none;
    font-size: .9rem;
}
.legal-nav a:hover { text-decoration: underline; }
.legal-back {
    margin-top: 2rem;
}
.legal-back a {
    color: rgba(255,255,255,.5);
    text-decoration: none;
    font-size: .9rem;
}
.legal-back a:hover { color: var(--accent); }
</style>
@endpush
