<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'program_id',
        'body',
        'role_label',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function displayName(): string
    {
        $name = $this->user?->name ?? 'Peserta';
        $first = collect(explode(' ', $name))->first() ?: $name;
        $campus = $this->user?->university;

        return $campus ? "{$first}, {$campus}" : $first;
    }

    public function initials(): string
    {
        $name = $this->user?->name ?? 'P';

        return strtoupper(collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    }

    public function roleText(): string
    {
        return $this->role_label
            ?: ($this->program?->title ?? 'Peserta Program');
    }
}
