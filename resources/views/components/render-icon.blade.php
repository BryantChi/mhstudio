{{--
    Render Icon — 自動判斷圖示類型渲染
    Usage: @include('components.render-icon', ['icon' => $model->icon, 'size' => 28, 'class' => 'me-2'])

    支援三種格式：
    1. URL（http... 或 /storage...）→ <img>
    2. CoreUI name（cil-...）→ <svg><use xlink:href></svg>
    3. SVG 原始碼（<path ...）→ <svg>{!! icon !!}</svg>
--}}
@php
    $icon   = $icon ?? '';
    $size   = $size ?? 24;
    $class  = $class ?? '';
    $isUrl  = $icon && (str_starts_with($icon, 'http') || str_starts_with($icon, '/storage'));
    $isCil  = $icon && str_starts_with($icon, 'cil-');
    $isSvg  = $icon && (str_contains($icon, '<path') || str_contains($icon, '<circle') || str_contains($icon, '<rect') || str_contains($icon, '<line'));
@endphp
@if($isUrl)
    <img src="{{ $icon }}" alt="icon" width="{{ $size }}" height="{{ $size }}" style="object-fit:contain;" class="{{ $class }}">
@elseif($isCil)
    <svg class="icon {{ $class }}" width="{{ $size }}" height="{{ $size }}"><use xlink:href="/assets/icons/free.svg#{{ $icon }}"></use></svg>
@elseif($isSvg)
    <svg viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" class="{{ $class }}" fill="none" stroke="currentColor" stroke-width="1.5">{!! $icon !!}</svg>
@elseif($icon)
    {{-- Fallback: 當作 CSS class（FontAwesome 等） --}}
    <i class="{{ $icon }} {{ $class }}" style="font-size:{{ $size }}px;"></i>
@endif
