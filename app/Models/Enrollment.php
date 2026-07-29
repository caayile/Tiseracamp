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
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
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
