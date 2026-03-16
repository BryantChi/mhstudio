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

    protected $description = '生成 XML Sitemap（讀取後台設定）';

    public function handle(): int
    {
        $this->info('開始生成 Sitemap...');

        $sitemap = Sitemap::create();

        $priorityHome = (float) setting('sitemap_priority_home', '1.0');
        $priorityArticles = (float) setting('sitemap_priority_articles', '0.7');
        $priorityProjects = (float) setting('sitemap_priority_projects', '0.7');
        $priorityServices = (float) setting('sitemap_priority_services', '0.6');

        // 首頁
        $sitemap->add(
            Url::create('/')
                ->setPriority($priorityHome)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        // 靜態頁面
        $sitemap->add(Url::create('/about')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
        $sitemap->add(Url::create('/portfolio')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add(Url::create('/blog')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add(Url::create('/quote')->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));

        // 已發布文章
        if (setting('sitemap_include_articles', '1') == '1') {
            Article::published()
                ->orderByDesc('published_at')
                ->each(function (Article $article) use ($sitemap, $priorityArticles) {
                    $sitemap->add(
                        Url::create("/blog/{$article->slug}")
                            ->setLastModificationDate($article->updated_at)
                            ->setPriority($priorityArticles)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    );
                });
            $this->info('  ✓ 文章已加入');
        }

        // 已發布作品
        if (setting('sitemap_include_projects', '1') == '1') {
            Project::published()
                ->ordered()
                ->each(function (Project $project) use ($sitemap, $priorityProjects) {
                    $sitemap->add(
                        Url::create("/portfolio/{$project->slug}")
                            ->setLastModificationDate($project->updated_at)
                            ->setPriority($priorityProjects)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    );
                });
            $this->info('  ✓ 作品已加入');
        }

        // 服務頁面
        if (setting('sitemap_include_services', '1') == '1') {
            Service::active()
                ->ordered()
                ->each(function (Service $service) use ($sitemap, $priorityServices) {
                    $sitemap->add(
                        Url::create("/services/{$service->slug}")
                            ->setLastModificationDate($service->updated_at)
                            ->setPriority($priorityServices)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    );
                });
            $this->info('  ✓ 服務頁面已加入');
        }

        // 法律頁面
        if (setting('sitemap_include_legal', '1') == '1') {
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
            $this->info('  ✓ 法律頁面已加入');
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $count = count($sitemap->getTags());
        $this->info("Sitemap 生成完成，共 {$count} 個 URL → public/sitemap.xml");

        return self::SUCCESS;
    }
}
