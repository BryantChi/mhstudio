@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <svg class="icon me-2">
        <use xlink:href="/assets/icons/free.svg#cil-check-circle"></use>
    </svg>
    {{ session('success') }}
    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <svg class="icon me-2">
        <use xlink:href="/assets/icons/free.svg#cil-x-circle"></use>
    </svg>
    {{ session('error') }}
    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <svg class="icon me-2">
        <use xlink:href="/assets/icons/free.svg#cil-warning"></use>
    </svg>
    {{ session('warning') }}
    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <svg class="icon me-2">
        <use xlink:href="/assets/icons/free.svg#cil-info"></use>
    </svg>
    {{ session('info') }}
    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <svg class="icon me-2">
        <use xlink:href="/assets/icons/free.svg#cil-x-circle"></use>
    </svg>
    <strong>發生錯誤：</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
</div>
@endif
