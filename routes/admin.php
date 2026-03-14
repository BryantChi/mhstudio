<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TimeEntryController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\ContractTemplateController;
use App\Http\Controllers\Admin\DeployController;
use App\Http\Controllers\Admin\LegalPageController;
use App\Http\Controllers\Admin\QuoteRequestController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| 後台管理系統路由
| 前綴: /admin
| 中介層: auth, verified (需要登入和驗證)
|
*/

Route::prefix(config('admin.prefix', 'admin'))
    ->name('admin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // 儀表板
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/system-info', [DashboardController::class, 'systemInfo'])->name('system-info');

        // 流量分析
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/api/overview', [AnalyticsController::class, 'apiOverview'])->name('analytics.api.overview');
        Route::get('analytics/api/pages', [AnalyticsController::class, 'apiPages'])->name('analytics.api.pages');
        Route::get('analytics/api/referrers', [AnalyticsController::class, 'apiReferrers'])->name('analytics.api.referrers');
        Route::get('analytics/api/chart', [AnalyticsController::class, 'apiChart'])->name('analytics.api.chart');

        // 用戶管理
        Route::resource('users', UserController::class);

        // 文章管理
        Route::resource('articles', ArticleController::class);

        // 分類管理
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::resource('categories', CategoryController::class);

        // 標籤管理
        Route::resource('tags', TagController::class);
        Route::post('tags/{tag}/sync-count', [TagController::class, 'syncCount'])->name('tags.sync-count');
        Route::post('tags/sync-all-counts', [TagController::class, 'syncAllCounts'])->name('tags.sync-all-counts');

        // SEO 管理
        Route::prefix('seo')->name('seo.')->group(function () {
            Route::get('/', [SeoController::class, 'index'])->name('index');
            Route::get('/meta', [SeoController::class, 'meta'])->name('meta');
            Route::get('/meta/{seoMeta}/edit', [SeoController::class, 'editMeta'])->name('meta.edit');
            Route::put('/meta/{seoMeta}', [SeoController::class, 'updateMeta'])->name('meta.update');
            Route::post('/generate-sitemap', [SeoController::class, 'generateSitemap'])->name('generate-sitemap');
            Route::get('/sitemap-settings', [SeoController::class, 'sitemapSettings'])->name('sitemap-settings');
            Route::put('/sitemap-settings', [SeoController::class, 'updateSitemapSettings'])->name('sitemap-settings.update');
            Route::get('/robots-txt', [SeoController::class, 'robotsTxt'])->name('robots-txt')->defaults('view', 'admin.seo.robots-txt-simple');
            Route::put('/robots-txt', [SeoController::class, 'updateRobotsTxt'])->name('robots-txt.update');
            Route::post('/generate-missing', [SeoController::class, 'generateMissingSeoMeta'])->name('generate-missing');
            Route::get('/analyze', [SeoController::class, 'analyze'])->name('analyze');
        });

        // 系統設定
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');

            // 一般設定
            Route::get('/general', [SettingController::class, 'general'])->name('general');
            Route::put('/general', [SettingController::class, 'updateGeneral'])->name('general.update');

            // SEO 設定
            Route::get('/seo', [SettingController::class, 'seo'])->name('seo');
            Route::put('/seo', [SettingController::class, 'updateSeo'])->name('seo.update');

            // 分析設定
            Route::get('/analytics', [SettingController::class, 'analytics'])->name('analytics');
            Route::put('/analytics', [SettingController::class, 'updateAnalytics'])->name('analytics.update');

            // 郵件設定
            Route::get('/mail', [SettingController::class, 'mail'])->name('mail');
            Route::put('/mail', [SettingController::class, 'updateMail'])->name('mail.update');

            // 前台設定
            Route::get('/frontend', [SettingController::class, 'frontend'])->name('frontend');
            Route::put('/frontend', [SettingController::class, 'updateFrontend'])->name('frontend.update');

            // 公司資訊
            Route::get('/company', [SettingController::class, 'company'])->name('company');
            Route::put('/company', [SettingController::class, 'updateCompany'])->name('company.update');

            // 快取管理
            Route::post('/clear-cache', [SettingController::class, 'clearCache'])->name('clear-cache');

            // 自訂設定
            Route::get('/create', [SettingController::class, 'create'])->name('create');
            Route::post('/', [SettingController::class, 'store'])->name('store');
            Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('edit');
            Route::put('/{setting}', [SettingController::class, 'update'])->name('update');
            Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy');
        });

        // 聯繫訊息
        Route::post('contact-messages/mark-all-read', [ContactMessageController::class, 'markAllRead'])->name('contact-messages.mark-all-read');
        Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show', 'update', 'destroy']);

        // 作品集管理
        Route::resource('projects', ProjectController::class);

        // 專案客戶管理
        Route::get('projects/{project}/clients', [ProjectController::class, 'clients'])->name('projects.clients');
        Route::put('projects/{project}/clients', [ProjectController::class, 'updateClients'])->name('projects.clients.update');
        Route::post('projects/{project}/milestones', [ProjectController::class, 'addMilestone'])->name('projects.milestones.store');
        Route::put('milestones/{milestone}', [ProjectController::class, 'updateMilestone'])->name('projects.milestones.update');
        Route::delete('milestones/{milestone}', [ProjectController::class, 'destroyMilestone'])->name('projects.milestones.destroy');
        Route::post('projects/{project}/files', [ProjectController::class, 'uploadFile'])->name('projects.files.store');
        Route::delete('files/{file}', [ProjectController::class, 'destroyFile'])->name('projects.files.destroy');
        Route::post('projects/{project}/comments', [ProjectController::class, 'addComment'])->name('projects.comments.store');

        // 客戶評價
        Route::post('testimonials/reorder', [TestimonialController::class, 'reorder'])->name('testimonials.reorder');
        Route::resource('testimonials', TestimonialController::class)->except(['show']);

        // 服務管理
        Route::post('services/reorder', [ServiceController::class, 'reorder'])->name('services.reorder');
        Route::resource('services', ServiceController::class)->except(['show']);

        // 媒體庫
        Route::get('media/browse', [MediaController::class, 'browse'])->name('media.browse');
        Route::post('media/bulk-destroy', [MediaController::class, 'bulkDestroy'])->name('media.bulk-destroy');
        Route::resource('media', MediaController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['media' => 'media_item']);

        // 電子報訂閱者
        Route::get('subscribers/export', [SubscriberController::class, 'export'])->name('subscribers.export');
        Route::resource('subscribers', SubscriberController::class)->only(['index', 'destroy']);

        // 電子報管理
        Route::post('newsletters/{newsletter}/send', [NewsletterController::class, 'send'])->name('newsletters.send');
        Route::get('newsletters/{newsletter}/preview', [NewsletterController::class, 'preview'])->name('newsletters.preview');
        Route::post('newsletters/{newsletter}/send-test', [NewsletterController::class, 'sendTest'])->name('newsletters.send-test');
        Route::resource('newsletters', NewsletterController::class);

        // ===== 商業管理模組 =====

        // 客戶 CRM
        Route::resource('clients', ClientController::class);
        Route::post('clients/{client}/interactions', [ClientController::class, 'storeInteraction'])->name('clients.interactions.store');
        Route::delete('interactions/{interaction}', [ClientController::class, 'destroyInteraction'])->name('clients.interactions.destroy');

        // 合約管理
        Route::post('contracts/{contract}/duplicate', [ContractController::class, 'duplicate'])->name('contracts.duplicate');
        Route::get('contracts/{contract}/pdf', [ContractController::class, 'exportPdf'])->name('contracts.pdf');
        Route::put('contracts/{contract}/status', [ContractController::class, 'updateStatus'])->name('contracts.update-status');
        Route::resource('contracts', ContractController::class);

        // 合約範本
        Route::post('contract-templates/reorder', [ContractTemplateController::class, 'reorder'])->name('contract-templates.reorder');
        Route::resource('contract-templates', ContractTemplateController::class);

        // 報價單
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'exportPdf'])->name('quotes.pdf');
        Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToInvoice'])->name('quotes.convert');
        Route::post('quotes/{quote}/convert-to-contract', [QuoteController::class, 'convertToContract'])->name('quotes.convert-to-contract');
        Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');
        Route::resource('quotes', QuoteController::class);

        // 發票管理
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
        Route::resource('invoices', InvoiceController::class);

        // 任務管理
        Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::resource('tasks', TaskController::class)->except(['show']);

        // 工時追蹤
        Route::get('time-entries/report', [TimeEntryController::class, 'report'])->name('time-entries.report');
        Route::post('time-entries/start', [TimeEntryController::class, 'startTimer'])->name('time-entries.start');
        Route::put('time-entries/{timeEntry}/stop', [TimeEntryController::class, 'stopTimer'])->name('time-entries.stop');
        Route::resource('time-entries', TimeEntryController::class)->except(['create', 'show', 'edit']);

        // API: 取得專案任務列表
        Route::get('api/projects/{project}/tasks', [TimeEntryController::class, 'projectTasks'])->name('api.project-tasks');

        // 定價管理
        Route::prefix('pricing')->name('pricing.')->group(function () {
            Route::get('/', [PricingController::class, 'index'])->name('index');
            Route::post('/categories', [PricingController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{category}', [PricingController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{category}', [PricingController::class, 'destroyCategory'])->name('categories.destroy');
            Route::post('/categories/reorder', [PricingController::class, 'reorderCategories'])->name('categories.reorder');
            Route::post('/features', [PricingController::class, 'storeFeature'])->name('features.store');
            Route::put('/features/{feature}', [PricingController::class, 'updateFeature'])->name('features.update');
            Route::delete('/features/{feature}', [PricingController::class, 'destroyFeature'])->name('features.destroy');
            Route::post('/features/reorder', [PricingController::class, 'reorderFeatures'])->name('features.reorder');
        });

        // 法律頁面管理
        Route::post('legal-pages/reorder', [LegalPageController::class, 'reorder'])->name('legal-pages.reorder');
        Route::resource('legal-pages', LegalPageController::class)->except(['show']);

        // 報價請求管理
        Route::resource('quote-requests', QuoteRequestController::class)->only(['index', 'show']);
        Route::put('quote-requests/{quoteRequest}/status', [QuoteRequestController::class, 'updateStatus'])->name('quote-requests.update-status');
        Route::post('quote-requests/{quoteRequest}/convert', [QuoteRequestController::class, 'convertToQuote'])->name('quote-requests.convert');

        // 部署工具（僅 super-admin，Controller 內有權限檢查）
        Route::prefix('deploy')->name('deploy.')->group(function () {
            Route::get('/', [DeployController::class, 'index'])->name('index');
            Route::post('/migrate', [DeployController::class, 'migrate'])->name('migrate');
            Route::post('/seed', [DeployController::class, 'seed'])->name('seed');
            Route::post('/storage-link', [DeployController::class, 'storageLink'])->name('storage-link');
            Route::post('/optimize', [DeployController::class, 'optimize'])->name('optimize');
            Route::post('/init', [DeployController::class, 'init'])->name('init');
        });
    });
