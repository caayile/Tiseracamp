<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        return $this->progress >= 100 || $this->status === 'completed';
    }

    public function hasGrade(): bool
    {
        return $this->graded_at !== null && $this->final_score !== null;
    }

    public static function gradeAspectDefaults(): array
    {
        return [
            'Kedisiplinan',
            'Kemampuan teknis',
            'Kerjasama & komunikasi',
            'Inisiatif & tanggung jawab',
            'Kualitas hasil kerja',
        ];
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

        $data = ['progress' => $progress];

        if ($progress >= 100 && $this->status !== 'completed') {
            $data['status'] = 'completed';
            $data['completed_at'] = now();

            if (! Certificate::where('enrollment_id', $this->id)->exists()) {
                Certificate::create([
                    'enrollment_id' => $this->id,
                    'code' => 'TS-'.strtoupper(Str::random(8)),
                    'issued_at' => now(),
                ]);
            }
        }

        $this->update($data);
    }
}
