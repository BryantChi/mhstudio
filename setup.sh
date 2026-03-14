#!/bin/bash

# Laravel Admin System - 快速安裝腳本
# 此腳本會自動完成資料庫設置和初始化

echo "🚀 Laravel 後台管理系統 - 快速安裝"
echo "=================================="
echo ""

# 檢查 .env 檔案
if [ ! -f .env ]; then
    echo "❌ 錯誤：.env 檔案不存在"
    echo "請先執行: cp .env.example .env"
    exit 1
fi

# 檢查資料庫設定
echo "📋 步驟 1/4: 檢查設定..."
DB_DATABASE=$(grep ^DB_DATABASE= .env | cut -d '=' -f2)
DB_USERNAME=$(grep ^DB_USERNAME= .env | cut -d '=' -f2)

if [ -z "$DB_DATABASE" ]; then
    echo "⚠️  警告：資料庫名稱未設定"
    echo "請編輯 .env 檔案設置 DB_DATABASE"
    exit 1
fi

echo "✅ 資料庫設定：$DB_DATABASE"
echo ""

# 發布 Spatie Permission 設定
echo "📦 步驟 2/4: 發布套件設定..."
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet
echo "✅ 套件設定已發布"
echo ""

# 執行 Migrations 和 Seeders
echo "🗄️  步驟 3/4: 建立資料表和初始資料..."
read -p "這將會清空並重建資料庫，確定要繼續嗎？(y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate:fresh --seed
    echo "✅ 資料庫初始化完成"
else
    echo "❌ 已取消"
    exit 1
fi
echo ""

# 建立儲存目錄連結
echo "🔗 步驟 4/4: 建立儲存連結..."
php artisan storage:link 2>/dev/null || echo "ℹ️  儲存連結已存在"
echo ""

# 設置權限
if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "win32" ]]; then
    echo "🔐 設置目錄權限..."
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    echo "✅ 權限設置完成"
    echo ""
fi

# 顯示完成訊息
echo "🎉 安裝完成！"
echo "=================================="
echo ""
echo "📝 預設管理員帳號："
echo "   Email:    admin@example.com"
echo "   Password: password"
echo ""
echo "🚀 啟動開發伺服器："
echo "   終端 1: php artisan serve"
echo "   終端 2: npm run dev"
echo ""
echo "🌐 訪問網址："
echo "   http://localhost:8000"
echo ""
echo "祝使用愉快！ ✨"
