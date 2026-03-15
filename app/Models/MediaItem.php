<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaItem extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'alt_text',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /* ===== Relations ===== */

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* ===== Accessors ===== */

    public function getUrlAttribute(): string
    {
        // 優先使用相對路徑 /storage/...，避免 APP_URL 不匹配導致 404
        if ($this->disk === 'public') {
            return '/storage/' . $this->path;
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /* ===== Scopes ===== */

    public function scopeImages(Builder $query): void
    {
        $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeDocuments(Builder $query): void
    {
        $query->where('mime_type', 'not like', 'image/%');
    }
}
