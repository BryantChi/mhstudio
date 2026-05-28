@extends('layouts.admin')

@section('title', '單據條款設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => route('admin.settings.index')],
        ['title' => '單據條款', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">單據條款設定</h2>
        <p class="text-muted">集中管理報價單的標準條款與固定備註，修改後即時套用，不需改程式</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.documents.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-header"><strong>報價單標準條款</strong></div>
                <div class="card-body">
                    <div class="mb-2">
                        <label for="quote_standard_terms" class="form-label">標準條款內容</label>
                        <textarea class="form-control @error('quote_standard_terms') is-invalid @enderror"
                                  id="quote_standard_terms" name="quote_standard_terms" rows="16"
                                  style="font-family: 'Menlo', 'Consolas', 'Courier New', monospace; line-height: 1.7;">{{ old('quote_standard_terms', setting('quote_standard_terms', '')) }}</textarea>
                        @error('quote_standard_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">報價單建立頁按「帶入標準條款」時，會填入此內容到備註欄。支援換行分段。</small>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong>報價單 PDF 固定備註</strong></div>
                <div class="card-body">
                    <div class="mb-2">
                        <label for="quote_pdf_notes" class="form-label">固定備註（每行一條）</label>
                        <textarea class="form-control @error('quote_pdf_notes') is-invalid @enderror"
                                  id="quote_pdf_notes" name="quote_pdf_notes" rows="8"
                                  style="font-family: 'Menlo', 'Consolas', 'Courier New', monospace; line-height: 1.7;">{{ old('quote_pdf_notes', setting('quote_pdf_notes', '')) }}</textarea>
                        @error('quote_pdf_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">每一行會成為報價單 PDF「備註」區的一個條列項目，無論是否填寫附加備註皆會顯示。</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 儲存設定
                        </button>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-light">返回</a>
                    </div>
                    <hr>
                    <p class="small text-muted mb-0">
                        合約條款另於「合約範本」管理，每份範本可各自編輯正文。
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
