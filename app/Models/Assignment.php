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

    public function collectsViaLink(): bool
    {
        if ($this->kind !== 'assignment') {
            return false;
        }

        $this->loadMissing('lesson.module.program');

        return $this->lesson?->type === 'assignment'
            || $this->lesson?->module?->program?->type === 'internship';
    }
}
