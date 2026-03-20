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
        // 在處理請求之前存入 session，確保 StartSession middleware 能正確保存
        if ($request->isMethod('get')) {
            $routeName = $request->route()?->getName();
            if ($routeName && str_ends_with($routeName, '.index')) {
                $prefix = str_replace('.index', '', $routeName);
                session()->put("admin_list.{$prefix}", $request->fullUrl());
            }
        }

        return $next($request);
    }
}
