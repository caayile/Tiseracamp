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
        'title', 'slug', 'type', 'level', 'education_level', 'majors', 'division',
        'location', 'deadline', 'duration_months', 'price', 'thumbnail',
        'excerpt', 'description', 'benefits', 'qualifications', 'required_documents',
        'preferred_skills', 'responsibilities', 'partner_id',
        'category_id', 'mentor_id', 'is_published', 'is_open', 'is_featured', 'approval_status',
        'audience',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'qualifications' => 'array',
            'required_documents' => 'array',
            'preferred_skills' => 'array',
            'responsibilities' => 'array',
            'deadline' => 'date',
            'is_published' => 'boolean',
            'is_open' => 'boolean',
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
        return match ($this->type) {
            'internship' => 'Magang',
            'job' => 'Lowongan Kerja',
            default => 'Bootcamp',
        };
    }

    public function isJob(): bool
    {
        return $this->type === 'job';
    }

    public function isFree(): bool
    {
        return (int) $this->price === 0;
    }

    public function formattedDuration(): string
    {
        if ((int) $this->duration_months <= 0) {
            return '—';
        }

        return $this->duration_months.' bulan';
    }

    public function formattedSalary(): string
    {
        return $this->price === 0
            ? 'Gaji dirundingkan'
            : 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function isInternshipOpen(): bool
    {
        if ($this->type !== 'internship') {
            return false;
        }

        return $this->isListingOpen();
    }

    public function isJobOpen(): bool
    {
        if ($this->type !== 'job') {
            return false;
        }

        return $this->isListingOpen();
    }

    public function isTsuOnly(): bool
    {
        return $this->audience === 'tsu';
    }

    public function isVisibleTo(?User $user): bool
    {
        return ! $this->isTsuOnly() || ($user?->isTsuStudent() ?? false);
    }

    public function scopeForAudience(Builder $query, bool $tsuOnly): Builder
    {
        if (! $tsuOnly) {
            return $query->where(fn (Builder $q) => $q->where('audience', 'all')->orWhereNull('audience'));
        }

        return $query->where('audience', 'tsu');
    }

    public function isListingOpen(): bool
    {
        if (! $this->is_open) {
            return false;
        }

        if (! $this->is_published || $this->approval_status !== 'approved') {
            return false;
        }

        if ($this->deadline && $this->deadline->copy()->endOfDay()->isPast()) {
            return false;
        }

        return true;
    }

    public function internshipStatusLabel(): string
    {
        return $this->isInternshipOpen() ? 'Terbuka' : 'Tertutup';
    }

    public function jobStatusLabel(): string
    {
        return $this->isJobOpen() ? 'Terbuka' : 'Tertutup';
    }
}
