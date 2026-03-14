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

// Language switcher
Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// ===== 部署輔助路由（需 token 驗證）=====
Route::prefix('deploy')->group(function () {
    Route::get('/migrate', function (\Illuminate\Http\Request $request) {
        if ($request->query('token') !== config('app.deploy_token')) {
            abort(403, 'Invalid deploy token');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return response()->json([
                'success' => true,
                'output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    })->name('deploy.migrate');

    Route::get('/seed', function (\Illuminate\Http\Request $request) {
        if ($request->query('token') !== config('app.deploy_token')) {
            abort(403, 'Invalid deploy token');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            return response()->json([
                'success' => true,
                'output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    })->name('deploy.seed');

    Route::get('/migrate-seed', function (\Illuminate\Http\Request $request) {
        if ($request->query('token') !== config('app.deploy_token')) {
            abort(403, 'Invalid deploy token');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'migrate_output' => $migrateOutput,
                'seed_output' => $seedOutput,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    })->name('deploy.migrate-seed');

    Route::get('/optimize', function (\Illuminate\Http\Request $request) {
        if ($request->query('token') !== config('app.deploy_token')) {
            abort(403, 'Invalid deploy token');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $clearOutput = \Illuminate\Support\Facades\Artisan::output();

            \Illuminate\Support\Facades\Artisan::call('optimize');
            $optimizeOutput = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'clear_output' => $clearOutput,
                'optimize_output' => $optimizeOutput,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    })->name('deploy.optimize');
});

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
