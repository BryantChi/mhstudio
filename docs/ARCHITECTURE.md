# 系統架構設計

## 目錄

- [整體架構](#整體架構)
- [分層設計](#分層設計)
- [目錄結構詳解](#目錄結構詳解)
- [設計模式](#設計模式)
- [核心組件](#核心組件)
- [資料流程](#資料流程)

## 整體架構

本系統採用經典的 **MVC（Model-View-Controller）** 架構，結合現代化的設計模式，確保系統的可維護性和可擴展性。

### 架構圖

```
┌─────────────────────────────────────────────────────────────┐
│                      用戶瀏覽器                               │
│                   (Browser / Client)                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     路由層 (Routes)                          │
│          web.php / admin.php / api.php                      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                  中間件層 (Middleware)                        │
│    Authentication / Authorization / Logging / CSRF          │
└─────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                  控制器層 (Controllers)                       │
│           處理 HTTP 請求、驗證、回應                          │
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ↓               ↓               ↓
        ┌─────────┐    ┌──────────┐    ┌─────────┐
        │ Actions │    │ Services │    │  Repos  │
        └─────────┘    └──────────┘    └─────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    模型層 (Models)                           │
│              Eloquent ORM / Business Logic                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                   資料庫層 (Database)                         │
│            MySQL / Redis / Cache                            │
└─────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                  視圖層 (Views - Blade)                      │
│          HTML + CoreUI + Bootstrap + JavaScript             │
└─────────────────────────────────────────────────────────────┘
```

## 分層設計

### 1. 表現層（Presentation Layer）

**職責**: 處理用戶界面展示和用戶交互

**組成**:
- **Views (Blade 模板)**: 負責 HTML 渲染
- **CoreUI 組件**: 提供一致的 UI 體驗
- **JavaScript (app.js)**: 處理前端交互邏輯

**位置**: `resources/views/`

### 2. 控制層（Controller Layer）

**職責**: 處理 HTTP 請求、路由分發、調用業務邏輯

**組成**:
- **Controllers**: HTTP 請求處理器
- **Form Requests**: 請求驗證
- **Middleware**: 請求過濾與處理

**位置**: `app/Http/Controllers/`

**範例**:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Actions\Article\CreateArticleAction;
use App\Models\Article;

class ArticleController extends Controller
{
    public function store(
        StoreArticleRequest $request,
        CreateArticleAction $action
    ) {
        $article = $action->execute($request->validated());

        return redirect()
            ->route('admin.articles.index')
            ->with('success', '文章創建成功');
    }
}
```

### 3. 業務邏輯層（Business Logic Layer）

**職責**: 實現具體的業務規則和邏輯

**組成**:
- **Actions**: 單一職責的業務操作
- **Services**: 複雜的業務邏輯服務
- **Repositories**: 數據訪問抽象層

**位置**: `app/Actions/`, `app/Services/`, `app/Repositories/`

#### Actions（動作模式）

單一職責的業務操作，每個 Action 類只做一件事。

**範例**:
```php
<?php

namespace App\Actions\Article;

use App\Models\Article;
use App\Events\ArticlePublished;

class PublishArticleAction
{
    public function execute(Article $article): Article
    {
        $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 自動生成 SEO 資料
        if (empty($article->meta_description)) {
            $article->generateSeoMeta();
        }

        // 觸發事件
        event(new ArticlePublished($article));

        return $article->fresh();
    }
}
```

#### Services（服務模式）

處理複雜的業務邏輯，可能涉及多個模型和第三方服務。

**範例**:
```php
<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getStatistics(): array
    {
        return Cache::remember('dashboard.stats', 600, function () {
            return [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
                ],
                'articles' => [
                    'total' => Article::count(),
                    'published' => Article::where('status', 'published')->count(),
                    'draft' => Article::where('status', 'draft')->count(),
                ],
            ];
        });
    }
}
```

#### Repositories（儲存庫模式）

數據訪問層的抽象，隔離具體的資料庫操作。

**範例**:
```php
<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Article::with(['author', 'category', 'tags'])
            ->latest()
            ->paginate($perPage);
    }

    public function findPublished(int $id): ?Article
    {
        return Article::published()
            ->with(['author', 'category', 'tags'])
            ->find($id);
    }
}
```

### 4. 數據層（Data Layer）

**職責**: 數據持久化和數據模型定義

**組成**:
- **Models (Eloquent)**: ORM 模型
- **Migrations**: 資料庫結構定義
- **Seeders**: 測試數據填充

**位置**: `app/Models/`, `database/migrations/`

### 5. 基礎設施層（Infrastructure Layer）

**職責**: 提供系統基礎服務

**組成**:
- **Cache (Redis)**: 快取服務
- **Queue**: 佇列處理
- **Events & Listeners**: 事件驅動
- **Logging**: 日誌記錄

## 目錄結構詳解

### `app/` 目錄

```
app/
├── Actions/                    # 業務動作類
│   ├── Auth/
│   │   ├── LoginAction.php
│   │   └── RegisterAction.php
│   ├── User/
│   │   ├── CreateUserAction.php
│   │   ├── UpdateUserAction.php
│   │   └── DeleteUserAction.php
│   ├── Article/
│   │   ├── CreateArticleAction.php
│   │   ├── PublishArticleAction.php
│   │   └── GenerateSeoAction.php
│   └── Analytics/
│       └── TrackEventAction.php
│
├── Events/                     # 事件類
│   ├── UserCreated.php
│   ├── ArticlePublished.php
│   └── SeoMetaUpdated.php
│
├── Listeners/                  # 事件監聽器
│   ├── SendWelcomeEmail.php
│   ├── UpdateSitemap.php
│   └── ClearCache.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # 後台控制器
│   │   │   ├── DashboardController.php
│   │   │   ├── UserController.php
│   │   │   ├── ArticleController.php
│   │   │   ├── SeoController.php
│   │   │   └── AnalyticsController.php
│   │   └── Auth/
│   │       └── LoginController.php
│   │
│   ├── Middleware/           # 中間件
│   │   ├── CheckPermission.php
│   │   ├── LogActivity.php
│   │   └── InjectSeoData.php
│   │
│   └── Requests/             # 表單請求驗證
│       ├── User/
│       │   ├── StoreUserRequest.php
│       │   └── UpdateUserRequest.php
│       ├── Article/
│       │   ├── StoreArticleRequest.php
│       │   └── UpdateArticleRequest.php
│       └── Seo/
│           └── UpdateSeoMetaRequest.php
│
├── Models/                    # Eloquent 模型
│   ├── User.php
│   ├── Article.php
│   ├── Category.php
│   ├── Tag.php
│   ├── Media.php
│   ├── Setting.php
│   ├── SeoMeta.php
│   └── AnalyticsEvent.php
│
├── Policies/                  # 授權策略
│   ├── UserPolicy.php
│   ├── ArticlePolicy.php
│   └── SettingPolicy.php
│
├── Services/                  # 服務類
│   ├── Dashboard/
│   │   └── DashboardService.php
│   ├── Seo/
│   │   ├── SeoService.php
│   │   ├── SitemapService.php
│   │   └── SchemaOrgService.php
│   ├── Analytics/
│   │   ├── GoogleAnalyticsService.php
│   │   └── AnalyticsReportService.php
│   └── Cache/
│       └── CacheService.php
│
└── View/Components/           # Blade 組件
    ├── Alert.php
    ├── Card.php
    ├── Table.php
    └── Chart.php
```

### `resources/` 目錄

```
resources/
├── views/
│   ├── layouts/              # 布局模板
│   │   ├── admin.blade.php
│   │   ├── auth.blade.php
│   │   └── partials/
│   │       ├── sidebar.blade.php
│   │       ├── header.blade.php
│   │       ├── footer.blade.php
│   │       └── breadcrumb.blade.php
│   │
│   ├── admin/               # 後台頁面
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── users/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── articles/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── seo/
│   │   │   ├── meta.blade.php
│   │   │   └── sitemap.blade.php
│   │   └── analytics/
│   │       └── dashboard.blade.php
│   │
│   ├── components/          # Blade 組件視圖
│   │   ├── alert.blade.php
│   │   ├── card.blade.php
│   │   └── table.blade.php
│   │
│   └── auth/
│       └── login.blade.php
│
├── css/
│   └── app.css              # 自定義樣式
│
└── js/
    ├── app.js               # 主要 JavaScript
    ├── bootstrap.js
    ├── chart-config.js
    └── analytics.js
```

## 設計模式

### 1. Repository 模式

**目的**: 在業務邏輯和數據訪問層之間提供抽象。

**優勢**:
- 解耦業務邏輯和數據存儲
- 易於測試（可 Mock）
- 集中管理數據訪問邏輯

**實作範例**:
```php
<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepository
{
    protected Article $model;

    public function __construct(Article $model)
    {
        $this->model = $model;
    }

    public function findWithRelations(int $id): ?Article
    {
        return $this->model
            ->with(['author', 'category', 'tags', 'seoMeta'])
            ->find($id);
    }

    public function getPublishedPaginated(int $perPage = 15)
    {
        return $this->model
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate($perPage);
    }
}
```

### 2. Action 模式

**目的**: 將複雜的業務邏輯封裝成單一職責的可執行類。

**優勢**:
- 單一職責原則
- 易於測試
- 可重用性高
- 代碼組織清晰

**實作範例**:
```php
<?php

namespace App\Actions\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateArticleAction
{
    public function execute(array $data, User $author): Article
    {
        return DB::transaction(function () use ($data, $author) {
            // 創建文章
            $article = Article::create([
                'user_id' => $author->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
            ]);

            // 同步標籤
            if (isset($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }

            // 創建 SEO Meta
            if (isset($data['seo'])) {
                $article->seoMeta()->create($data['seo']);
            }

            return $article;
        });
    }
}
```

### 3. Service 模式

**目的**: 處理複雜的業務邏輯，協調多個資源。

**優勢**:
- 業務邏輯集中管理
- 可處理跨模型的複雜操作
- 易於維護和擴展

**實作範例**:
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

        // 添加文章
        Article::published()->each(function (Article $article) use ($sitemap) {
            $sitemap->add(
                Url::create(route('articles.show', $article))
                    ->setLastModificationDate($article->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });

        // 添加分類
        Category::active()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create(route('categories.show', $category))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.9)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
```

### 4. Observer 模式

**目的**: 在模型事件發生時自動執行邏輯。

**優勢**:
- 自動化處理
- 保持模型邏輯清晰
- 易於維護

**實作範例**:
```php
<?php

namespace App\Observers;

use App\Models\Article;
use App\Jobs\GenerateSitemap;
use Illuminate\Support\Str;

class ArticleObserver
{
    public function creating(Article $article): void
    {
        // 自動生成 slug
        if (empty($article->slug)) {
            $article->slug = Str::slug($article->title);
        }
    }

    public function updated(Article $article): void
    {
        // 如果狀態改為已發布，重新生成 sitemap
        if ($article->wasChanged('status') && $article->status === 'published') {
            GenerateSitemap::dispatch();
        }
    }
}
```

## 核心組件

### 1. 認證系統

**實作**: Laravel Breeze + Spatie Permission

**組成**:
- 登入/登出
- 密碼重置
- 記住我功能
- Session 管理

### 2. 權限系統

**實作**: Spatie Laravel Permission

**組成**:
- 角色（Roles）
- 權限（Permissions）
- 權限檢查（Gates、Policies）

### 3. SEO 系統

**組成**:
- Meta Tags 管理
- Sitemap 生成
- Schema.org 結構化數據
- Robots.txt 管理

### 4. Analytics 系統

**實作**: Spatie Laravel Analytics

**組成**:
- GA4 整合
- 事件追蹤
- 報表生成
- 即時數據

### 5. 快取系統

**實作**: Redis（可選）+ File Cache

**策略**:
- 查詢結果快取
- 配置快取
- 視圖快取
- 路由快取

## 資料流程

### 用戶請求流程

```
1. 用戶發起 HTTP 請求
   ↓
2. Nginx/Apache 接收請求
   ↓
3. Laravel 路由匹配
   ↓
4. 中間件處理（認證、權限、日誌）
   ↓
5. Controller 接收請求
   ↓
6. Form Request 驗證
   ↓
7. 調用 Action/Service 執行業務邏輯
   ↓
8. Repository 查詢資料庫
   ↓
9. Model 返回數據
   ↓
10. Controller 處理數據並返回 View
    ↓
11. Blade 引擎編譯模板
    ↓
12. 返回 HTML 給用戶
```

### 文章發布流程

```
1. 用戶提交文章表單
   ↓
2. StoreArticleRequest 驗證數據
   ↓
3. CreateArticleAction 創建文章
   ↓
4. ArticleObserver 自動生成 slug
   ↓
5. 同步標籤關聯
   ↓
6. 創建 SEO Meta
   ↓
7. 觸發 ArticlePublished 事件
   ↓
8. UpdateSitemap Listener 更新 sitemap
   ↓
9. ClearCache Listener 清除快取
   ↓
10. 返回成功響應
```

## 可擴展性考量

### 1. 插件系統（未來）

預留插件接口，允許第三方擴展功能。

### 2. 多語言支持（未來）

使用 Laravel Localization，支持多語言內容。

### 3. API 版本控制（未來）

`/api/v1/`, `/api/v2/` 分版本管理 API。

### 4. 微服務拆分（未來）

可將 Analytics、Media 等模組拆分為獨立服務。

## 安全性設計

### 1. 認證與授權

- Session-based Authentication
- RBAC 權限控制
- Policy 授權策略

### 2. CSRF 保護

- 所有 POST/PUT/DELETE 請求自動驗證 CSRF Token

### 3. XSS 防護

- Blade 模板自動轉義輸出
- 使用 `{{ }}` 而非 `{!! !!}`

### 4. SQL 注入防護

- Eloquent ORM 參數綁定
- Query Builder 自動轉義

### 5. 速率限制

- Login: 5次/15分鐘
- API: 60次/分鐘

## 效能優化

### 1. 資料庫優化

- 索引設計
- Eager Loading（避免 N+1）
- 查詢快取

### 2. 快取策略

- Redis 快取熱點數據
- 配置快取
- 視圖快取

### 3. 前端優化

- Vite 建構優化
- 資源壓縮
- CDN 加速

## 監控與日誌

### 1. 應用日誌

- Laravel Log（`storage/logs/`）
- 日誌分級（debug, info, warning, error）

### 2. 操作日誌

- Spatie Activity Log
- 記錄所有 CRUD 操作

### 3. 效能監控

- Laravel Telescope（開發環境）
- 查詢時間監控
- 記憶體使用監控

---

**版本**: 1.0
**最後更新**: 2024年1月
