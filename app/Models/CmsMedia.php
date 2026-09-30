<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsMedia extends Model
{
    protected $table = 'cms_media';

    protected $fillable = [
        'filename', 'stored_name', 'path', 'url', 'mime_type', 'extension',
        'file_size', 'width', 'height', 'alt_text', 'title', 'caption',
        'folder', 'uploaded_by',
    ];

    public function getThumbUrlAttribute(): string
    {
        // Returns URL; thumbnail generation can be added later
        return $this->url;
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) {
            return "{$bytes} B";
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . " KB";
        }
        return round($bytes / 1048576, 1) . " MB";
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}
