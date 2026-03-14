@extends('layouts.admin')

@section('title', '定價管理')

@php
    $breadcrumbs = [
        ['title' => '定價管理', 'url' => '#']
    ];
@endphp

@section('content')
{{-- Page Header --}}
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">定價管理</h2>
        <p class="text-muted">管理服務定價分類與功能項目</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createCategoryModal">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增分類
        </button>
        <button type="button" class="btn btn-outline-primary ms-2" data-coreui-toggle="modal" data-coreui-target="#createFeatureModal">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增通用功能
        </button>
    </div>
</div>

{{-- Categories Accordion --}}
@if($categories->count() > 0)
    <div class="accordion" id="categoriesAccordion">
        @foreach($categories as $category)
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between py-3" id="heading-{{ $category->id }}">
                <div class="d-flex align-items-center flex-grow-1"
                     style="cursor: pointer;"
                     data-coreui-toggle="collapse"
                     data-coreui-target="#collapse-{{ $category->id }}"
                     aria-expanded="false"
                     aria-controls="collapse-{{ $category->id }}">
                    <span class="me-2">
                    @if($category->icon)
                        @include('components.render-icon', ['icon' => $category->icon, 'size' => 18])
                    @else
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-folder"></use></svg>
                    @endif
                    </span>
                    <div>
                        <strong class="me-2">{{ $category->name }}</strong>
                        <span class="text-muted me-2">
                            NT$ {{ number_format($category->base_price_min) }} ~ {{ number_format($category->base_price_max) }}
                        </span>
                        @if($category->is_active)
                            <span class="badge bg-success">啟用</span>
                        @else
                            <span class="badge bg-secondary">停用</span>
                        @endif
                        <span class="badge bg-info ms-1">{{ $category->features->count() }} 項功能</span>
                    </div>
                </div>
                <div class="btn-group ms-3" role="group">
                    <button type="button"
                            class="btn btn-sm btn-light"
                            data-coreui-toggle="tooltip"
                            title="編輯分類"
                            onclick="editCategory({{ $category->id }}, {{ json_encode($category->toArray()) }})">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </button>
                    <form method="POST"
                          action="{{ route('admin.pricing.categories.destroy', $category) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此分類嗎？刪除後該分類下的功能也會一併刪除。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-light text-danger"
                                data-coreui-toggle="tooltip"
                                title="刪除分類">
                            <svg class="icon">
                                <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <div id="collapse-{{ $category->id }}" class="collapse" aria-labelledby="heading-{{ $category->id }}" data-coreui-parent="#categoriesAccordion">
                <div class="card-body p-0">
                    @if($category->description)
                    <div class="px-4 py-2 bg-light border-bottom">
                        <small class="text-muted">{{ $category->description }}</small>
                    </div>
                    @endif

                    @if($category->features->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>功能名稱</th>
                                    <th>說明</th>
                                    <th>價格範圍</th>
                                    <th>狀態</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->features as $feature)
                                <tr>
                                    <td class="text-muted">{{ $feature->order }}</td>
                                    <td>
                                        @if($feature->icon)
                                        <svg class="icon me-1">
                                            <use xlink:href="/assets/icons/free.svg#{{ $feature->icon }}"></use>
                                        </svg>
                                        @endif
                                        <strong>{{ $feature->name }}</strong>
                                    </td>
                                    <td><small class="text-muted">{{ $feature->description ?? '-' }}</small></td>
                                    <td>NT$ {{ number_format($feature->price_min) }} ~ {{ number_format($feature->price_max) }}</td>
                                    <td>
                                        @if($feature->is_active)
                                            <span class="badge bg-success">啟用</span>
                                        @else
                                            <span class="badge bg-secondary">停用</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-light"
                                                    data-coreui-toggle="tooltip"
                                                    title="編輯"
                                                    onclick="editFeature({{ $feature->id }}, {{ json_encode($feature->toArray()) }})">
                                                <svg class="icon">
                                                    <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                                </svg>
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('admin.pricing.features.destroy', $feature) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('確定要刪除此功能項目嗎？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-light text-danger"
                                                        data-coreui-toggle="tooltip"
                                                        title="刪除">
                                                    <svg class="icon">
                                                        <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <svg class="icon icon-xl mb-2">
                            <use xlink:href="/assets/icons/free.svg#cil-layers"></use>
                        </svg>
                        <p>此分類尚無功能項目</p>
                    </div>
                    @endif

                    <div class="card-footer bg-light text-end">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                onclick="openCreateFeatureForCategory({{ $category->id }}, '{{ $category->name }}')">
                            <svg class="icon me-1">
                                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
                            </svg>
                            新增功能
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
<div class="card mb-4">
    <div class="card-body text-center py-5">
        <svg class="icon icon-3xl text-muted mb-3">
            <use xlink:href="/assets/icons/free.svg#cil-calculator"></use>
        </svg>
        <p class="text-muted mb-3">尚無定價分類</p>
        <button type="button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createCategoryModal">
            新增第一個分類
        </button>
    </div>
</div>
@endif

{{-- Universal Features Section --}}
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-globe-alt"></use>
            </svg>
            <strong>通用功能</strong>
            <small class="text-muted ms-2">不屬於任何分類的通用功能項目</small>
        </div>
        <button type="button"
                class="btn btn-sm btn-outline-primary"
                data-coreui-toggle="modal"
                data-coreui-target="#createFeatureModal">
            <svg class="icon me-1">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增通用功能
        </button>
    </div>
    <div class="card-body p-0">
        @if($universalFeatures->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>功能名稱</th>
                        <th>說明</th>
                        <th>價格範圍</th>
                        <th>狀態</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($universalFeatures as $feature)
                    <tr>
                        <td class="text-muted">{{ $feature->order }}</td>
                        <td>
                            @if($feature->icon)
                            <svg class="icon me-1">
                                <use xlink:href="/assets/icons/free.svg#{{ $feature->icon }}"></use>
                            </svg>
                            @endif
                            <strong>{{ $feature->name }}</strong>
                        </td>
                        <td><small class="text-muted">{{ $feature->description ?? '-' }}</small></td>
                        <td>NT$ {{ number_format($feature->price_min) }} ~ {{ number_format($feature->price_max) }}</td>
                        <td>
                            @if($feature->is_active)
                                <span class="badge bg-success">啟用</span>
                            @else
                                <span class="badge bg-secondary">停用</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <button type="button"
                                        class="btn btn-sm btn-light"
                                        data-coreui-toggle="tooltip"
                                        title="編輯"
                                        onclick="editFeature({{ $feature->id }}, {{ json_encode($feature->toArray()) }})">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </button>
                                <form method="POST"
                                      action="{{ route('admin.pricing.features.destroy', $feature) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此功能項目嗎？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-light text-danger"
                                            data-coreui-toggle="tooltip"
                                            title="刪除">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4 text-muted">
            <svg class="icon icon-xl mb-2">
                <use xlink:href="/assets/icons/free.svg#cil-globe-alt"></use>
            </svg>
            <p>尚無通用功能項目</p>
        </div>
        @endif
    </div>
</div>

{{-- Create Category Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pricing.categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-folder"></use>
                        </svg>
                        新增定價分類
                    </h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cat-name" class="form-label">分類名稱 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cat-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="cat-description" class="form-label">說明</label>
                        <textarea class="form-control" id="cat-description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cat-base-price-min" class="form-label">基本價格 (最低) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="cat-base-price-min" name="base_price_min" step="1" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="cat-base-price-max" class="form-label">基本價格 (最高) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="cat-base-price-max" name="base_price_max" step="1" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="cat-icon" class="form-label">圖示</label>
                        @include('admin.media.partials.icon-picker-field', [
                            'fieldName'  => 'icon',
                            'fieldId'    => 'cat-icon',
                            'fieldValue' => '',
                        ])
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cat-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="cat-is-active">啟用</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-1">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        建立分類
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editCategoryForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                        編輯定價分類
                    </h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-cat-name" class="form-label">分類名稱 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-cat-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-cat-description" class="form-label">說明</label>
                        <textarea class="form-control" id="edit-cat-description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-cat-base-price-min" class="form-label">基本價格 (最低) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="edit-cat-base-price-min" name="base_price_min" step="1" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-cat-base-price-max" class="form-label">基本價格 (最高) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="edit-cat-base-price-max" name="base_price_max" step="1" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-cat-icon" class="form-label">圖示</label>
                        @include('admin.media.partials.icon-picker-field', [
                            'fieldName'  => 'icon',
                            'fieldId'    => 'edit-cat-icon',
                            'fieldValue' => '',
                        ])
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit-cat-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-cat-is-active">啟用</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-1">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        更新分類
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Create Feature Modal --}}
<div class="modal fade" id="createFeatureModal" tabindex="-1" aria-labelledby="createFeatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pricing.features.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createFeatureModalLabel">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-layers"></use>
                        </svg>
                        新增功能項目
                    </h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="feat-category" class="form-label">所屬分類</label>
                        <select class="form-select" id="feat-category" name="pricing_category_id">
                            <option value="">通用功能 (不屬於任何分類)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="feat-name" class="form-label">功能名稱 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="feat-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="feat-description" class="form-label">說明</label>
                        <input type="text" class="form-control" id="feat-description" name="description" placeholder="簡短說明此功能">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="feat-price-min" class="form-label">價格 (最低) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="feat-price-min" name="price_min" step="1" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="feat-price-max" class="form-label">價格 (最高) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="feat-price-max" name="price_max" step="1" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="feat-icon" class="form-label">圖示</label>
                        @include('admin.media.partials.icon-picker-field', [
                            'fieldName'  => 'icon',
                            'fieldId'    => 'feat-icon',
                            'fieldValue' => '',
                        ])
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="feat-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="feat-is-active">啟用</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-1">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        建立功能
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Feature Modal --}}
<div class="modal fade" id="editFeatureModal" tabindex="-1" aria-labelledby="editFeatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editFeatureForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editFeatureModalLabel">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                        編輯功能項目
                    </h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-feat-category" class="form-label">所屬分類</label>
                        <select class="form-select" id="edit-feat-category" name="pricing_category_id">
                            <option value="">通用功能 (不屬於任何分類)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-feat-name" class="form-label">功能名稱 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-feat-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-feat-description" class="form-label">說明</label>
                        <input type="text" class="form-control" id="edit-feat-description" name="description" placeholder="簡短說明此功能">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-feat-price-min" class="form-label">價格 (最低) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="edit-feat-price-min" name="price_min" step="1" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-feat-price-max" class="form-label">價格 (最高) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" id="edit-feat-price-max" name="price_max" step="1" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-feat-icon" class="form-label">圖示</label>
                        @include('admin.media.partials.icon-picker-field', [
                            'fieldName'  => 'icon',
                            'fieldId'    => 'edit-feat-icon',
                            'fieldValue' => '',
                        ])
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit-feat-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-feat-is-active">啟用</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-1">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        更新功能
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.media.partials.picker-modal')
@endsection

