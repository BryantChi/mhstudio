# Laravel 11 + Bootstrap 5 + CoreUI 通用後台管理系統

一個功能完整、可擴展、易維護的通用後台管理系統，特別針對內容管理和 SEO 優化設計。

## 📋 技術棧

- **後端框架**: Laravel 11.x + PHP 8.2
- **前端框架**: Blade 模板引擎 + Bootstrap 5.3
- **UI 模板**: CoreUI Free Bootstrap Admin Template
- **資料庫**: MySQL 8.0（預留 Redis 支援）
- **建構工具**: Vite 5.x
- **SEO 優化**: Meta Tags、Sitemap、Schema.org
- **數據分析**: Google Analytics 4 整合
- **測試框架**: Pest PHP
- **代碼品質**: Laravel Pint + PHPStan

## ✨ 核心功能

### 1. 用戶與權限管理（RBAC）
- ✅ 完整的用戶管理（CRUD）
- ✅ 角色管理（super-admin、admin、editor、author）
- ✅ 細粒度權限控制
- ✅ 活動日誌追蹤
- ✅ 登入歷史記錄

### 2. 內容管理系統（CMS）
- ✅ 文章管理（草稿/發布/排程/封存）
- ✅ 富文本編輯器（TinyMCE 6）
- ✅ 分類管理（支援巢狀分類）
- ✅ 標籤系統
- ✅ 媒體庫（圖片/文件管理）
- ✅ 特色圖片上傳
- ✅ 瀏覽次數統計

### 3. SEO 優化模組
- ✅ 動態 Meta Tags 管理
- ✅ Open Graph（Facebook）
- ✅ Twitter Cards
- ✅ XML Sitemap 自動生成
- ✅ Robots.txt 動態管理
- ✅ Schema.org 結構化數據
- ✅ 自動 Slug 生成
- ✅ Canonical URL 管理
- ✅ 301 重定向管理

### 4. Google Analytics 整合
- ✅ GA4 深度整合
- ✅ 即時訪客數據
- ✅ 事件追蹤系統
- ✅ 轉換漏斗分析
- ✅ 自定義報表
- ✅ 流量來源分析
- ✅ 設備與瀏覽器統計

### 5. 數據分析儀表板
- ✅ 統計卡片（用戶、文章、瀏覽量）
- ✅ Chart.js 互動圖表
- ✅ 訪客趨勢分析
- ✅ 熱門內容排行
- ✅ 最近活動時間軸
- ✅ SEO 健康度評分

### 6. 系統配置與日誌
- ✅ 動態配置管理
- ✅ 分組配置系統
- ✅ 操作日誌記錄
- ✅ 系統錯誤追蹤
- ✅ 登入歷史
- ✅ SEO 變更追蹤

## 🚀 快速開始

### 系統需求

- PHP >= 8.2
- Composer >= 2.5
- Node.js >= 18.x
- MySQL >= 8.0
- Redis >= 7.0（可選）

### 安裝步驟

#### 1. 克隆專案

```bash
git clone https://github.com/your-repo/admin-system.git
cd admin-system
```

#### 2. 安裝依賴

```bash
# 安裝 Composer 依賴
composer install

# 安裝 NPM 依賴
npm install
```

#### 3. 環境配置

```bash
# 複製環境變數文件
cp .env.example .env

# 生成應用密鑰
php artisan key:generate
```

編輯 `.env` 文件，配置資料庫：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Redis（可選）
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Google Analytics
ANALYTICS_VIEW_ID=your_view_id
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account.json
```

#### 4. 資料庫遷移與種子數據

```bash
# 執行遷移
php artisan migrate

# 填充種子數據
php artisan db:seed
```

#### 5. 編譯前端資源

```bash
# 開發模式
npm run dev

# 生產模式
npm run build
```

#### 6. 啟動開發伺服器

```bash
php artisan serve
```

訪問 `http://localhost:8000/admin`

### 預設帳號

- **Email**: admin@example.com
- **Password**: password

