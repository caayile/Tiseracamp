<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Program extends Model
{
    protected $fillable = [
        'title', 'slug', 'type', 'level', 'duration_months', 'price', 'thumbnail',
        'excerpt', 'description', 'benefits', 'partner_id', 'category_id', 'mentor_id',
        'is_published', 'is_featured', 'approval_status',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('approval_status', 'approved');
    }

    public function formattedPrice(): string
    {
        return $this->price === 0
            ? 'Gratis'
            : 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function typeLabel(): string
    {
        return $this->type === 'internship' ? 'Magang' : 'Bootcamp';
    }

    public function isFree(): bool
    {
        return (int) $this->price === 0;
    }

    public function formattedDuration(): string
    {
        return $this->duration_months.' bulan';
    }
}
