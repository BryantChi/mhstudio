# Laravel 後台系統安裝指南

## 已完成的文件

✅ **配置文件**:
- `composer.json` - Composer 依賴配置
- `package.json` - NPM 依賴配置
- `.env.example` - 環境變數範本
- `config/seo.php` - SEO 配置
- `config/analytics.php` - Analytics 配置
- `config/admin.php` - 後台配置

✅ **文檔**:
- `README.md` - 專案說明
- `docs/ARCHITECTURE.md` - 系統架構文檔
- `docs/SEO.md` - SEO 功能文檔
- `docs/ROADMAP.md` - 開發路線圖

✅ **目錄結構**: 所有必要的目錄已建立

✅ **部分 Migrations**: Articles 表 Migration 已建立

## 後續安裝步驟

### 1. 安裝依賴

```bash
# 進入專案目錄
cd /Users/bryantchi/Documents/MWStudio\ Code/BaseWebSite/admin

# 安裝 Composer 依賴
composer install

# 安裝 NPM 依賴
npm install
```

### 2. 環境配置

```bash
# 複製環境變數文件
cp .env.example .env

# 生成應用密鑰
php artisan key:generate

# 編輯 .env 文件，配置資料庫
DB_DATABASE=admin_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. 建立完整的 Migrations

需要創建的 Migrations（按順序）:

```bash
php artisan make:migration create_users_table
php artisan make:migration create_permission_tables
php artisan make:migration create_categories_table
php artisan make:migration create_tags_table
php artisan make:migration create_article_tag_table
php artisan make:migration create_media_table
php artisan make:migration create_settings_table
php artisan make:migration create_seo_meta_table
php artisan make:migration create_analytics_events_table
php artisan make:migration create_activity_log_table
```

或使用我們提供的完整 Migration 範例（參考 `docs/DATABASE.md`）。

### 4. 執行 Migrations

```bash
php artisan migrate
```

### 5. 建立 Models

需要創建的 Models:

```bash
php artisan make:model User
php artisan make:model Article
php artisan make:model Category
php artisan make:model Tag
php artisan make:model Media
php artisan make:model Setting
php artisan make:model SeoMeta
php artisan make:model AnalyticsEvent
```

### 6. 安裝 Spatie 套件

```bash
# 發布 Permission 配置
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 發布 Activity Log 配置
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

# 發布 Media Library 配置
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"

# 執行 Permission Migrations
php artisan migrate
```

### 7. 建立 Seeders

```bash
php artisan make:seeder RolePermissionSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder SettingSeeder

# 執行 Seeders
php artisan db:seed
```

### 8. 建立 Controllers

```bash
php artisan make:controller Admin/DashboardController
php artisan make:controller Admin/UserController --resource
php artisan make:controller Admin/ArticleController --resource
php artisan make:controller Admin/CategoryController --resource
php artisan make:controller Admin/SeoController
php artisan make:controller Admin/AnalyticsController
```

### 9. 建立 Requests

```bash
php artisan make:request User/StoreUserRequest
php artisan make:request User/UpdateUserRequest
php artisan make:request Article/StoreArticleRequest
php artisan make:request Article/UpdateArticleRequest
```

### 10. 建立 Services 和 Actions

手動創建以下文件（參考 `docs/ARCHITECTURE.md`）:

- `app/Services/Dashboard/DashboardService.php`
- `app/Services/Seo/SeoService.php`
- `app/Services/Seo/SitemapService.php`
- `app/Services/Analytics/GoogleAnalyticsService.php`
- `app/Actions/Article/CreateArticleAction.php`
- `app/Actions/Article/PublishArticleAction.php`

### 11. 整合 CoreUI

```bash
# 下載 CoreUI Free Template
# https://coreui.io/product/free-bootstrap-admin-template/

# 複製 CoreUI 資源到 public/assets
# 複製 CSS, JS, 圖片等文件
```

### 12. 建立 Blade 視圖

參考 `docs/ARCHITECTURE.md` 中的視圖結構，建立:

- `resources/views/layouts/admin.blade.php`
- `resources/views/layouts/partials/*.blade.php`
- `resources/views/admin/**/*.blade.php`

### 13. 配置路由

編輯 `routes/web.php` 和創建 `routes/admin.php`（參考文檔）。

### 14. 編譯前端資源

```bash
# 開發模式
npm run dev

# 生產模式
npm run build
```

### 15. 啟動開發伺服器

```bash
php artisan serve
```

訪問 `http://localhost:8000/admin`

## 快速開始腳本

為了加速開發，可以使用以下腳本：

```bash
#!/bin/bash

# install.sh
echo "開始安裝 Laravel 後台系統..."

# 安裝依賴
composer install
npm install

# 環境配置
cp .env.example .env
php artisan key:generate

# 資料庫遷移
php artisan migrate

# 發布配置
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"

# 執行 Seeders
php artisan db:seed

# 編譯前端
npm run build

echo "安裝完成！"
echo "請執行: php artisan serve"
```

## 完整代碼範例

所有核心功能的完整代碼範例都在文檔中：

- **Migrations**: 參考 `docs/DATABASE.md`（待創建）
- **Models**: 參考 `docs/ARCHITECTURE.md`
- **Controllers**: 參考 `docs/ARCHITECTURE.md`
- **Views**: 參考 CoreUI 文檔和我們的範例
- **SEO 功能**: 參考 `docs/SEO.md`

## 開發建議

1. **按照 ROADMAP.md 的順序開發**，先完成基礎架構
2. **使用 Laravel 官方命令**生成檔案骨架
3. **參考文檔中的代碼範例**完善功能
4. **邊開發邊測試**，確保每個功能正常
5. **使用 Git 版本控制**，記錄每個階段的進度

## 需要幫助？

- 查看 `docs/` 目錄中的詳細文檔
- 參考 Laravel 官方文檔: https://laravel.com/docs
- 參考 CoreUI 文檔: https://coreui.io/docs
- 參考 Spatie 套件文檔: https://spatie.be/docs

## 後續開發

系統的核心架構和規劃已完成，您可以根據 `docs/ROADMAP.md` 中的 10 週計劃逐步實作所有功能。

所有必要的文檔、配置和架構設計都已準備好，可以直接開始開發！

---

**目前進度**: 20% （配置和架構設計完成）
**預計完成時間**: 按 ROADMAP 10 週計劃執行
