<div class="category-tree-item level-{{ $level }}" style="border-left-color: {{ $category->color ?? '#6c757d' }}">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            @if($category->icon)
                <i class="{{ $category->icon }} me-2" style="color: {{ $category->color ?? '#6c757d' }}"></i>
            @endif
            <strong>{{ $category->name }}</strong>
            <span class="badge bg-light text-dark ms-2">{{ $category->articles_count ?? 0 }} 篇文章</span>
            @if($category->status === 'inactive')
                <span class="badge bg-secondary ms-1">停用</span>
            @endif
        </div>
        <div>
            @can('view categories')
            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-sm btn-light">
                <svg class="icon">
                    <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                </svg>
            </a>
            @endcan
            @can('edit categories')
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-light">
                <svg class="icon">
                    <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                </svg>
            </a>
            @endcan
        </div>
    </div>

    @if($category->description)
    <p class="text-muted small mb-0 mt-2">{{ Str::limit($category->description, 100) }}</p>
    @endif
</div>

@if($category->children && $category->children->count() > 0)
<div class="category-tree-children">
    @foreach($category->children as $child)
        @include('admin.categories.partials.tree-item', ['category' => $child, 'level' => $level + 1])
    @endforeach
</div>
@endif
