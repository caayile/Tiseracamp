<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    protected $fillable = [
        'module_id', 'title', 'type', 'content', 'video_url',
        'file_url', 'file_type', 'image_path', 'duration_minutes', 'sort_order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    /**
     * Auto-convert YouTube watch/share links to embed when mentor/admin saves or displays.
     */
    protected function videoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => youtube_embed_url($value) ?? $value,
            set: fn (?string $value) => youtube_embed_url($value),
        );
    }

    public function embedVideoUrl(): ?string
    {
        return $this->video_url;
    }

    /**
     * Public URL for PDF/attachment — supports external links and uploaded files.
     */
    public function filePublicUrl(): ?string
    {
        return media_url($this->file_url);
    }

    public function isPdf(): bool
    {
        if ($this->type === 'pdf' || $this->file_type === 'pdf') {
            return true;
        }

        $url = strtolower((string) $this->file_url);

        return $url !== '' && (str_contains($url, '.pdf') || str_ends_with($url, 'pdf'));
    }
}
