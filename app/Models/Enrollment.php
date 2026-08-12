<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'program_id',
        'batch_id',
        'status',
        'progress',
        'enrolled_at',
        'completed_at',
        'student_rating',
        'student_feedback',
        'student_feedback_at',
        'mentor_rating',
        'mentor_note',
        'mentor_rated_at',
        'final_score',
        'grade_predicate',
        'grade_note',
        'grade_aspects',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'student_feedback_at' => 'datetime',
            'mentor_rated_at' => 'datetime',
            'graded_at' => 'datetime',
            'grade_aspects' => 'array',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->progress >= 100
            || $this->status === 'completed'
            || $this->hasGrade();
    }

    public function canWriteTestimonial(): bool
    {
        return $this->isCompleted() && ! $this->testimonial;
    }

    public function markCompleted(): void
    {
        $data = [];

        if ($this->status !== 'completed') {
            $data['status'] = 'completed';
        }

        if ((int) $this->progress < 100) {
            $data['progress'] = 100;
        }

        if (! $this->completed_at) {
            $data['completed_at'] = now();
        }

        if ($data !== []) {
            $this->update($data);
        }

        if (! Certificate::where('enrollment_id', $this->id)->exists()) {
            Certificate::create([
                'enrollment_id' => $this->id,
                'code' => 'TS-'.strtoupper(Str::random(8)),
                'issued_at' => now(),
            ]);
        }
    }

    public function hasGrade(): bool
    {
        return $this->graded_at !== null && $this->final_score !== null;
    }

    public static function gradeAspectDefaults(): array
    {
        return [
            'project' => [
                'Implementasi UI/UX & Desain Responsif',
                'Integrasi API & Platform Eksternal',
                'Manajemen State (State Management)',
            ],
            'sikap' => [
                'Kehadiran',
                'Kedisiplinan',
                'Tanggung Jawab',
                'Ketaatan',
                'Kejujuran',
                'Hubungan Kerja',
                'Konsep/Ide/Kreativitas',
                'Produktivitas',
            ],
        ];
    }

    public static function projectWeight(): int
    {
        return 40;
    }

    public static function sikapWeight(): int
    {
        return 60;
    }

    public static function letterFromScore(int|float $score): string
    {
        $score = (int) round($score);

        return match (true) {
            $score >= 90 => 'A',
            $score >= 85 => 'A-',
            $score >= 80 => 'B+',
            $score >= 75 => 'B',
            $score >= 70 => 'B-',
            $score >= 65 => 'C+',
            $score >= 60 => 'C',
            $score >= 55 => 'C-',
            $score >= 50 => 'D',
            default => 'E',
        };
    }

    public static function predicateFromScore(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Sangat Baik',
            $score >= 80 => 'Baik',
            $score >= 70 => 'Cukup',
            $score >= 60 => 'Kurang',
            default => 'Sangat Kurang',
        };
    }

    /**
     * Normalize grade_aspects to { project: [], sikap: [] }.
     *
     * @return array{project: list<array{aspect: string, score: int|null, letter: string|null}>, sikap: list<array{aspect: string, score: int|null, letter: string|null}>}
     */
    public function gradedAspectGroups(): array
    {
        $raw = $this->grade_aspects;
        $defaults = self::gradeAspectDefaults();

        if (is_array($raw) && (isset($raw['project']) || isset($raw['sikap']))) {
            return [
                'project' => self::normalizeAspectRows($raw['project'] ?? []),
                'sikap' => self::normalizeAspectRows($raw['sikap'] ?? []),
            ];
        }

        // Legacy flat list → treat as sikap-style aspects
        if (is_array($raw) && $raw !== [] && array_is_list($raw)) {
            return [
                'project' => collect($defaults['project'])->map(fn ($name) => [
                    'aspect' => $name,
                    'score' => null,
                    'letter' => null,
                ])->all(),
                'sikap' => self::normalizeAspectRows($raw),
            ];
        }

        return [
            'project' => collect($defaults['project'])->map(fn ($name) => [
                'aspect' => $name,
                'score' => null,
                'letter' => null,
            ])->all(),
            'sikap' => collect($defaults['sikap'])->map(fn ($name) => [
                'aspect' => $name,
                'score' => null,
                'letter' => null,
            ])->all(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{aspect: string, score: int|null, letter: string|null}>
     */
    public static function normalizeAspectRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $aspect = trim((string) ($row['aspect'] ?? $row['name'] ?? ''));
            if ($aspect === '') {
                continue;
            }
            $score = $row['score'] ?? null;
            $score = $score === null || $score === '' ? null : (int) $score;
            $out[] = [
                'aspect' => $aspect,
                'score' => $score,
                'letter' => $score === null ? ($row['letter'] ?? null) : self::letterFromScore($score),
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{aspect: string, score: int|null}>  $rows
     */
    public static function averageAspectScore(array $rows): ?float
    {
        $scores = collect($rows)
            ->pluck('score')
            ->filter(fn ($s) => $s !== null && $s !== '')
            ->map(fn ($s) => (float) $s);

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 2);
    }

    /**
     * Final = project_avg * 40% + sikap_avg * 60%.
     *
     * @param  array{project?: list<array>, sikap?: list<array>}  $groups
     */
    public static function computeFinalScore(array $groups): ?int
    {
        $projectAvg = self::averageAspectScore($groups['project'] ?? []);
        $sikapAvg = self::averageAspectScore($groups['sikap'] ?? []);

        if ($projectAvg === null && $sikapAvg === null) {
            return null;
        }

        $projectAvg ??= 0.0;
        $sikapAvg ??= 0.0;

        return (int) round(
            ($projectAvg * self::projectWeight() / 100)
            + ($sikapAvg * self::sikapWeight() / 100)
        );
    }

    public static function mentorRatingLabels(): array
    {
        return [
            5 => 'Berhasil menyelesaikan semua',
            4 => 'Ada course yang tidak sesuai',
            3 => 'Performa cukup',
            2 => 'Perlu perbaikan signifikan',
            1 => 'Tidak memenuhi kriteria',
        ];
    }

    public function mentorRatingLabel(): ?string
    {
        if (! $this->mentor_rating) {
            return null;
        }

        return self::mentorRatingLabels()[$this->mentor_rating] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function logbookEntries(): HasMany
    {
        return $this->hasMany(LogbookEntry::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function testimonial(): HasOne
    {
        return $this->hasOne(Testimonial::class);
    }

    public function recalculateProgress(): void
    {
        $totalLessons = $this->program->lessons()->count();

        if ($totalLessons === 0) {
            $this->update(['progress' => 0]);

            return;
        }

        $completed = LessonProgress::query()
            ->where('user_id', $this->user_id)
            ->whereIn('lesson_id', $this->program->lessons()->pluck('lessons.id'))
            ->count();

        $progress = (int) round(($completed / $totalLessons) * 100);

        $this->update(['progress' => $progress]);

        if ($progress >= 100) {
            $this->markCompleted();
        }
    }
}
