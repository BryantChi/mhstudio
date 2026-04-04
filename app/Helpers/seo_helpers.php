<?php

if (!function_exists('seo_title')) {
    /**
     * 設置頁面 SEO 標題
     */
    function seo_title(string $title = null): string
    {
        if ($title) {
            view()->share('seo_title', $title);
            return $title;
        }

        return view()->shared('seo_title', config('seo.defaults.title'));
    }
}

if (!function_exists('seo_description')) {
    /**
     * 設置頁面 SEO 描述
     */
    function seo_description(string $description = null): string
    {
        if ($description) {
            view()->share('seo_description', $description);
            return $description;
        }

        return view()->shared('seo_description', config('seo.defaults.description'));
    }
}

if (!function_exists('seo_keywords')) {
    /**
     * 設置頁面 SEO 關鍵字
     */
    function seo_keywords(string $keywords = null): string
    {
        if ($keywords) {
            view()->share('seo_keywords', $keywords);
            return $keywords;
        }

        return view()->shared('seo_keywords', config('seo.defaults.keywords'));
    }
}

if (!function_exists('set_seo_meta')) {
    /**
     * 批量設置 SEO Meta 資料
     */
    function set_seo_meta(array $meta): void
    {
        if (isset($meta['title'])) {
            seo_title($meta['title']);
        }

        if (isset($meta['description'])) {
            seo_description($meta['description']);
        }

        if (isset($meta['keywords'])) {
            seo_keywords($meta['keywords']);
        }

        if (isset($meta['og_title'])) {
            view()->share('og_title', $meta['og_title']);
        }

        if (isset($meta['og_description'])) {
            view()->share('og_description', $meta['og_description']);
        }

        if (isset($meta['og_image'])) {
            view()->share('og_image', $meta['og_image']);
        }

        if (isset($meta['canonical_url'])) {
            view()->share('canonical_url', $meta['canonical_url']);
        }
    }
}

if (!function_exists('generate_meta_description')) {
    /**
     * 從內容生成 Meta Description
     */
    function generate_meta_description(string $content, int $length = 160): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length - 3) . '...';
        }

        return $text;
    }
}

if (!function_exists('validate_meta_title')) {
    /**
     * 驗證 Meta Title 長度
     */
    function validate_meta_title(string $title): array
    {
        $length = mb_strlen($title);
        $limits = config('seo.limits.title');

        return [
            'length' => $length,
            'is_valid' => $length >= $limits['min'] && $length <= $limits['max'],
            'is_optimal' => $length >= $limits['min'] && $length <= $limits['recommended'],
            'message' => $length < $limits['min']
                ? "標題太短（建議 {$limits['min']}-{$limits['recommended']} 字元）"
                : ($length > $limits['max']
                    ? "標題太長（建議 {$limits['min']}-{$limits['recommended']} 字元）"
                    : '長度適中'),
        ];
    }
}

if (!function_exists('validate_meta_description')) {
    /**
     * 驗證 Meta Description 長度
     */
    function validate_meta_description(string $description): array
    {
        $length = mb_strlen($description);
        $limits = config('seo.limits.description');

        return [
            'length' => $length,
            'is_valid' => $length >= $limits['min'] && $length <= $limits['max'],
            'is_optimal' => $length >= $limits['min'] && $length <= $limits['recommended'],
            'message' => $length < $limits['min']
                ? "描述太短（建議 {$limits['min']}-{$limits['recommended']} 字元）"
                : ($length > $limits['max']
                    ? "描述太長（建議 {$limits['min']}-{$limits['recommended']} 字元）"
                    : '長度適中'),
        ];
    }
}

if (!function_exists('generate_schema_article')) {
    /**
     * 生成文章 Schema.org 結構化數據
     */
    function generate_schema_article(object $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt ?? generate_meta_description($article->content),
            'image' => $article->featured_image ? asset($article->featured_image) : null,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->display_author_name,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
        ];
    }
}

if (!function_exists('generate_breadcrumb_schema')) {
    /**
     * 生成麵包屑 Schema.org 結構化數據
     */
    function generate_breadcrumb_schema(array $items): array
    {
        $itemListElements = [];

        foreach ($items as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElements,
        ];
    }
}
