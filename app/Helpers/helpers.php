<?php

if (! function_exists('admin_list_url')) {
    /**
     * 取得後台列表頁的記憶 URL（含 ?page=X），用於 store/update/destroy 後返回正確頁數
     */
    function admin_list_url(string $indexRoute): string
    {
        $prefix = str_replace('.index', '', $indexRoute);
        return session("admin_list.{$prefix}", route($indexRoute));
    }
}

if (! function_exists('setting')) {
    /**
     * 獲取或設置系統配置
     */
    function setting(string $key, $default = null)
    {
        if (class_exists('\App\Models\Setting')) {
            return \App\Models\Setting::get($key, $default);
        }

        return $default;
    }
}

if (! function_exists('format_date')) {
    /**
     * 格式化日期
     */
    function format_date($date, string $format = 'Y-m-d H:i:s'): string
    {
        if (! $date) {
            return '';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }
}

if (! function_exists('format_file_size')) {
    /**
     * 格式化文件大小
     */
    function format_file_size(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}

if (! function_exists('active_route')) {
    /**
     * 檢查當前路由是否活動
     */
    function active_route(string|array $routes, string $activeClass = 'active'): string
    {
        $routes = is_array($routes) ? $routes : [$routes];

        foreach ($routes as $route) {
            if (request()->routeIs($route.'*')) {
                return $activeClass;
            }
        }

        return '';
    }
}

if (! function_exists('can_any')) {
    /**
     * 檢查用戶是否擁有任一權限
     */
    function can_any(array $permissions): bool
    {
        if (! auth()->check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('admin_asset')) {
    /**
     * 獲取後台資源路徑
     */
    function admin_asset(string $path): string
    {
        return asset('assets/'.ltrim($path, '/'));
    }
}

if (! function_exists('truncate_html')) {
    /**
     * 截斷 HTML 內容
     */
    function truncate_html(string $html, int $length = 100, string $ending = '...'): string
    {
        $text = strip_tags($html);
        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length).$ending;
        }

        return $text;
    }
}

if (! function_exists('generate_slug')) {
    /**
     * 生成 URL slug
     */
    function generate_slug(string $string): string
    {
        return \Illuminate\Support\Str::slug($string);
    }
}

if (! function_exists('flash_success')) {
    /**
     * 設置成功訊息
     */
    function flash_success(string $message): void
    {
        session()->flash('success', $message);
    }
}

if (! function_exists('flash_error')) {
    /**
     * 設置錯誤訊息
     */
    function flash_error(string $message): void
    {
        session()->flash('error', $message);
    }
}

if (! function_exists('flash_warning')) {
    /**
     * 設置警告訊息
     */
    function flash_warning(string $message): void
    {
        session()->flash('warning', $message);
    }
}

if (! function_exists('flash_info')) {
    /**
     * 設置資訊訊息
     */
    function flash_info(string $message): void
    {
        session()->flash('info', $message);
    }
}
