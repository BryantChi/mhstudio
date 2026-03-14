# 🚀 快速開始指南

這是最快速開始開發的步驟指南。

## 📋 前置需求

確保你已安裝：
- PHP >= 8.2
- Composer >= 2.5
- Node.js >= 18.x
- MySQL >= 8.0
- Redis >= 7.0（可選）

## ⚡ 5 分鐘快速啟動

### 1. 安裝依賴（2 分鐘）

```bash
# 進入專案目錄
cd /Users/bryantchi/Documents/MWStudio\ Code/BaseWebSite/admin

# 安裝 PHP 依賴
composer install

# 安裝 JavaScript 依賴
npm install
```

### 2. 環境配置（1 分鐘）

```bash
# 複製環境文件
cp .env.example .env

# 生成應用密鑰
php artisan key:generate
```

編輯 `.env` 設置資料庫：
```env
DB_DATABASE=admin_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. 建立資料庫（1 分鐘）

```bash
# 創建資料庫
mysql -u root -p
CREATE DATABASE admin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 4. 發布 Spatie 套件（1 分鐘）

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 5. 啟動開發伺服器（立即）

```bash
# 啟動 Laravel
php artisan serve

# 另開終端，啟動 Vite
npm run dev
```

訪問: http://localhost:8000

## 📝 接下來要做什麼？

### 選項 A：從範例開始（推薦新手）

1. **建立完整的 Migrations**
   ```bash
   # 複製 docs/ARCHITECTURE.md 中的 Migration 代碼
   # 或使用 artisan 命令建立
   php artisan make:migration create_categories_table
   php artisan make:migration create_tags_table
   # ... 其他表

   php artisan migrate
   ```

2. **建立 Models**
   ```bash
   php artisan make:model Category
   php artisan make:model Tag
   php artisan make:model User
   # 參考 app/Models/Article.php 的結構編寫
   ```

3. **建立 Controllers**
   ```bash
   php artisan make:controller Admin/DashboardController
   php artisan make:controller Admin/UserController --resource
   php artisan make:controller Admin/ArticleController --resource
   ```

4. **下載 CoreUI 模板**
   - 訪問: https://coreui.io/product/free-bootstrap-admin-template/
   - 下載 Free版本
   - 解壓並複製到 `public/assets/`

5. **建立視圖**
   - 參考 CoreUI 文檔建立 `resources/views/layouts/admin.blade.php`
   - 建立其他頁面視圖

### 選項 B：按部就班開發（推薦專業）

遵循 `docs/ROADMAP.md` 的 10週計劃：

**Week 1-2**: 基礎架構
- ✅ 已完成配置
- 建立所有 Migrations
- 整合 CoreUI
- 建立基礎布局

**Week 3**: 用戶管理
- User CRUD
- 角色權限管理

**Week 4-5**: CMS
- 文章管理
- 分類標籤
- 媒體庫

**Week 6**: SEO
- Meta Tags 管理
- Sitemap 生成

**Week 7**: Analytics
- GA4 整合
- 報表系統

**Week 8**: Dashboard
- 統計數據
- 圖表展示

**Week 9**: 測試優化
**Week 10**: 部署上線

## 🎯 核心檔案位置

### 必讀文檔
```
docs/
├── ARCHITECTURE.md   ← 系統架構（必讀）
├── SEO.md           ← SEO 功能詳解
└── ROADMAP.md       ← 開發計劃（必讀）
```

### 核心配置
```
config/
├── seo.php          ← SEO 設定
├── analytics.php    ← GA 設定
└── admin.php        ← 後台設定（含選單結構）
```

### 範例代碼
```
app/
├── Helpers/
│   ├── helpers.php      ← 通用函數
│   └── seo_helpers.php  ← SEO 函數
└── Models/
    └── Article.php      ← Model 範例（超完整）
```

## 💡 開發技巧

### 使用 Artisan 命令加速開發

