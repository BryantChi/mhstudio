# 🚀 安裝指南

## ✅ 已完成的步驟

以下步驟已經完成，無需再執行：

- ✅ Composer 依賴安裝完成
- ✅ NPM 依賴安裝完成
- ✅ .env 文件已建立
- ✅ Application Key 已生成

## 📋 接下來的步驟

### 1. 配置資料庫

編輯 `.env` 文件，設置資料庫連線：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. 建立資料庫

```bash
# 登入 MySQL
mysql -u root -p

# 建立資料庫
CREATE DATABASE admin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 3. 發布 Spatie Permission 設定檔

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 4. 執行 Migrations 和 Seeders

```bash
php artisan migrate:fresh --seed
```

這將會建立：
- 所有資料表
- 5 個預設角色（super-admin, admin, editor, author, viewer）
- 32 個權限
- 測試用戶（admin@example.com / password）
- 範例分類和設定

### 5. 啟動開發伺服器

開啟兩個終端視窗：

**終端 1 - Laravel 伺服器：**
```bash
php artisan serve
```

**終端 2 - Vite 開發伺服器：**
```bash
npm run dev
```

### 6. 訪問系統

開啟瀏覽器訪問：**http://localhost:8000**

**預設登入帳號：**
- Email: `admin@example.com`
- Password: `password`

## 🎯 可選設定

### 設置檔案權限（Linux/Mac）

```bash
chmod -R 755 storage bootstrap/cache
```

### 清除快取

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 執行測試

```bash
php artisan test
# 或使用 Pest
./vendor/bin/pest
```

## 🔧 常見問題

### 問題：無法連接資料庫

**解決方法：**
1. 確認 MySQL 服務已啟動
2. 檢查 `.env` 中的資料庫設定是否正確
3. 確認資料庫已建立

### 問題：頁面樣式未載入

**解決方法：**
1. 確認 `npm run dev` 正在執行
2. 清除瀏覽器快取
3. 執行 `npm run build` 建立生產版本

### 問題：權限錯誤

**解決方法：**
```bash
# Mac/Linux
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 或使用當前用戶
chown -R $USER:$USER storage bootstrap/cache
```

## 📚 相關文檔

- `START_HERE.md` - 快速入門指南
- `DEVELOPMENT_STATUS.md` - 開發狀態
- `README.md` - 專案總覽
- `docs/ARCHITECTURE.md` - 系統架構
- `docs/SEO.md` - SEO 指南

## 🎉 安裝完成！

安裝完成後，您可以：

1. ✅ 登入後台管理系統
2. ✅ 管理用戶和權限
3. ✅ 建立和管理文章
4. ✅ 配置 SEO 設定
5. ✅ 查看系統統計資訊

祝使用愉快！ 🚀
