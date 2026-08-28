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

    public function youtubeEmbedSrc(): ?string
    {
        $url = (string) $this->embedVideoUrl();

        return $url !== '' && str_contains($url, 'youtube.com/embed/') ? $url : null;
    }

    public function playableVideoSrc(): ?string
    {
        if ($this->file_type === 'video' && $this->filePublicUrl()) {
            return $this->filePublicUrl();
        }

        if (preg_match('/\.(mp4|webm|mov)(?:\?|$)/i', (string) $this->file_url)) {
            return $this->filePublicUrl();
        }

        $url = (string) $this->embedVideoUrl();
        if ($url !== '' && ! str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }

        return null;
    }

    public function playableAudioSrc(): ?string
    {
        if (! $this->filePublicUrl()) {
            return null;
        }

        if ($this->file_type === 'audio') {
            return $this->filePublicUrl();
        }

        if ($this->type === 'recording' && (
            preg_match('/\.(mp3|wav|m4a|aac|ogg|mpeg)(?:\?|$)/i', (string) $this->file_url)
            || str_starts_with((string) $this->file_url, 'http')
        )) {
            return $this->filePublicUrl();
        }

        return null;
    }

    public function isExternalFileUrl(): bool
    {
        $url = (string) $this->file_url;

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
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

    public function isWeeklyAssignment(): bool
    {
        return $this->type === 'assignment';
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'text' => 'Pengenalan',
            'video' => 'Video',
            'article' => 'Artikel',
            'pdf' => 'PDF',
            'recording' => 'Rekaman',
            'quiz' => 'Quiz',
            'assignment' => 'Upload tugas',
            default => $this->type,
        };
    }
}
