<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $guarded = [];

    /** Polymorphic: belongs to either Invoice or Purchase */
    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Public URL for serving the file */
    public function getUrlAttribute(): string
    {
        return asset($this->file_path);
    }

    /** Formatted file size (e.g. "1.2 MB") */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return ($this->file_type ?? '') === 'application/pdf';
    }
}