@push('scripts')
<script>
    // Edit Category
    function editCategory(id, data) {
        const form = document.getElementById('editCategoryForm');
        form.action = '{{ url("/" . config("admin.prefix", "admin") . "/pricing/categories") }}/' + id;

        document.getElementById('edit-cat-name').value = data.name || '';
        document.getElementById('edit-cat-description').value = data.description || '';
        document.getElementById('edit-cat-base-price-min').value = data.base_price_min || 0;
        document.getElementById('edit-cat-base-price-max').value = data.base_price_max || 0;
        document.getElementById('edit-cat-icon').value = data.icon || '';
        document.getElementById('edit-cat-is-active').checked = data.is_active;

        const modal = new coreui.Modal(document.getElementById('editCategoryModal'));
        modal.show();
    }

    // Edit Feature
    function editFeature(id, data) {
        const form = document.getElementById('editFeatureForm');
        form.action = '{{ url("/" . config("admin.prefix", "admin") . "/pricing/features") }}/' + id;

        document.getElementById('edit-feat-category').value = data.pricing_category_id || '';
        document.getElementById('edit-feat-name').value = data.name || '';
        document.getElementById('edit-feat-description').value = data.description || '';
        document.getElementById('edit-feat-price-min').value = data.price_min || 0;
        document.getElementById('edit-feat-price-max').value = data.price_max || 0;
        document.getElementById('edit-feat-icon').value = data.icon || '';
        document.getElementById('edit-feat-is-active').checked = data.is_active;

        const modal = new coreui.Modal(document.getElementById('editFeatureModal'));
        modal.show();
    }

    // Open Create Feature modal with pre-selected category
    function openCreateFeatureForCategory(categoryId, categoryName) {
        document.getElementById('feat-category').value = categoryId;
        document.getElementById('feat-name').value = '';
        document.getElementById('feat-description').value = '';
        document.getElementById('feat-price-min').value = '';
        document.getElementById('feat-price-max').value = '';
        document.getElementById('feat-icon').value = '';
        document.getElementById('feat-is-active').checked = true;

        const modal = new coreui.Modal(document.getElementById('createFeatureModal'));
        modal.show();
    }
</script>
@endpush
