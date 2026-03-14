# SEO 優化完整指南

本文檔詳細說明系統的 SEO 優化功能及使用方法。

## 目錄

- [SEO 功能概覽](#seo-功能概覽)
- [Meta Tags 管理](#meta-tags-管理)
- [Open Graph 與 Twitter Cards](#open-graph-與-twitter-cards)
- [Sitemap 生成](#sitemap-生成)
- [Robots.txt 管理](#robotstxt-管理)
- [Schema.org 結構化數據](#schemaorg-結構化數據)
- [URL 優化](#url-優化)
- [SEO 最佳實踐](#seo-最佳實踐)

## SEO 功能概覽

系統提供完整的 SEO 工具鏈，幫助網站提升搜尋引擎排名：

✅ **動態 Meta Tags**（標題、描述、關鍵字）
✅ **Open Graph**（社交媒體分享優化）
✅ **Twitter Cards**（Twitter 分享卡片）
✅ **XML Sitemap**（自動生成與更新）
✅ **Robots.txt**（動態管理）
✅ **Schema.org**（結構化數據）
✅ **URL 優化**（自動 Slug、Canonical URL）
✅ **301 重定向管理**

## Meta Tags 管理

### 資料表結構

```sql
CREATE TABLE seo_meta (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    model_type VARCHAR(255) NOT NULL,     -- 模型類型
    model_id BIGINT NOT NULL,              -- 模型 ID
    meta_title VARCHAR(255),               -- SEO 標題
    meta_description TEXT,                 -- SEO 描述
    meta_keywords VARCHAR(500),            -- SEO 關鍵字
    og_title VARCHAR(255),                 -- Open Graph 標題
    og_description TEXT,                   -- Open Graph 描述
    og_image VARCHAR(255),                 -- Open Graph 圖片
    twitter_title VARCHAR(255),            -- Twitter 標題
    twitter_description TEXT,              -- Twitter 描述
    twitter_image VARCHAR(255),            -- Twitter 圖片
    canonical_url VARCHAR(255),            -- 規範網址
    robots VARCHAR(100) DEFAULT 'index,follow',  -- 爬蟲指令
    schema_data JSON,                      -- Schema.org 數據
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_model (model_type, model_id),
    INDEX idx_robots (robots)
);
```

### Model 設定

**Article Model**:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Article extends Model
{
    /**
     * SEO Meta 關聯（多態關聯）
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    /**
     * 自動生成 SEO Meta
     */
    public function generateSeoMeta(): void
    {
        if (!$this->seoMeta) {
            $this->seoMeta()->create([
                'meta_title' => $this->title,
                'meta_description' => $this->generateMetaDescription(),
                'meta_keywords' => $this->generateMetaKeywords(),
                'og_title' => $this->title,
                'og_description' => $this->generateMetaDescription(),
                'og_image' => $this->featured_image,
                'canonical_url' => route('articles.show', $this),
            ]);
        }
    }

    /**
     * 生成 Meta Description
     */
    protected function generateMetaDescription(): string
    {
        if ($this->excerpt) {
            return substr($this->excerpt, 0, 160);
        }

        $content = strip_tags($this->content);
        return substr($content, 0, 160);
    }

    /**
     * 生成 Meta Keywords
     */
    protected function generateMetaKeywords(): string
    {
        $keywords = $this->tags->pluck('name')->toArray();
        return implode(', ', $keywords);
    }
}
```

### Controller 使用

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use App\Http\Requests\Seo\UpdateSeoMetaRequest;

class SeoController extends Controller
{
    /**
     * 更新 SEO Meta
     */
    public function updateMeta(UpdateSeoMetaRequest $request, Article $article)
    {
        $article->seoMeta()->updateOrCreate(
            ['model_type' => get_class($article), 'model_id' => $article->id],
            $request->validated()
        );

        return back()->with('success', 'SEO 設定已更新');
    }

    /**
     * 批量生成 SEO Meta
     */
    public function generateAllMeta()
    {
        Article::whereDoesntHave('seoMeta')->each(function ($article) {
            $article->generateSeoMeta();
        });

        return back()->with('success', '已為所有文章生成 SEO Meta');
    }
}
```

### Blade 模板使用

**layouts/admin.blade.php**:
```blade
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title>{{ $seo_title ?? $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seo_description ?? '' }}">
    <meta name="keywords" content="{{ $seo_keywords ?? '' }}">
    <meta name="robots" content="{{ $seo_robots ?? 'index,follow' }}">

    @if(isset($canonical_url))
        <link rel="canonical" href="{{ $canonical_url }}">
    @endif

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $og_title ?? $seo_title ?? $title }}">
    <meta property="og:description" content="{{ $og_description ?? $seo_description }}">
    <meta property="og:image" content="{{ $og_image ?? asset('images/default-og.jpg') }}">
    <meta property="og:url" content="{{ $canonical_url ?? url()->current() }}">
    <meta property="og:type" content="{{ $og_type ?? 'website' }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $twitter_title ?? $og_title ?? $seo_title }}">
    <meta name="twitter:description" content="{{ $twitter_description ?? $og_description ?? $seo_description }}">
    <meta name="twitter:image" content="{{ $twitter_image ?? $og_image ?? asset('images/default-og.jpg') }}">

    @if(config('services.twitter.site'))
        <meta name="twitter:site" content="@{{ config('services.twitter.site') }}">
    @endif

    <!-- Schema.org -->
    @if(isset($schema_data))
        <script type="application/ld+json">
            {!! json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
</head>
<body>
    @yield('content')
</body>
</html>
```

### SEO 表單

**admin/seo/meta.blade.php**:
```blade
<div class="card">
    <div class="card-header">
        <i class="icon-magnifier"></i> SEO 設定
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.seo.update-meta', $article) }}">
            @csrf
            @method('PUT')

            <!-- Meta Title -->
            <div class="mb-3">
                <label class="form-label">SEO 標題</label>
                <input type="text"
                       name="meta_title"
                       class="form-control @error('meta_title') is-invalid @enderror"
                       value="{{ old('meta_title', $article->seoMeta->meta_title ?? $article->title) }}"
                       maxlength="60">
                <small class="form-text text-muted">
                    建議長度：50-60 個字元（目前：<span id="title-count">0</span>）
                </small>
                @error('meta_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Meta Description -->
            <div class="mb-3">
                <label class="form-label">SEO 描述</label>
                <textarea name="meta_description"
                          class="form-control @error('meta_description') is-invalid @enderror"
                          rows="3"
                          maxlength="160">{{ old('meta_description', $article->seoMeta->meta_description ?? '') }}</textarea>
                <small class="form-text text-muted">
                    建議長度：120-160 個字元（目前：<span id="desc-count">0</span>）
                </small>
                @error('meta_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Meta Keywords -->
            <div class="mb-3">
                <label class="form-label">關鍵字</label>
                <input type="text"
                       name="meta_keywords"
                       class="form-control"
                       value="{{ old('meta_keywords', $article->seoMeta->meta_keywords ?? '') }}"
                       placeholder="關鍵字1, 關鍵字2, 關鍵字3">
                <small class="form-text text-muted">使用逗號分隔多個關鍵字</small>
            </div>

            <!-- Canonical URL -->
            <div class="mb-3">
                <label class="form-label">Canonical URL</label>
                <input type="url"
                       name="canonical_url"
                       class="form-control"
                       value="{{ old('canonical_url', $article->seoMeta->canonical_url ?? route('articles.show', $article)) }}">
                <small class="form-text text-muted">規範網址，避免重複內容</small>
            </div>

            <!-- Robots -->
            <div class="mb-3">
                <label class="form-label">Robots 指令</label>
                <select name="robots" class="form-select">
                    <option value="index,follow" {{ ($article->seoMeta->robots ?? '') == 'index,follow' ? 'selected' : '' }}>
                        index, follow（允許索引和追蹤）
                    </option>
                    <option value="noindex,follow" {{ ($article->seoMeta->robots ?? '') == 'noindex,follow' ? 'selected' : '' }}>
                        noindex, follow（不索引但追蹤）
                    </option>
                    <option value="index,nofollow" {{ ($article->seoMeta->robots ?? '') == 'index,nofollow' ? 'selected' : '' }}>
                        index, nofollow（索引但不追蹤）
                    </option>
                    <option value="noindex,nofollow" {{ ($article->seoMeta->robots ?? '') == 'noindex,nofollow' ? 'selected' : '' }}>
                        noindex, nofollow（不索引也不追蹤）
                    </option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">儲存 SEO 設定</button>
        </form>
    </div>
</div>

<script>
// 字元計數
document.querySelector('[name="meta_title"]').addEventListener('input', function() {
    document.getElementById('title-count').textContent = this.value.length;
});

document.querySelector('[name="meta_description"]').addEventListener('input', function() {
    document.getElementById('desc-count').textContent = this.value.length;
});
</script>
```

## Open Graph 與 Twitter Cards

### Open Graph 設定

Open Graph 協定讓網頁在社交媒體（Facebook、LinkedIn）上分享時顯示豐富的預覽資訊。

**必要標籤**:
- `og:title` - 標題
- `og:description` - 描述
- `og:image` - 圖片（建議尺寸：1200x630px）
- `og:url` - 網址
- `og:type` - 類型（website, article, product 等）

### Twitter Cards 設定

**卡片類型**:
- `summary` - 小圖片摘要
- `summary_large_image` - 大圖片摘要（建議）
- `app` - 應用卡片
- `player` - 影片播放器

### 測試工具

- **Facebook**: https://developers.facebook.com/tools/debug/
- **Twitter**: https://cards-dev.twitter.com/validator
- **LinkedIn**: https://www.linkedin.com/post-inspector/

## Sitemap 生成

### 安裝套件

```bash
composer require spatie/laravel-sitemap
```

### 配置

**config/sitemap.php**:
```php
<?php

return [
    /*
     * Sitemap 儲存路徑
     */
    'path' => public_path('sitemap.xml'),

    /*
     * 預設更新頻率
     */
    'default_change_frequency' => Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY,

    /*
     * 預設優先級
     */
    'default_priority' => 0.8,
];
```

### Service 實作

**app/Services/Seo/SitemapService.php**:
```php
<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Category;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function generate(): void
    {
        $sitemap = Sitemap::create();

        // 首頁
        $sitemap->add(
            Url::create(route('home'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        // 文章
        Article::published()->each(function (Article $article) use ($sitemap) {
            $sitemap->add(
                Url::create(route('articles.show', $article))
                    ->setLastModificationDate($article->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });

        // 分類
        Category::where('is_active', true)->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create(route('categories.show', $category))
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9)
            );
        });

        // 寫入文件
        $sitemap->writeToFile(public_path('sitemap.xml'));

        // 通知 Google
        $this->pingGoogle();
    }

    /**
     * 通知 Google Sitemap 已更新
     */
    protected function pingGoogle(): void
    {
        $sitemapUrl = urlencode(url('sitemap.xml'));
        file_get_contents("https://www.google.com/ping?sitemap={$sitemapUrl}");
    }
}
```

### 自動更新 Sitemap

**app/Observers/ArticleObserver.php**:
```php
<?php

namespace App\Observers;

use App\Models\Article;
use App\Jobs\GenerateSitemap;

class ArticleObserver
{
    public function updated(Article $article): void
    {
        // 如果狀態改為已發布，重新生成 sitemap
        if ($article->wasChanged('status') && $article->status === 'published') {
            GenerateSitemap::dispatch()->delay(now()->addMinutes(5));
        }
    }

    public function deleted(Article $article): void
    {
        // 刪除文章後重新生成 sitemap
        GenerateSitemap::dispatch()->delay(now()->addMinutes(5));
    }
}
```

### 排程任務

**app/Console/Kernel.php**:
```php
<?php

protected function schedule(Schedule $schedule)
{
    // 每天凌晨 2 點生成 sitemap
    $schedule->job(new \App\Jobs\GenerateSitemap())->dailyAt('02:00');
}
```

## Robots.txt 管理

### 動態 Robots.txt

**routes/web.php**:
```php
Route::get('/robots.txt', function () {
    $robots = Setting::get('seo.robots_txt', view('seo.robots-default')->render());

    return response($robots)
        ->header('Content-Type', 'text/plain');
});
```

### 預設 Robots.txt

**resources/views/seo/robots-default.blade.php**:
```
User-agent: *
Allow: /

# Sitemap
Sitemap: {{ url('sitemap.xml') }}

# 不允許爬取的目錄
Disallow: /admin/
Disallow: /api/
Disallow: /vendor/
Disallow: /storage/

# 爬取延遲（秒）
Crawl-delay: 10
```

### 環境區分

**生產環境**:
```
User-agent: *
Allow: /
Sitemap: https://example.com/sitemap.xml
```

**開發環境**:
```
User-agent: *
Disallow: /
```

## Schema.org 結構化數據

### Article Schema

```php
<?php

namespace App\Services\Seo;

class SchemaOrgService
{
    /**
     * 生成文章 Schema
     */
    public function generateArticleSchema(Article $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt,
            'image' => [
                '@type' => 'ImageObject',
                'url' => asset($article->featured_image),
            ],
            'datePublished' => $article->published_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->author->name,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
        ];
    }

    /**
     * 生成麵包屑 Schema
     */
    public function generateBreadcrumbSchema(array $items): array
    {
        $itemListElements = [];

        foreach ($items as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElements,
        ];
    }
}
```

## URL 優化

### 自動 Slug 生成

```php
<?php

namespace App\Models;

use Illuminate\Support\Str;

class Article extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);

                // 確保唯一性
                $count = 1;
                while (static::where('slug', $article->slug)->exists()) {
                    $article->slug = Str::slug($article->title) . '-' . $count;
                    $count++;
                }
            }
        });
    }
}
```

### Canonical URL

避免重複內容問題，設定規範網址：

```blade
<link rel="canonical" href="{{ $canonical_url ?? url()->current() }}">
```

### 301 重定向

當 URL 結構變更時，設定 301 永久重定向：

```php
Route::get('/old-url', function () {
    return redirect('/new-url', 301);
});
```

## SEO 最佳實踐

### 1. Title 優化

- **長度**: 50-60 個字元
- **格式**: `主要關鍵字 - 次要關鍵字 | 網站名稱`
- **避免**: 關鍵字堆疊

### 2. Description 優化

- **長度**: 120-160 個字元
- **內容**: 包含主要關鍵字，吸引用戶點擊
- **避免**: 重複內容

### 3. 圖片優化

- **檔名**: 使用描述性名稱（如 `laravel-tutorial.jpg`）
- **Alt 文字**: 描述圖片內容
- **尺寸**: 壓縮圖片，使用 WebP 格式

### 4. 內部連結

- 使用描述性錨文本
- 避免過多連結
- 確保連結有效

### 5. 移動友好

- 響應式設計
- 快速載入速度
- 易於點擊的按鈕

### 6. 頁面速度

- 壓縮 CSS/JS
- 使用 CDN
- 啟用瀏覽器快取
- 優化圖片

### 7. HTTPS

- 使用 SSL 憑證
- 強制 HTTPS
- 更新內部連結

---

**相關資源**:
- [Google 搜尋中心](https://developers.google.com/search)
- [Schema.org](https://schema.org/)
- [Open Graph Protocol](https://ogp.me/)
- [Twitter Cards](https://developer.twitter.com/en/docs/twitter-for-websites/cards/)

**版本**: 1.0
**最後更新**: 2024年1月
