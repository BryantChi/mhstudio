<?php

namespace Database\Seeders;

use App\Models\SeoMeta;
use App\Models\Service;
use App\Models\Project;
use Illuminate\Database\Seeder;

class SeoMetaSeeder extends Seeder
{
    public function run(): void
    {
        $appUrl = config('app.url', 'https://powerchi.com.tw');

        // ===== 為所有服務建立 SEO Meta =====
        $services = Service::all();
        foreach ($services as $service) {
            SeoMeta::firstOrCreate(
                ['model_type' => Service::class, 'model_id' => $service->id],
                [
                    'meta_title' => $this->serviceTitle($service),
                    'meta_description' => $this->serviceDescription($service),
                    'meta_keywords' => $this->serviceKeywords($service),
                    'meta_robots' => 'index, follow',
                    'canonical_url' => $appUrl . '/services/' . $service->slug,
                    'og_title' => $this->serviceTitle($service),
                    'og_description' => $this->serviceDescription($service),
                    'og_type' => 'website',
                    'og_url' => $appUrl . '/services/' . $service->slug,
                    'twitter_card' => 'summary_large_image',
                    'twitter_title' => $this->serviceTitle($service),
                    'twitter_description' => $this->serviceDescription($service),
                    'schema_org' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Service',
                        'name' => $service->title,
                        'description' => strip_tags($service->excerpt ?? ''),
                        'provider' => [
                            '@type' => 'Organization',
                            'name' => 'MH Studio 孟衡工作室',
                            'url' => $appUrl,
                        ],
                        'areaServed' => ['@type' => 'Country', 'name' => 'Taiwan'],
                        'url' => $appUrl . '/services/' . $service->slug,
                    ],
                ]
            );
        }

        $this->command->info('已為 ' . $services->count() . ' 個服務建立 SEO Meta');

        // ===== 為所有作品建立 SEO Meta =====
        $projects = Project::all();
        foreach ($projects as $project) {
            SeoMeta::firstOrCreate(
                ['model_type' => Project::class, 'model_id' => $project->id],
                [
                    'meta_title' => $project->title . ' — 作品案例 | MH Studio 孟衡',
                    'meta_description' => $this->truncate($project->excerpt ?? strip_tags($project->content ?? ''), 155),
                    'meta_keywords' => implode(', ', array_merge(
                        is_array($project->tech_stack) ? $project->tech_stack : [],
                        ['作品集', '案例', 'MH Studio']
                    )),
                    'meta_robots' => 'index, follow',
                    'canonical_url' => $appUrl . '/portfolio/' . $project->slug,
                    'og_title' => $project->title . ' | MH Studio 作品集',
                    'og_description' => $this->truncate($project->excerpt ?? strip_tags($project->content ?? ''), 155),
                    'og_type' => 'article',
                    'og_image' => $project->cover_image,
                    'og_url' => $appUrl . '/portfolio/' . $project->slug,
                    'twitter_card' => 'summary_large_image',
                    'twitter_title' => $project->title . ' | MH Studio',
                    'twitter_description' => $this->truncate($project->excerpt ?? strip_tags($project->content ?? ''), 155),
                    'twitter_image' => $project->cover_image,
                ]
            );
        }

        $this->command->info('已為 ' . $projects->count() . ' 個作品建立 SEO Meta');
        $this->command->info('SEO Meta 資料建立完成，總計: ' . SeoMeta::count() . ' 筆');
    }

    /**
     * 依服務類型產生 SEO Title
     */
    protected function serviceTitle(Service $service): string
    {
        $typeMap = [
            'website' => '網頁設計',
            'hosting' => '主機代管',
            'maintenance' => '網站維護',
            'addon' => '加值服務',
            'consulting' => '技術顧問',
        ];

        $typeLabel = $typeMap[$service->type] ?? '專業服務';
        return $service->title . ' — ' . $typeLabel . '方案 | MH Studio 孟衡';
    }

    /**
     * 依服務內容產生 SEO Description
     */
    protected function serviceDescription(Service $service): string
    {
        if ($service->excerpt) {
            $desc = strip_tags($service->excerpt);
        } else {
            $desc = strip_tags($service->content ?? $service->title);
        }

        $desc = $this->truncate($desc, 140);

        $price = $service->formatted_price ?? $service->price_range;
        if ($price) {
            $desc .= ' 方案費用：' . $price . '。';
        }

        return $this->truncate($desc . ' 免費諮詢 | MH Studio 孟衡工作室', 160);
    }

    /**
     * 依服務類型產生關鍵字
     */
    protected function serviceKeywords(Service $service): string
    {
        $base = ['MH Studio', '孟衡工作室', '台中'];

        $typeKeywords = match ($service->type) {
            'website' => ['網頁設計', '網站製作', '客製化網站', 'RWD響應式', '企業官網'],
            'hosting' => ['主機代管', '虛擬主機', '網站託管', 'SSL憑證', '網站維運'],
            'maintenance' => ['網站維護', '網站更新', '技術支援', '安全維護'],
            'addon' => ['SEO優化', '網站加值', 'Google Analytics', '效能優化'],
            'consulting' => ['技術顧問', '系統規劃', '架構設計', '技術諮詢'],
            default => ['專業服務', '數位解決方案'],
        };

        $techTags = is_array($service->tech_tags) ? array_slice($service->tech_tags, 0, 3) : [];

        return implode(', ', array_merge($base, $typeKeywords, $techTags, [$service->title]));
    }

    /**
     * 截斷文字
     */
    protected function truncate(string $text, int $length): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length - 3) . '...';
        }
        return $text;
    }
}
