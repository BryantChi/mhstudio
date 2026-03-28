<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use App\Models\Service;
use App\Models\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;

class SeoController extends Controller
{
    /**
     * 顯示 SEO 總覽
     */
    public function index(): View
    {
        $totalMeta = SeoMeta::count();
        $articlesWithSeo = Article::published()->has('seoMeta')->count();
        $articlesWithoutSeo = Article::published()->doesntHave('seoMeta')->count();
        $projectsWithoutSeo = Project::published()->doesntHave('seoMeta')->count();
        $servicesWithoutSeo = Service::active()->doesntHave('seoMeta')->count();
        $totalWithoutSeo = $articlesWithoutSeo + $projectsWithoutSeo + $servicesWithoutSeo;
        $totalArticles = $articlesWithSeo + $articlesWithoutSeo;

        // 計算 robots.txt 規則數
        $robotsPath = public_path('robots.txt');
        $robotsRules = 0;
        if (file_exists($robotsPath)) {
            $lines = file($robotsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $robotsRules = count(array_filter($lines, fn ($l) => !str_starts_with(trim($l), '#')));
        }

        // 計算 sitemap URL 數
        $sitemapPath = public_path('sitemap.xml');
        $sitemapUrls = 0;
        if (file_exists($sitemapPath)) {
            $sitemapUrls = substr_count(file_get_contents($sitemapPath), '<url>');
            if ($sitemapUrls === 0) {
                $sitemapUrls = substr_count(file_get_contents($sitemapPath), '<loc>');
            }
        }

        // Meta 覆蓋率
        $metaCoverage = $totalArticles > 0
            ? round($articlesWithSeo / $totalArticles * 100)
            : 0;

        // SEO 評分（簡易計算）
        $seoScore = min(100, (int) (
            ($metaCoverage * 0.4) +
            ($sitemapUrls > 0 ? 20 : 0) +
            ($robotsRules > 0 ? 20 : 0) +
            ($totalMeta > 0 ? 20 : 0)
        ));

        $stats = [
            'total_meta' => $totalMeta,
            'sitemap_urls' => $sitemapUrls,
            'robots_rules' => $robotsRules,
            'seo_score' => $seoScore,
            'articles_with_seo' => $articlesWithSeo,
            'articles_without_seo' => $articlesWithoutSeo,
        ];

        $health = [
            'meta_coverage' => $metaCoverage,
            'image_alt' => 0,       // 未來實作
            'internal_links' => 0,  // 未來實作
            'page_speed' => 0,      // 未來實作
        ];

        return view('admin.seo.index', compact('stats', 'health'));
    }

    /**
     * 顯示 SEO Meta 管理
     */
    public function meta(Request $request): View
    {
        $query = SeoMeta::with('model');

        // 類型篩選
        if ($request->filled('type')) {
            $query->where('model_type', $request->type);
        }

        // 狀態篩選
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'complete') {
                $query->whereNotNull('meta_title')
                      ->where('meta_title', '!=', '')
                      ->whereNotNull('meta_description')
                      ->where('meta_description', '!=', '');
            } elseif ($status === 'incomplete') {
                $query->where(function ($q) {
                    $q->whereNull('meta_description')
                      ->orWhere('meta_description', '')
                      ->orWhereNull('og_title')
                      ->orWhere('og_title', '');
                })->whereNotNull('meta_title')->where('meta_title', '!=', '');
            } elseif ($status === 'missing') {
                $query->where(function ($q) {
                    $q->whereNull('meta_title')
                      ->orWhere('meta_title', '');
                });
            }
        }

        $seoMetas = $query->latest()->paginate(15);