## 📁 目錄結構

```
admin/
├── app/
│   ├── Actions/              # 業務邏輯 Actions
│   ├── Http/
│   │   ├── Controllers/Admin/  # 後台控制器
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/               # Eloquent 模型
│   ├── Services/             # 服務層
│   │   ├── Seo/             # SEO 服務
│   │   ├── Analytics/       # GA 分析服務
│   │   └── Dashboard/       # 儀表板服務
│   └── View/Components/      # Blade 組件
│
├── resources/
│   ├── views/
│   │   ├── layouts/          # 布局模板
│   │   ├── admin/           # 後台頁面
│   │   └── components/      # UI 組件
│   ├── css/
│   └── js/
│
├── database/
│   ├── migrations/          # 資料庫遷移
│   └── seeders/             # 種子數據
│
├── routes/
│   ├── web.php
│   └── admin.php            # 後台路由
│
├── public/
│   ├── assets/              # CoreUI 資源
│   └── uploads/             # 上傳文件
│
└── docs/                    # 技術文檔
    ├── ARCHITECTURE.md
    ├── DATABASE.md
    ├── SEO.md
    ├── ANALYTICS.md
    └── DEPLOYMENT.md
```

## 📚 文檔

詳細文檔請參閱 `/docs` 目錄：

- [系統架構設計](docs/ARCHITECTURE.md) - 完整的架構說明
- [資料庫設計](docs/DATABASE.md) - 資料表結構與關聯
- [SEO 優化指南](docs/SEO.md) - SEO 功能詳解
- [Google Analytics 整合](docs/ANALYTICS.md) - GA4 設定與使用
- [部署指南](docs/DEPLOYMENT.md) - Docker 與 CI/CD 配置
- [開發路線圖](docs/ROADMAP.md) - 10週開發計劃

## 🔧 開發工具

### 代碼格式化

```bash
# 使用 Laravel Pint 格式化程式碼
./vendor/bin/pint
```

### 靜態分析

```bash
# 使用 PHPStan 進行靜態分析
./vendor/bin/phpstan analyse
```

### 測試

```bash
# 執行所有測試
php artisan test

# 執行特定測試
php artisan test --filter UserTest
```

### 清除快取

```bash
# 清除所有快取
php artisan optimize:clear

# 或分別清除
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🐳 Docker 部署

使用 Docker Compose 快速部署：

```bash
# 啟動容器
docker-compose up -d

# 進入容器
docker-compose exec app bash

# 執行遷移
docker-compose exec app php artisan migrate

# 停止容器
docker-compose down
```

## 🔐 安全性

### 權限檢查

**控制器層**:
```php
// 使用 Policy
$this->authorize('update', $article);

// 使用中間件
Route::middleware('can:edit articles')->group(function () {
    // 路由定義
});
```

**視圖層**:
```blade
@can('edit', $article)
    <a href="{{ route('admin.articles.edit', $article) }}">編輯</a>
@endcan

@role('admin')
    <div class="admin-panel">管理員面板</div>
@endrole
```

### CSRF 保護

所有表單自動包含 CSRF 令牌：

```blade
<form method="POST" action="{{ route('admin.articles.store') }}">
    @csrf
    <!-- 表單欄位 -->
</form>
```

### XSS 防護

Blade 預設轉義輸出：

```blade
{{ $userInput }}  <!-- 自動轉義 -->
{!! $trustedHtml !!}  <!-- 不轉義，僅用於可信內容 -->
```

## ⚡ 效能優化

### 查詢優化

```php
// 使用 Eager Loading 避免 N+1 問題
$articles = Article::with(['author', 'category', 'tags'])->get();

// 只選擇需要的欄位
$articles = Article::select('id', 'title', 'slug')->get();
```

### 快取策略

```php
// 快取查詢結果
$stats = Cache::remember('dashboard.stats', 600, function () {
    return [
        'users' => User::count(),
        'articles' => Article::count(),
    ];
});

