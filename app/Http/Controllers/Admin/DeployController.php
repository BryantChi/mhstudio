<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;

class DeployController extends Controller
{
    /**
     * 部署工具頁面
     */
    public function index(): View
    {
        return view('admin.deploy.index');
    }

    /**
     * Composer Install
     */
    public function composerInstall(): JsonResponse
    {
        return $this->runShellCommand(
            'cd '.base_path().' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1',
            300 // 5 分鐘 timeout
        );
    }

    /**
     * NPM Install + Build
     */
    public function npmBuild(): JsonResponse
    {
        return $this->runShellCommand(
            'cd '.base_path().' && npm install --production=false 2>&1 && npm run build 2>&1',
            300
        );
    }

    /**
     * 執行資料庫遷移
     */
    public function migrate(): JsonResponse
    {
        return $this->runArtisan('migrate', ['--force' => true]);
    }

    /**
     * 執行資料庫種子
     */
    public function seed(): JsonResponse
    {
        return $this->runArtisan('db:seed', ['--force' => true]);
    }

    /**
     * 建立 Storage 連結
     */
    public function storageLink(): JsonResponse
    {
        return $this->runArtisan('storage:link');
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
     * 一鍵部署（composer + migrate + seed + storage:link + optimize + view:cache）
     */
    public function init(): JsonResponse
    {
        $results = [];
        $startTime = microtime(true);

        try {
            // Step 1: Composer Install
            $composerResult = $this->execShell(
                'cd '.base_path().' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1',
                300
            );
            $results['composer_install'] = $composerResult['output'];
            if (! $composerResult['success']) {
                $results['composer_install'] .= ' (⚠ 失敗，可能 exec 被停用，跳過此步驟)';
            }

            // Step 1.5: NPM Install + Build
            $npmResult = $this->execShell(
                'cd '.base_path().' && npm install 2>&1 && npm run build 2>&1',
                300
            );
            $results['npm_build'] = $npmResult['output'];
            if (! $npmResult['success']) {
                $results['npm_build'] .= ' (⚠ 失敗，可能 exec 被停用，跳過此步驟)';
            }

            // Step 2: Migrate
            Artisan::call('migrate', ['--force' => true]);
            $results['migrate'] = trim(Artisan::output());

            // Step 3: Seed
            Artisan::call('db:seed', ['--force' => true]);
            $results['seed'] = trim(Artisan::output());

            // Step 4: Storage link
            try {
                Artisan::call('storage:link');
                $results['storage_link'] = trim(Artisan::output());
            } catch (\Throwable $e) {
                $results['storage_link'] = '已存在或 '.$e->getMessage();
            }

            // Step 5: Optimize
            Artisan::call('optimize:clear');
            $results['optimize_clear'] = trim(Artisan::output());

            Artisan::call('optimize');
            $results['optimize'] = trim(Artisan::output());

            // Step 6: View cache
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
     * 清理 Telescope 記錄
     */
    public function telescopePrune(): JsonResponse
    {
        try {
            // 清理記錄
            Artisan::call('telescope:prune');
            $pruneOutput = trim(Artisan::output());

            // 重建表回收空間
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE telescope_entries ENGINE=InnoDB');
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE telescope_entries_tags ENGINE=InnoDB');
            \Illuminate\Support\Facades\DB::statement('ANALYZE TABLE telescope_entries');
            \Illuminate\Support\Facades\DB::statement('ANALYZE TABLE telescope_entries_tags');

            // 回報目前大小
            $db = config('database.connections.mysql.database');
            $sizeResult = \Illuminate\Support\Facades\DB::select(
                'SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?',
                [$db]
            );
            $remaining = \Illuminate\Support\Facades\DB::table('telescope_entries')->count();

            return response()->json([
                'success' => true,
                'output' => $pruneOutput."\n\n剩餘記錄：{$remaining} 筆\n資料庫總大小：{$sizeResult[0]->size_mb} MB",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 切換 Telescope 啟用狀態（修改 .env）
     */
    public function telescopeToggle(): JsonResponse
    {
        try {
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            // 判斷目前狀態
            $currentlyEnabled = true;
            if (preg_match('/^TELESCOPE_ENABLED=(.*)$/m', $envContent, $matches)) {
                $currentlyEnabled = strtolower(trim($matches[1])) === 'true' || trim($matches[1]) === '1';
            }

            $newValue = $currentlyEnabled ? 'false' : 'true';
            $statusText = $currentlyEnabled ? '已停用' : '已啟用';

            // 更新 .env
            if (preg_match('/^TELESCOPE_ENABLED=.*$/m', $envContent)) {
                $envContent = preg_replace('/^TELESCOPE_ENABLED=.*$/m', "TELESCOPE_ENABLED={$newValue}", $envContent);
            } else {
                $envContent .= "\nTELESCOPE_ENABLED={$newValue}\n";
            }

            file_put_contents($envPath, $envContent);

            // 清除快取讓新設定生效
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'output' => "Telescope {$statusText}（TELESCOPE_ENABLED={$newValue}）\n設定快取已清除。",
                'enabled' => ! $currentlyEnabled,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 執行 Artisan 命令
     */
    protected function runArtisan(string $command, array $params = []): JsonResponse
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

    /**
     * 執行 Shell 命令並回傳 JSON
     */
    protected function runShellCommand(string $command, int $timeout = 120): JsonResponse
    {
        $result = $this->execShell($command, $timeout);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * 底層 Shell 執行（支援 Process facade 或 exec fallback）
     */
    protected function execShell(string $command, int $timeout = 120): array
    {
        try {
            // 優先使用 Laravel Process facade
            $result = Process::timeout($timeout)->run($command);

            return [
                'success' => $result->successful(),
                'output' => trim($result->output()."\n".$result->errorOutput()),
            ];
        } catch (\Throwable $e) {
            // fallback: 嘗試 exec
            try {
                $output = [];
                $exitCode = 0;
                exec($command, $output, $exitCode);

                return [
                    'success' => $exitCode === 0,
                    'output' => implode("\n", $output),
                ];
            } catch (\Throwable $e2) {
                return [
                    'success' => false,
                    'output' => '無法執行 Shell 命令：exec() 可能被主機停用。'."\n".$e2->getMessage(),
                ];
            }
        }
    }
}
