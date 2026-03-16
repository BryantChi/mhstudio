<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Article;
use App\Models\Project;
use App\Models\Service;
use App\Models\LegalPage;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = '生成 XML Sitemap';

    public function handle(): int
    {
        $this->info('開始生成 Sitemap...');

        $sitemap = Sitemap::create();

        // 首頁
        $sitemap->add(
            Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        // 靜態頁面
        $staticPages = [
            '/about' => 0.8,
            '/portfolio' => 0.9,
            '/blog' => 0.9,
            '/quote' => 0.7,
        ];

        foreach ($staticPages as $path => $priority) {
            $sitemap->add(
                Url::create($path)
                    ->setPriority($priority)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        // 已發布文章
        Article::published()
            ->orderByDesc('published_at')
            ->each(function (Article $article) use ($sitemap) {
                $sitemap->add(
                    Url::create("/blog/{$article->slug}")
                        ->setLastModificationDate($article->updated_at)
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        // 已發布作品
        Project::published()
            ->ordered()
            ->each(function (Project $project) use ($sitemap) {
                $sitemap->add(
                    Url::create("/portfolio/{$project->slug}")
                        ->setLastModificationDate($project->updated_at)
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        // 服務頁面
        Service::active()
            ->ordered()
            ->each(function (Service $service) use ($sitemap) {
                $sitemap->add(
                    Url::create("/services/{$service->slug}")
                        ->setLastModificationDate($service->updated_at)
                        ->setPriority(0.6)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        // 法律頁面
        LegalPage::active()
            ->ordered()
            ->each(function (LegalPage $page) use ($sitemap) {
                $sitemap->add(
                    Url::create("/legal/{$page->slug}")
                        ->setLastModificationDate($page->updated_at)
                        ->setPriority(0.3)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                );
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $count = count($sitemap->getTags());
        $this->info("Sitemap 生成完成，共 {$count} 個 URL，已寫入 public/sitemap.xml");

        return self::SUCCESS;
    }
}
