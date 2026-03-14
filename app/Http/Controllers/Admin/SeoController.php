<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
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
        $stats = [
            'total_seo_meta' => SeoMeta::count(),
            'articles_with_seo' => Article::has('seoMeta')->count(),
            'articles_without_seo' => Article::doesntHave('seoMeta')->count(),
        ];

        return view('admin.seo.index', compact('stats'));
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
        $validated = $request->validate([
            'sitemap_enabled' => 'boolean',
            'sitemap_ping_google' => 'boolean',
            'sitemap_ping_bing' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            setting()->set('seo.' . $key, $value);
        }

        flash_success('Sitemap 設定更新成功');

        return redirect()->back();
    }

    /**
     * 顯示 Robots.txt 編輯器
     */
    public function robotsTxt(): View
    {
        $robotsPath = public_path('robots.txt');
        $content = file_exists($robotsPath) ? file_get_contents($robotsPath) : '';

        return view('admin.seo.robots-txt-new', compact('content'));
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
     * 批次生成缺少的 SEO Meta
     */
    public function generateMissingSeoMeta(): RedirectResponse
    {
        $articles = Article::doesntHave('seoMeta')->get();

        $count = 0;
        foreach ($articles as $article) {
            $article->generateSeoMeta();
            $count++;
        }

        flash_success("已為 {$count} 篇文章生成 SEO Meta");

        return redirect()->back();
    }

    /**
     * SEO 分析
     */
    public function analyze(): View
    {
        $issues = [];

        // 檢查缺少 SEO Meta 的文章
        $articlesWithoutSeo = Article::doesntHave('seoMeta')->count();
        if ($articlesWithoutSeo > 0) {
            $issues[] = [
                'severity' => 'warning',
                'title' => '缺少 SEO Meta',
                'description' => "{$articlesWithoutSeo} 篇文章缺少 SEO Meta 資料",
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
