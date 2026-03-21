<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RememberListUrl
{
    /**
     * 自動記錄 admin *.index 路由的完整 URL（含 ?page=X）
     * 讓 store/update/destroy 後能返回正確頁數
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('get') && ! $request->ajax() && ! $request->wantsJson()) {
            $routeName = $request->route()?->getName();
            if ($routeName && str_ends_with($routeName, '.index') && ! $request->has('_sortable')) {
                $prefix = str_replace('.index', '', $routeName);
                $url = $request->fullUrl();
                session()->put("admin_list.{$prefix}", $url);
            }
        }

        return $next($request);
    }
}
