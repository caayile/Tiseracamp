<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipApplication extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'full_name', 'phone', 'university', 'major', 'semester', 'education_level',
        'motivation', 'experience', 'cv_path', 'transcript_path', 'cover_letter_path', 'portfolio_url', 'portfolio_path',
        'status', 'reviewer_note', 'reviewed_by', 'submitted_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Menunggu seleksi',
            'under_review' => 'Sedang ditinjau',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'submitted', 'under_review' => 'bg-amber-100 text-amber-800',
            'accepted' => 'bg-emerald-100 text-emerald-800',
            'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    /** Current step 1–5 matching proses pendaftaran magang UI */
    public function processStep(): int
    {
        return match ($this->status) {
            'submitted' => 3,
            'under_review' => 3,
            'accepted' => 5,
            'rejected' => 4,
            default => 1,
        };
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['submitted', 'under_review'], true);
    }

    public function initials(): string
    {
        return strtoupper(
            collect(explode(' ', $this->displayName()))
                ->filter()
                ->map(fn ($word) => mb_substr($word, 0, 1))
                ->take(1)
                ->implode('')
        ) ?: '?';
    }

    public function displayName(): string
    {
        $name = trim((string) $this->full_name);
        $stripped = trim((string) preg_replace('/^\d+[\s.\-_:]*/u', '', $name));
        $name = $stripped !== '' ? $stripped : $name;

        if ($name !== '' && mb_strtolower($name) === $name) {
            return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
        }

        return $name !== '' ? $name : '—';
    }

    public function institutionLabel(): string
    {
        return collect([$this->university, $this->major])
            ->filter()
            ->implode(', ') ?: '—';
    }

    /** @return array<int, array{label: string, url: string}> */
    public function documents(): array
    {
        $items = [];

        $files = [
            'cv' => ['label' => 'CV', 'path' => $this->cv_path],
            'transcript' => ['label' => 'Transkrip', 'path' => $this->transcript_path],
            'cover-letter' => ['label' => 'Surat pengantar', 'path' => $this->cover_letter_path],
            'portfolio' => ['label' => 'Portfolio', 'path' => $this->portfolio_path],
        ];

        foreach ($files as $type => $file) {
            if (! filled($file['path'])) {
                continue;
            }

            $items[] = [
                'label' => $file['label'],
                'url' => route('admin.applications.document', [$this, $type], false),
            ];
        }

        if ($this->portfolio_url) {
            $items[] = ['label' => 'Portfolio (link)', 'url' => $this->portfolio_url];
        }

        return $items;
    }
}
