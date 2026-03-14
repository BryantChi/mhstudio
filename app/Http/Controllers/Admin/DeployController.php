<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class DeployController extends Controller
{
    public function __construct()
    {
        // 僅 super-admin 可使用
        $this->middleware(function ($request, $next) {
            if (! $request->user()?->isSuperAdmin()) {
                abort(403, '僅限超級管理員使用此功能');
            }
            return $next($request);
        });
    }

    /**
     * 部署工具頁面
     */
    public function index(): View
    {
        return view('admin.deploy.index');
    }

    /**
     * 執行資料庫遷移
     */
    public function migrate(): JsonResponse
    {
        return $this->runCommand('migrate', ['--force' => true]);
    }

    /**
     * 執行資料庫種子
     */
    public function seed(): JsonResponse
    {
        return $this->runCommand('db:seed', ['--force' => true]);
    }

    /**
     * 建立 Storage 連結
     */
    public function storageLink(): JsonResponse
    {
        return $this->runCommand('storage:link');
    }

    /**
     * 清除並重建快取
     */
    public function optimize(): JsonResponse
    {
        try {
            Artisan::call('optimize:clear');
            $clearOutput = trim(Artisan::output());

            Artisan::call('optimize');
            $optimizeOutput = trim(Artisan::output());

            Artisan::call('view:cache');
            $viewOutput = trim(Artisan::output());

            return response()->json([
                'success' => true,
                'output' => implode("\n", array_filter([$clearOutput, $optimizeOutput, $viewOutput])),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 一鍵部署（migrate + seed + storage:link + optimize + view:cache）
     */
    public function init(): JsonResponse
    {
        $results = [];
        $startTime = microtime(true);

        try {
            // Step 1: Migrate
            Artisan::call('migrate', ['--force' => true]);
            $results['migrate'] = trim(Artisan::output());

            // Step 2: Seed
            Artisan::call('db:seed', ['--force' => true]);
            $results['seed'] = trim(Artisan::output());

            // Step 3: Storage link
            try {
                Artisan::call('storage:link');
                $results['storage_link'] = trim(Artisan::output());
            } catch (\Throwable $e) {
                $results['storage_link'] = '已存在或 ' . $e->getMessage();
            }

            // Step 4: Optimize
            Artisan::call('optimize:clear');
            $results['optimize_clear'] = trim(Artisan::output());

            Artisan::call('optimize');
            $results['optimize'] = trim(Artisan::output());

            // Step 5: View cache
            Artisan::call('view:cache');
            $results['view_cache'] = trim(Artisan::output());

            $elapsed = round(microtime(true) - $startTime, 2);

            return response()->json([
                'success' => true,
                'message' => "一鍵部署完成，耗時 {$elapsed} 秒",
                'steps' => $results,
                'elapsed_seconds' => $elapsed,
            ]);
        } catch (\Throwable $e) {
            $elapsed = round(microtime(true) - $startTime, 2);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'completed_steps' => $results,
                'elapsed_seconds' => $elapsed,
            ], 500);
        }
    }

    /**
     * 執行單一 Artisan 命令
     */
    protected function runCommand(string $command, array $params = []): JsonResponse
    {
        try {
            Artisan::call($command, $params);
            return response()->json([
                'success' => true,
                'output' => trim(Artisan::output()),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
