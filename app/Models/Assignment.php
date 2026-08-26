<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = ['lesson_id', 'title', 'instructions', 'deadline', 'kind'];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function isQuiz(): bool
    {
        return $this->kind === 'quiz';
    }

    /**
     * Tugas dikumpulkan lewat tautan (Drive/GitHub/Figma) atau unggah file — siswa memilih salah satu.
     */
    public function collectsWork(): bool
    {
        return $this->kind === 'assignment';
    }
}
