<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'project_url',
        'image_url',
        'image_path',
        'portfolio_file_url',
        'is_featured',
        'is_published',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    /**
     * Scope untuk portofolio yang ditampilkan di beranda.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope untuk portofolio yang dipublikasikan di galeri.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Kembalikan URL gambar portfolio (path upload lokal atau image_url eksternal).
     */
    public function getImageUrlAttribute(?string $value): ?string
    {
        if ($this->image_path) {
            return media_url($this->image_path);
        }
        return $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCv(): bool
    {
        return $this->type === 'cv';
    }

    public function typeLabel(): string
    {
        return $this->isCv() ? 'CV' : 'Portofolio';
    }
}