        return view('admin.seo.meta', compact('seoMetas'));
    }

    /**
     * 編輯 SEO Meta
     */
    public function editMeta(SeoMeta $seoMeta): View
    {
        $seoMeta->load('model');
        return view('admin.seo.edit-meta', compact('seoMeta'));
    }

    /**
     * 更新 SEO Meta
     */
    public function updateMeta(Request $request, SeoMeta $seoMeta): RedirectResponse
    {
        $validated = $request->validate([
            'meta_title' => 'required|string|max:255',
            'meta_description' => 'required|string',
            'meta_keywords' => 'nullable|string',
            'meta_robots' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'og_title' => 'nullable|string',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string',
            'og_type' => 'nullable|string',
            'twitter_card' => 'nullable|string',
            'twitter_title' => 'nullable|string',
            'twitter_description' => 'nullable|string',
            'twitter_image' => 'nullable|string',
        ]);

        $seoMeta->update($validated);

        flash_success('SEO Meta 更新成功');

        return redirect()->route('admin.seo.meta');
    }

    /**
     * 生成 Sitemap
     */
    public function generateSitemap(): RedirectResponse
    {
        try {
            Artisan::call('sitemap:generate');

            flash_success('Sitemap 生成成功');
        } catch (\Exception $e) {
            flash_error('Sitemap 生成失敗: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * 顯示 Sitemap 設定
     */
    public function sitemapSettings(): View
    {
        return view('admin.seo.sitemap-settings');
    }

    /**
     * 更新 Sitemap 設定
     */
    public function updateSitemapSettings(Request $request): RedirectResponse
    {
        // 包含內容（checkbox 未勾選不會送出，預設 0）
        $includes = ['include_articles', 'include_projects', 'include_services', 'include_legal'];
        foreach ($includes as $key) {
            setting()->set('sitemap_' . $key, $request->boolean($key) ? '1' : '0');
        }

        // 優先順序
        $priorities = ['priority_home', 'priority_articles', 'priority_projects', 'priority_services'];
        foreach ($priorities as $key) {
            if ($request->filled($key)) {
                setting()->set('sitemap_' . $key, $request->input($key));
            }
        }

        flash_success('Sitemap 設定已儲存');

        return redirect()->back();
    }

    /**
     * 顯示 Robots.txt 編輯器
     */
    public function robotsTxt(): View
    {
        $robotsPath = public_path('robots.txt');
        $content = file_exists($robotsPath) ? file_get_contents($robotsPath) : '';

        return view('admin.seo.robots-txt-simple', compact('content'));
    }

    /**
     * 更新 Robots.txt
     */
    public function updateRobotsTxt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $robotsPath = public_path('robots.txt');

        try {
            // 記錄接收到的內容（用於除錯）
            \Log::info('Robots.txt 更新請求', [
                'content_length' => strlen($validated['content']),
                'content' => $validated['content'],
            ]);

            $result = file_put_contents($robotsPath, $validated['content']);

            if ($result === false) {
                flash_error('Robots.txt 寫入失敗，請檢查檔案權限');
                return redirect()->back();
            }

            // 驗證寫入結果
            $writtenContent = file_get_contents($robotsPath);
            $linesWritten = substr_count($validated['content'], "\n") + 1;

            flash_success('Robots.txt 更新成功（已寫入 ' . $result . ' 位元組，共 ' . $linesWritten . ' 行）');
        } catch (\Exception $e) {
            flash_error('Robots.txt 更新失敗：' . $e->getMessage());
            \Log::error('Robots.txt 更新失敗', ['error' => $e->getMessage()]);
            return redirect()->back();
        }

        return redirect()->back();
    }

    /**
     * 批次生成缺少的 SEO Meta（文章 + 作品 + 服務）
     */
    public function generateMissingSeoMeta(): RedirectResponse
    {
        $results = [];

        // 文章
        $articles = Article::published()->doesntHave('seoMeta')->get();
        foreach ($articles as $article) {
            $article->generateSeoMeta();
        }
        if ($articles->count() > 0) {
            $results[] = "文章 {$articles->count()} 篇";
        }

        // 作品
        $projects = Project::published()->doesntHave('seoMeta')->get();
        foreach ($projects as $project) {
            $project->generateSeoMeta();
        }
        if ($projects->count() > 0) {
            $results[] = "作品 {$projects->count()} 個";
        }

        // 服務
        $services = Service::active()->doesntHave('seoMeta')->get();
        foreach ($services as $service) {
            $service->generateSeoMeta();
        }
        if ($services->count() > 0) {
            $results[] = "服務 {$services->count()} 個";
        }

        $total = $articles->count() + $projects->count() + $services->count();
        if ($total > 0) {
            flash_success("已生成 SEO Meta：" . implode('、', $results));
        } else {
            flash_info('所有已發布內容皆已有 SEO Meta，無需生成');
        }

        return redirect()->back();
    }

    /**
     * 重新生成全部 SEO Meta（覆蓋現有）
     */
    public function regenerateAllSeoMeta(): RedirectResponse
    {
        $results  = [];
        $errors   = [];

        // 文章
        $articles = Article::published()->with('seoMeta', 'tags')->get();
        $articleOk = 0;
        foreach ($articles as $article) {
            try {
                $article->generateSeoMeta(true);
                $articleOk++;
            } catch (\Throwable $e) {
                $errors[] = "文章 [{$article->title}]：{$e->getMessage()}";
                \Log::error('generateSeoMeta 失敗 (Article)', [
                    'id'      => $article->id,
                    'title'   => $article->title,
                    'error'   => $e->getMessage(),
                    'sqlstate' => $e instanceof \Illuminate\Database\QueryException ? $e->errorInfo : null,
                ]);
            }
        }
        if ($articleOk > 0) {
            $results[] = "文章 {$articleOk} 篇";
        }

        // 作品
        $projects = Project::published()->with('seoMeta')->get();
        $projectOk = 0;
        foreach ($projects as $project) {
            try {
                $project->generateSeoMeta(true);
                $projectOk++;
            } catch (\Throwable $e) {
                $errors[] = "作品 [{$project->title}]：{$e->getMessage()}";
                \Log::error('generateSeoMeta 失敗 (Project)', [
                    'id'      => $project->id,
                    'title'   => $project->title,
                    'error'   => $e->getMessage(),
                    'sqlstate' => $e instanceof \Illuminate\Database\QueryException ? $e->errorInfo : null,
                ]);
            }
        }
        if ($projectOk > 0) {
            $results[] = "作品 {$projectOk} 個";
        }

        // 服務
        $services = Service::active()->with('seoMeta')->get();
        $serviceOk = 0;
        foreach ($services as $service) {
            try {
                $service->generateSeoMeta(true);
                $serviceOk++;
            } catch (\Throwable $e) {
                $errors[] = "服務 [{$service->title}]：{$e->getMessage()}";
                \Log::error('generateSeoMeta 失敗 (Service)', [
                    'id'      => $service->id,
                    'title'   => $service->title,
                    'error'   => $e->getMessage(),
                    'sqlstate' => $e instanceof \Illuminate\Database\QueryException ? $e->errorInfo : null,
                ]);
            }
        }
        if ($serviceOk > 0) {
            $results[] = "服務 {$serviceOk} 個";
        }

        $total = $articleOk + $projectOk + $serviceOk;
        if (!empty($errors)) {
            $firstError = $errors[0];
            flash_error('部分項目生成失敗（共 ' . count($errors) . ' 個），請查看 Log。第一個錯誤：' . $firstError);
        } elseif ($total > 0) {
            flash_success("已重新生成全部 SEO Meta：" . implode('、', $results));
        } else {
            flash_info('沒有已發布的內容需要生成');
        }

        return redirect()->back();
    }

    /**
     * SEO 分析
     */
    public function analyze(): View
    {
        $issues = [];

        // 檢查缺少 SEO Meta 的內容
        $missingArticles = Article::published()->doesntHave('seoMeta')->count();
        $missingProjects = Project::published()->doesntHave('seoMeta')->count();
        $missingServices = Service::active()->doesntHave('seoMeta')->count();
        $totalMissing = $missingArticles + $missingProjects + $missingServices;

        if ($totalMissing > 0) {
            $details = [];
            if ($missingArticles > 0) $details[] = "文章 {$missingArticles} 篇";
            if ($missingProjects > 0) $details[] = "作品 {$missingProjects} 個";
            if ($missingServices > 0) $details[] = "服務 {$missingServices} 個";

            $issues[] = [
                'severity' => 'warning',
                'title' => '缺少 SEO Meta',
                'description' => implode('、', $details) . '缺少 SEO Meta 資料',
                'action' => route('admin.seo.generate-missing'),
            ];
        }

        // 檢查 Meta Title 長度問題
        $invalidTitles = SeoMeta::whereNotNull('meta_title')
            ->get()
            ->filter(function ($seo) {
                $validation = validate_meta_title($seo->meta_title);
                return !$validation['is_optimal'];
            });

        if ($invalidTitles->count() > 0) {
            $issues[] = [
                'severity' => 'info',
                'title' => 'Meta Title 長度問題',
                'description' => "{$invalidTitles->count()} 個 Meta Title 長度不理想",
            ];
        }

        return view('admin.seo.analyze', compact('issues'));
    }
}
