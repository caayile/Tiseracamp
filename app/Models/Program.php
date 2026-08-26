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
        'timeline',
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
            'timeline' => 'array',
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

    public function enrollableBatchId(): ?int
    {
        $batches = $this->batches()
            ->where('status', 'active')
            ->withCount('enrollments')
            ->orderBy('id')
            ->get();

        if ($batches->isEmpty()) {
            return null;
        }

        return $batches
            ->first(fn (Batch $batch) => ! $batch->quota || $batch->enrollments_count < $batch->quota)
            ?->id;
    }

    public function hasAvailableSeat(): bool
    {
        $batches = $this->batches()->where('status', 'active')->withCount('enrollments')->get();
        if ($batches->isEmpty()) {
            return true;
        }

        return $batches->contains(fn (Batch $batch) => ! $batch->quota || $batch->enrollments_count < $batch->quota);
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

    public function internshipApplications(): HasMany
    {
        return $this->hasMany(InternshipApplication::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('approval_status', 'approved');
    }

    public function scopeOrderOpenFirst(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN is_open AND (deadline IS NULL OR deadline >= CURRENT_DATE) THEN 0 ELSE 1 END')
            ->latest();
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

    public function internshipQuota(): ?int
    {
        $batch = $this->relationLoaded('batches')
            ? $this->batches->sortBy('id')->first()
            : $this->batches()->orderBy('id')->first();

        return $batch?->quota;
    }

    public function acceptedInternCount(): int
    {
        if (isset($this->enrollments_count)) {
            return (int) $this->enrollments_count;
        }

        if ($this->relationLoaded('enrollments')) {
            return $this->enrollments->count();
        }

        return $this->enrollments()->count();
    }

    public function remainingInternshipSeats(): ?int
    {
        $quota = $this->internshipQuota();
        if ($quota === null) {
            return null;
        }

        return max(0, $quota - $this->acceptedInternCount());
    }

    public function internshipQuotaLabel(): string
    {
        $quota = $this->internshipQuota();
        $filled = $this->acceptedInternCount();

        if ($quota === null) {
            return $filled === 0
                ? 'Kuota belum diatur'
                : $filled.' peserta (kuota belum diatur)';
        }

        return $filled.' / '.$quota.' kursi terisi';
    }

    public function syncInternshipQuota(int $quota): void
    {
        $batch = $this->batches()->orderBy('id')->first();
        if ($batch) {
            $batch->update([
                'quota' => $quota,
                'status' => 'active',
            ]);

            return;
        }

        $this->batches()->create([
            'name' => 'Batch 1',
            'quota' => $quota,
            'status' => 'active',
            'start_date' => now()->toDateString(),
        ]);
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

    public function isForTsu(): bool
    {
        return in_array($this->audience, ['tsu', 'both'], true);
    }

    public function isTsuOnly(): bool
    {
        return $this->audience === 'tsu';
    }

    public function isHiddenFromAll(): bool
    {
        return $this->audience === 'none';
    }

    public function isVisibleTo(?User $user): bool
    {
        if ($this->audience === 'tsu') {
            return $user?->isTsuStudent() ?? false;
        }

        return ! $this->isHiddenFromAll();
    }

    public function scopeForAudience(Builder $query, bool $tsuOnly): Builder
    {
        if (! $tsuOnly) {
            return $query->where(fn (Builder $q) => $q->whereIn('audience', ['all', 'both'])->orWhereNull('audience'));
        }

        return $query->whereIn('audience', ['tsu', 'both']);
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

    public function timelineWeeks(): array
    {
        if (! empty($this->timeline)) {
            return $this->timeline;
        }
        return $this->defaultTimeline();
    }

    public function defaultTimeline(): array
    {
        return [
            [
                'week' => 1,
                'title' => 'Onboarding & Learning Path',
                'description' => 'Perkenalan program, mentor, dan lingkungan kerja. Mulai mempelajari learning path sesuai divisi masing-masing.',
            ],
            [
                'week' => 2,
                'title' => 'Learning & Project Development',
                'description' => 'Melanjutkan learning path dan mulai mengerjakan project dengan bimbingan mentor.',
            ],
            [
                'week' => 3,
                'title' => 'Project Development & Review',
                'description' => 'Melanjutkan pengerjaan project dan melakukan review bersama mentor untuk mendapatkan feedback dan arahan.',
            ],
            [
                'week' => 4,
                'title' => 'Final Project & Presentation',
                'description' => 'Menyelesaikan project, melakukan presentasi, dan mendapatkan sertifikat setelah menyelesaikan program.',
            ],
        ];
    }

    public function timelineWeeksTitle(): string
    {
        $weeks = count($this->timelineWeeks());
        return "Timeline Magang ({$weeks} Minggu)";
    }

    /**
     * Slot pengumpulan tugas selalu diletakkan paling akhir dalam satu minggu,
     * supaya materi yang ditambahkan mentor tetap tampil lebih dulu.
     */
    public const WEEKLY_ASSIGNMENT_SORT_ORDER = 900;

    /**
     * Magang selalu punya 4 slot minggu. Admin/mentor tinggal mengisi tugas di dalamnya.
     */
    public function ensureInternshipWeeks(): void
    {
        if ($this->type !== 'internship') {
            return;
        }

        if (! $this->modules()->exists()) {
            foreach (range(1, 4) as $week) {
                $this->modules()->create([
                    'title' => 'Minggu '.$week,
                    'sort_order' => $week,
                ]);
            }

            $this->unsetRelation('modules');
        }

        $this->ensureWeeklyAssignments();
    }

    /**
     * Tiap minggu punya satu slot "Tugas Minggu N" bawaan — mentor cukup mengisi
     * instruksi dan deadline, peserta mengumpulkan lewat tautan atau unggah file.
     */
    public function ensureWeeklyAssignments(): void
    {
        $weeks = $this->modules()
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Module $module) => static::weekNumber($module->title) !== null);

        foreach ($weeks as $module) {
            $lesson = $module->lessons()->where('type', 'assignment')->first()
                ?? $module->lessons()->create([
                    'title' => 'Tugas '.$module->title,
                    'type' => 'assignment',
                    'duration_minutes' => 30,
                    'sort_order' => static::WEEKLY_ASSIGNMENT_SORT_ORDER,
                ]);

            $lesson->assignment()->firstOrCreate([], [
                'title' => $lesson->title,
                'kind' => 'assignment',
            ]);
        }

        $this->unsetRelation('modules');
    }

    /**
     * Nama minggu boleh diberi keterangan tambahan, misalnya "Minggu 2 - Riset Pasar".
     */
    public static function weekNumber(string $title): ?int
    {
        return preg_match('/^\s*minggu\s*(\d+)/i', $title, $match) ? (int) $match[1] : null;
    }
}
