<?php

namespace App\Providers;

use Dompdf\Dompdf;
use Illuminate\Support\ServiceProvider;

class DompdfFontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 註冊 dompdf 中文字型（Noto Sans TC，含拉丁字母）
        // 解決報價單 / 合約等 PDF 匯出中文亂碼問題
        $this->app->resolving('dompdf', function (Dompdf $dompdf) {
            $regular = storage_path('fonts/NotoSansTC-Regular.ttf');
            $bold = storage_path('fonts/NotoSansTC-Bold.ttf');
            if (! is_file($regular) || ! is_file($bold)) {
                return;
            }
            $fontMetrics = $dompdf->getFontMetrics();
            $fontMetrics->registerFont(
                ['family' => 'notosanstc', 'style' => 'normal', 'weight' => 'normal'],
                $regular
            );
            $fontMetrics->registerFont(
                ['family' => 'notosanstc', 'style' => 'normal', 'weight' => 'bold'],
                $bold
            );
        });
    }
}
