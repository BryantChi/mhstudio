@extends('layouts.admin')

@section('title', '文章詳情')

@php
    $breadcrumbs = [
        ['title' => '文章管理', 'url' => route('admin.articles.index')],
        ['title' => '文章詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">文章詳情</h2>
        <p class="text-muted">查看文章資訊</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('edit articles')
        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
            </svg>
            編輯
        </a>
        @endcan

        <a href="{{ route('admin.articles.index') }}" class="btn btn-light">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>文章內容</strong>
                <div>
                    <span class="badge bg-{{ $article->status_color }}">{{ $article->status_label }}</span>
                    @if($article->is_featured)
                        <span class="badge bg-warning ms-2">精選</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($article->featured_image)
                <div class="mb-4">
                    <img src="{{ $article->featured_image }}"
                         class="img-fluid rounded"
                         alt="{{ $article->title }}">
                </div>
                @endif

                <h3 class="mb-3">{{ $article->title }}</h3>

                @if($article->excerpt)
                <div class="alert alert-light border mb-3">
                    <strong>摘要：</strong>{{ $article->excerpt }}
                </div>
                @endif

                <div class="content-preview">
                    {!! nl2br(e($article->content)) !!}
                </div>
            </div>
        </div>

        @if($article->seoMeta)
        <div class="card mt-3">
            <div class="card-header">
                <strong>SEO 資訊</strong>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="200">Meta 標題</th>
                            <td>{{ $article->seoMeta->meta_title ?? $article->title }}</td>
                        </tr>
                        <tr>
                            <th>Meta 描述</th>
                            <td>{{ $article->seoMeta->meta_description ?? $article->excerpt }}</td>
                        </tr>
                        @if($article->seoMeta->meta_keywords)
                        <tr>
                            <th>Meta 關鍵字</th>
                            <td>{{ $article->seoMeta->meta_keywords }}</td>
                        </tr>
                        @endif
                        @if($article->seoMeta->canonical_url)
                        <tr>
                            <th>Canonical URL</th>
                            <td>
                                <a href="{{ $article->seoMeta->canonical_url }}" target="_blank">
                                    {{ $article->seoMeta->canonical_url }}
                                </a>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>索引狀態</th>
                            <td>
                                @if($article->seoMeta->index_status)
                                    <span class="badge bg-success">允許索引</span>
                                @else
                                    <span class="badge bg-warning">禁止索引</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if($article->seoMeta->og_title || $article->seoMeta->og_description)
                <h6 class="mt-4">Open Graph 資訊</h6>
                <table class="table">
                    <tbody>
                        @if($article->seoMeta->og_title)
                        <tr>
                            <th width="200">OG 標題</th>
                            <td>{{ $article->seoMeta->og_title }}</td>
                        </tr>
                        @endif
                        @if($article->seoMeta->og_description)
                        <tr>
                            <th>OG 描述</th>
                            <td>{{ $article->seoMeta->og_description }}</td>
                        </tr>
                        @endif
                        @if($article->seoMeta->og_image)
                        <tr>
                            <th>OG 圖片</th>
                            <td>
                                <img src="{{ $article->seoMeta->og_image }}" class="img-thumbnail" style="max-width: 300px;">
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <strong>基本資訊</strong>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="100">文章 ID</th>
                            <td>{{ $article->id }}</td>
                        </tr>
                        <tr>
                            <th>作者</th>
                            <td>{{ $article->author->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <th>狀態</th>
                            <td>
                                <span class="badge bg-{{ $article->status_color }}">
                                    {{ $article->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>分類</th>
                            <td>
                                @if($article->category)
                                    <span class="badge bg-secondary">{{ $article->category->name }}</span>
                                @else
                                    <span class="text-muted">未分類</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>標籤</th>
                            <td>
                                @forelse($article->tags as $tag)
                                    <span class="badge bg-info me-1">{{ $tag->name }}</span>
                                @empty
                                    <span class="text-muted">無標籤</span>
                                @endforelse
                            </td>
                        </tr>
                        <tr>
                            <th>網址</th>
                            <td>
                                <a href="{{ url('articles/' . $article->slug) }}" target="_blank" class="text-break">
                                    {{ $article->slug }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>發布時間</th>
                            <td>
                                @if($article->published_at)
                                    {{ $article->published_at->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-muted">未發布</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>建立時間</th>
                            <td>{{ $article->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>最後更新</th>
                            <td>{{ $article->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>統計資訊</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <svg class="icon icon-xl text-primary mb-2">
                                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                            </svg>
                            <div class="fs-5 fw-semibold">{{ number_format($article->views_count) }}</div>
                            <div class="small text-muted">瀏覽次數</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <svg class="icon icon-xl text-success mb-2">
                                <use xlink:href="/assets/icons/free.svg#cil-comment-bubble"></use>
                            </svg>
                            <div class="fs-5 fw-semibold">{{ $article->comments_count ?? 0 }}</div>
                            <div class="small text-muted">評論數量</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>設定</strong>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <svg class="icon me-2 {{ $article->is_featured ? 'text-warning' : 'text-muted' }}">
                        <use xlink:href="/assets/icons/free.svg#cil-star"></use>
                    </svg>
                    精選文章：
                    <strong>{{ $article->is_featured ? '是' : '否' }}</strong>
                </div>
                <div class="mb-2">
                    <svg class="icon me-2 {{ $article->allow_comments ? 'text-success' : 'text-muted' }}">
                        <use xlink:href="/assets/icons/free.svg#cil-comment-bubble"></use>
                    </svg>
                    允許評論：
                    <strong>{{ $article->allow_comments ? '是' : '否' }}</strong>
                </div>
            </div>
        </div>

        @can('delete articles')
        <div class="card mt-3">
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.articles.destroy', $article) }}"
                      onsubmit="return confirm('確定要刪除此文章嗎？此操作無法復原。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                        </svg>
                        刪除文章
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>

@push('styles')
<style>
    .content-preview {
        line-height: 1.8;
        font-size: 1.1rem;
        color: #333;
    }
    .icon-xl {
        width: 2rem;
        height: 2rem;
    }
</style>
@endpush
@endsection