// 清除快取
Cache::forget('dashboard.stats');
```

### 生產環境優化

```bash
# 快取配置
php artisan config:cache

# 快取路由
php artisan route:cache

# 快取視圖
php artisan view:cache

# 優化自動加載
composer dump-autoload --optimize
```

## 🎨 UI 組件

### CoreUI 組件使用

**卡片**:
```blade
<div class="card">
    <div class="card-header">
        <i class="icon-doc"></i> 文章列表
    </div>
    <div class="card-body">
        <!-- 內容 -->
    </div>
</div>
```

**表格**:
```blade
<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>標題</th>
            <th>作者</th>
            <th>狀態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach($articles as $article)
            <tr>
                <td>{{ $article->title }}</td>
                <td>{{ $article->author->name }}</td>
                <td>
                    <span class="badge bg-{{ $article->status_color }}">
                        {{ $article->status_label }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-primary">
                        編輯
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

**按鈕**:
```blade
<button class="btn btn-primary">主要按鈕</button>
<button class="btn btn-success">成功按鈕</button>
<button class="btn btn-danger">危險按鈕</button>
<button class="btn btn-outline-primary">外框按鈕</button>
```

## 📊 SEO 功能使用

### 設置頁面 Meta 標籤

```php
// Controller
public function show(Article $article)
{
    // 自動設置 SEO Meta
    $article->loadSeoMeta();

    return view('admin.articles.show', compact('article'));
}
```

```blade
<!-- Layout -->
<head>
    <title>{{ $seo_title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seo_description ?? '' }}">
    <meta name="keywords" content="{{ $seo_keywords ?? '' }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $og_title ?? $seo_title }}">
    <meta property="og:description" content="{{ $og_description ?? $seo_description }}">
    <meta property="og:image" content="{{ $og_image ?? '' }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $twitter_title ?? $seo_title }}">
</head>
```

### 生成 Sitemap

```bash
# 手動生成
php artisan sitemap:generate

# 排程自動生成（每日）
```

## 📈 Google Analytics 使用

### 追蹤頁面瀏覽

自動追蹤所有頁面（已在布局中配置）。

### 追蹤自定義事件

```javascript
// 追蹤按鈕點擊
<button onclick="trackEvent('button_click', 'download', 'PDF檔案')">
    下載 PDF
</button>

<script>
function trackEvent(action, category, label) {
    gtag('event', action, {
        'event_category': category,
        'event_label': label
    });
}
</script>
```

### 查看分析報表

訪問後台 Analytics 儀表板：`/admin/analytics`

## 🤝 貢獻指南

歡迎提交 Pull Request！請遵循以下步驟：

1. Fork 此專案
2. 創建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交變更 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 開啟 Pull Request

### 編碼規範

- 遵循 PSR-12 編碼標準
- 使用 Laravel Pint 格式化程式碼
- 使用 PHPStan Level 6 進行靜態分析
- 編寫清晰的註釋和文檔
- 為新功能添加測試

## 📝 變更日誌

查看 [CHANGELOG.md](CHANGELOG.md) 了解版本變更詳情。

## 📄 授權

本專案採用 MIT 授權條款 - 查看 [LICENSE](LICENSE) 文件了解詳情。

## 🙏 致謝

- [Laravel](https://laravel.com/) - 優雅的 PHP 框架
- [CoreUI](https://coreui.io/) - 免費的 Bootstrap 管理模板
- [Spatie](https://spatie.be/) - 優秀的 Laravel 套件
- [Bootstrap](https://getbootstrap.com/) - 強大的前端框架
- [Chart.js](https://www.chartjs.org/) - 簡單易用的圖表庫

## 📧 聯繫方式

如有問題或建議，請聯繫：

- Email: bryantchi.work@gmail.com
- Issues: https://github.com/BryantChi/mhstudio/issues

---

**開發者**: 紀孟勳 Bryant Chi
**最後更新**: 2026年3月
**版本**: 1.0.0
