<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\ClientPortalController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PageController::class, 'index'])->name('home');
Route::post('/contact', [PageController::class, 'contactSubmit'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');

// Blog
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'blogShow'])->name('blog.show');

// Portfolio
Route::get('/portfolio', [PageController::class, 'portfolio'])->name('portfolio');
Route::get('/portfolio/{slug}', [PageController::class, 'portfolioShow'])->name('portfolio.show');

// About
Route::get('/about', [PageController::class, 'about'])->name('about');

// Quote
Route::get('/quote', [PageController::class, 'quote'])->name('quote');
Route::get('/api/pricing', [PageController::class, 'pricingData'])->name('api.pricing');
Route::post('/quote-request', [PageController::class, 'quoteRequestSubmit'])
    ->middleware('throttle:3,1')
    ->name('quote-request.submit');
Route::get('/quote-status/{token}', [PageController::class, 'quoteStatus'])->name('quote-request.status');

// Services
Route::get('/services/{slug}', [PageController::class, 'serviceShow'])->name('services.show');

// Newsletter
Route::post('/subscribe', [PageController::class, 'subscribe'])
    ->middleware('throttle:5,1')
    ->name('subscribe');
Route::get('/unsubscribe', [PageController::class, 'unsubscribe'])->name('unsubscribe');

// Legal Pages (隱私權政策、服務條款等)
Route::get('/legal/{slug}', [PageController::class, 'legalPage'])->name('legal.show');

// Language switcher
Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// ===== 首次部署專用路由（資料庫有使用者後自動失效）=====
Route::get('/deploy/init', function (\Illuminate\Http\Request $request) {
    // 安全檢查 1：Token 驗證
    if ($request->query('token') !== config('app.deploy_token')) {
        abort(403, 'Invalid deploy token');
    }

    // 安全檢查 2：若已有使用者，代表非首次部署，封鎖此路由
    try {
        if (\App\Models\User::count() > 0) {
            return response()->json([
                'success' => false,
                'error' => '系統已初始化完成，此路由已停用。請登入後台使用「部署工具」頁面。',
            ], 403);
        }
    } catch (\Throwable $e) {
        // 資料表不存在，代表是全新環境，繼續執行
    }

    $results = [];
    $startTime = microtime(true);

    try {
        // Step 0: Composer Install
        try {
            $composerOutput = [];
            $composerExit = 0;
            exec('cd ' . base_path() . ' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $composerOutput, $composerExit);
            $results['composer_install'] = implode("\n", $composerOutput);
        } catch (\Throwable $e) {
            $results['composer_install'] = '跳過（exec 可能被停用）：' . $e->getMessage();
        }

        // Step 1: Migrate
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $results['migrate'] = trim(\Illuminate\Support\Facades\Artisan::output());

        // Step 2: Seed
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        $results['seed'] = trim(\Illuminate\Support\Facades\Artisan::output());

        // Step 3: Storage link
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            $results['storage_link'] = trim(\Illuminate\Support\Facades\Artisan::output());
        } catch (\Throwable $e) {
            $results['storage_link'] = '已存在或 ' . $e->getMessage();
        }

        // Step 4: Optimize
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $results['optimize_clear'] = trim(\Illuminate\Support\Facades\Artisan::output());

        \Illuminate\Support\Facades\Artisan::call('optimize');
        $results['optimize'] = trim(\Illuminate\Support\Facades\Artisan::output());

        // Step 5: View cache
        \Illuminate\Support\Facades\Artisan::call('view:cache');
        $results['view_cache'] = trim(\Illuminate\Support\Facades\Artisan::output());

        $elapsed = round(microtime(true) - $startTime, 2);

        return response()->json([
            'success' => true,
            'message' => "首次部署完成，耗時 {$elapsed} 秒。請前往 /login 登入後台。",
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
})->name('deploy.init');

// 認證路由
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// 客戶專區
Route::middleware('auth')->prefix('client')->name('client.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/projects/{project}', [ClientPortalController::class, 'projectShow'])->name('project.show');
    Route::post('/projects/{project}/comments', [ClientPortalController::class, 'addComment'])->name('project.comment');
    Route::get('/projects/{project}/files/{file}/download', [ClientPortalController::class, 'downloadFile'])->name('project.file.download');
});

// 載入後台路由
require __DIR__.'/admin.php';