```bash
# 建立 Model + Migration + Factory + Seeder + Controller
php artisan make:model Category -mfsc

# 建立 Request
php artisan make:request Article/StoreArticleRequest

# 建立 Resource
php artisan make:resource ArticleResource

# 清除所有快取
php artisan optimize:clear

# 查看路由列表
php artisan route:list
```

### 使用 Helper 函數

```php
// 設置 SEO
set_seo_meta([
    'title' => '文章標題',
    'description' => '文章描述',
    'keywords' => 'laravel, admin',
]);

// 快速閃存訊息
flash_success('操作成功！');
flash_error('操作失敗！');

// 檢查權限
if (can_any(['edit users', 'delete users'])) {
    // 有權限
}

// 生成 Schema
$schema = generate_schema_article($article);
```

### 使用 Blade 組件

```blade
{{-- 檢查權限 --}}
@can('edit users')
    <button>編輯</button>
@endcan

{{-- 活動路由 --}}
<li class="{{ active_route('admin.users') }}">
    <a href="{{ route('admin.users.index') }}">用戶管理</a>
</li>

{{-- 設置 SEO --}}
@section('seo')
    @php
        seo_title('文章標題');
        seo_description('文章描述');
    @endphp
@endsection
```

## 🔧 常見問題

### Q1: Composer install 失敗？
```bash
# 更新 Composer
composer self-update

# 清除快取後重試
composer clear-cache
composer install
```

### Q2: NPM install 失敗？
```bash
# 刪除 node_modules 和 package-lock.json
rm -rf node_modules package-lock.json

# 重新安裝
npm install
```

### Q3: Migration 失敗？
```bash
# 檢查資料庫連線
php artisan db:show

# 重置資料庫
php artisan migrate:fresh

# 查看 Migration 狀態
php artisan migrate:status
```

### Q4: 權限錯誤？
```bash
# macOS / Linux
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache

# 或使用 artisan
php artisan storage:link
```

## 📚 學習資源

### Laravel 官方
- 文檔: https://laravel.com/docs
- 影片: https://laracasts.com

### CoreUI
- 文檔: https://coreui.io/docs
- 元件: https://coreui.io/components

### Spatie 套件
- Permission: https://spatie.be/docs/laravel-permission
- Activity Log: https://spatie.be/docs/laravel-activitylog
- Media Library: https://spatie.be/docs/laravel-medialibrary
- Sitemap: https://github.com/spatie/laravel-sitemap

## 🎓 推薦開發順序

1. **Day 1-2**: 閱讀所有文檔，理解架構
2. **Day 3-4**: 建立完整的 Migrations
3. **Day 5-7**: 整合 CoreUI，建立布局
4. **Week 2**: 實作認證系統
5. **Week 3**: 用戶與權限管理
6. **Week 4-5**: CMS 功能
7. **Week 6+**: 按 ROADMAP 繼續

## ✅ 檢查清單

開始開發前，確保：

- [ ] PHP、Composer、Node.js 已安裝
- [ ] MySQL 已啟動
- [ ] 已閱讀 README.md
- [ ] 已閱讀 docs/ARCHITECTURE.md
- [ ] 已閱讀 docs/ROADMAP.md
- [ ] Composer dependencies 已安裝
- [ ] NPM dependencies 已安裝
- [ ] .env 已配置
- [ ] 資料庫已建立
- [ ] Laravel 伺服器可以啟動

全部完成後，你已經準備好開始開發了！🎉

## 🚀 開始開發

```bash
# 終端 1: Laravel 伺服器
php artisan serve

# 終端 2: Vite 開發伺服器
npm run dev

# 開始編碼！
```

---

**祝開發順利！** 🎊

如有問題，請參考：
- `PROJECT_STATUS.md` - 了解專案狀態
- `INSTALLATION.md` - 詳細安裝步驟
- `docs/` - 完整技術文檔
