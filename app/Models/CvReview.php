<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CvReview extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'target_position',
        'company_name',
        'education_level',
        'preferred_field',
        'location',
        'experience_level',
        'original_filename',
        'file_path',
        'score',
        'result',
        'cover_letter',
        'interview',
        'provider',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'cover_letter' => 'array',
            'interview' => 'array',
            'score' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CvReview $review) {
            if (blank($review->uuid)) {
                $review->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
