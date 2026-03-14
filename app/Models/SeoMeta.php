<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';

    protected $fillable = [
        'model_type',
        'model_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_robots',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'og_url',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'twitter_site',
        'twitter_creator',
        'schema_org',
        'additional_meta',
    ];

    protected $casts = [
        'schema_org' => 'array',
        'additional_meta' => 'array',
    ];

    /**
     * 關聯:多態關聯
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 獲取完整的 Meta Title
     */
    public function getFullMetaTitleAttribute(): string
    {
        $title = $this->meta_title ?: config('seo.defaults.title');
        $siteName = config('app.name');

        if ($title === $siteName) {
            return $title;
        }

        return "{$title} - {$siteName}";
    }

    /**
     * 獲取 Meta Description
     */
    public function getMetaDescriptionOrDefaultAttribute(): string
    {
        return $this->meta_description ?: config('seo.defaults.description');
    }

    /**
     * 獲取 Meta Keywords
     */
    public function getMetaKeywordsOrDefaultAttribute(): string
    {
        return $this->meta_keywords ?: config('seo.defaults.keywords');
    }

    /**
     * 生成 Open Graph Tags
     */
    public function generateOgTags(): array
    {
        return [
            'og:title' => $this->og_title ?: $this->meta_title,
            'og:description' => $this->og_description ?: $this->meta_description,
            'og:image' => $this->og_image ? asset($this->og_image) : null,
            'og:type' => $this->og_type ?: 'website',
            'og:url' => $this->og_url ?: $this->canonical_url,
            'og:site_name' => config('app.name'),
        ];
    }

    /**
     * 生成 Twitter Card Tags
     */
    public function generateTwitterTags(): array
    {
        return [
            'twitter:card' => $this->twitter_card ?: 'summary_large_image',
            'twitter:title' => $this->twitter_title ?: $this->meta_title,
            'twitter:description' => $this->twitter_description ?: $this->meta_description,
            'twitter:image' => $this->twitter_image ? asset($this->twitter_image) : null,
            'twitter:site' => $this->twitter_site,
            'twitter:creator' => $this->twitter_creator,
        ];
    }

    /**
     * 驗證 Meta Title 長度
     */
    public function validateMetaTitle(): array
    {
        if (!$this->meta_title) {
            return [
                'is_valid' => false,
                'message' => 'Meta Title 不可為空',
            ];
        }

        return validate_meta_title($this->meta_title);
    }

    /**
     * 驗證 Meta Description 長度
     */
    public function validateMetaDescription(): array
    {
        if (!$this->meta_description) {
            return [
                'is_valid' => false,
                'message' => 'Meta Description 不可為空',
            ];
        }

        return validate_meta_description($this->meta_description);
    }

    /**
     * 生成 Schema.org JSON-LD
     */
    public function getSchemaOrgJsonAttribute(): ?string
    {
        if (!$this->schema_org) {
            return null;
        }

        return json_encode($this->schema_org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
