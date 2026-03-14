<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * 已知的爬蟲/機器人 User-Agent 關鍵字
     */
    protected array $botKeywords = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
        'lighthouse', 'pingdom', 'phant', 'curl', 'wget',
        'python', 'java/', 'httpclient', 'go-http-client',
        'headlesschrome', 'phantomjs', 'selenium',
        'googlebot', 'bingbot', 'yandexbot', 'baiduspider',
        'duckduckbot', 'facebookexternalhit', 'twitterbot',
        'linkedinbot', 'applebot', 'semrushbot', 'ahrefsbot',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 只追蹤成功的 GET 請求
        if ($request->method() !== 'GET' || $response->getStatusCode() >= 400) {
            return $response;
        }

        // 跳過管理後台和 API 路由
        $adminPrefix = config('admin.prefix', 'admin');
        $path = $request->path();

        if (str_starts_with($path, $adminPrefix) || str_starts_with($path, 'api/')) {
            return $response;
        }

        // 跳過靜態資源請求
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot|map)$/i', $path)) {
            return $response;
        }

        // 跳過機器人/爬蟲
        if ($this->isBot($request->userAgent())) {
            return $response;
        }

        // 記錄頁面瀏覽
        try {
            $this->recordPageView($request);
        } catch (\Throwable $e) {
            // 靜默失敗，不影響使用者體驗
            report($e);
        }

        return $response;
    }

    /**
     * 判斷是否為機器人/爬蟲
     */
    protected function isBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $userAgentLower = strtolower($userAgent);

        foreach ($this->botKeywords as $keyword) {
            if (str_contains($userAgentLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 記錄頁面瀏覽事件
     */
    protected function recordPageView(Request $request): void
    {
        // 匿名化 IP - 將最後一組清零
        $ip = $this->anonymizeIp($request->ip());

        // 從路由名稱取得頁面標題
        $pageTitle = $request->route()?->getName() ?? $request->path();

        AnalyticsEvent::create([
            'event_name' => 'page_view',
            'event_category' => 'engagement',
            'event_action' => 'view',
            'page_url' => $request->fullUrl(),
            'page_title' => $pageTitle,
            'referrer' => $request->header('referer'),
            'user_agent' => $request->userAgent(),
            'ip_address' => $ip,
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'event_time' => now(),
        ]);
    }

    /**
     * 匿名化 IP 地址 - 將最後一組設為 0
     */
    protected function anonymizeIp(?string $ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }

        // IPv6 - 將最後 80 位清零
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = inet_pton($ip);
            // 清零後 10 個 bytes (80 bits)
            for ($i = 6; $i < 16; $i++) {
                $packed[$i] = "\x00";
            }

            return inet_ntop($packed);
        }

        return $ip;
    }
}
