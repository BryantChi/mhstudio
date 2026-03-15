<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 使用 Bootstrap 5 分頁樣式（搭配 CoreUI）
        Paginator::useBootstrapFive();

        // 防止 N+1 查詢問題（開發環境）
        if (app()->environment('local')) {
            Model::preventLazyLoading(true);
        }

        // 只在 debug 模式下記錄慢查詢
        if (config('app.debug')) {
            DB::listen(function ($query) {
                if ($query->time > 500) { // 超過 500ms
                    logger()->warning('Slow Query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }
    }
}
