<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'uploaded_by',
        'filename',
        'original_name',
        'path',
        'disk',
        'size',
        'mime_type',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /* ===== Relations ===== */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* ===== Accessors ===== */

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        return format_file_size($this->size);
    }

    public function getIconAttribute(): string
    {
        return match (true) {
            str_contains($this->mime_type, 'image') => 'cil-image',
            str_contains($this->mime_type, 'pdf') => 'cil-file',
            str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document') => 'cil-file',
            str_contains($this->mime_type, 'spreadsheet') || str_contains($this->mime_type, 'excel') => 'cil-spreadsheet',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'archive') => 'cil-zip',
            str_contains($this->mime_type, 'video') => 'cil-video',
            default => 'cil-file',
        };
    }
}
