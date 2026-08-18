<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogbookEntry extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'enrollment_id', 'entry_date',
        'title', 'body', 'obstacles', 'hours', 'progress', 'attachment_path',
        'status', 'reviewer_note', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'reviewed' => 'Sudah direview',
            'revision' => 'Perlu revisi',
            default => 'Menunggu review',
        };
    }

    public function progressPercent(): int
    {
        return max(0, min(100, (int) $this->progress));
    }

    public function isDone(): bool
    {
        return $this->progressPercent() >= 100;
    }

    public function workStatusLabel(): string
    {
        return $this->isDone() ? 'Done' : 'On Progress';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function serveAttachment(): BinaryFileResponse|RedirectResponse
    {
        abort_unless(filled($this->attachment_path), 404);

        $path = (string) $this->attachment_path;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return redirect()->away($path);
        }

        $absolute = resolve_public_upload($path);
        abort_if($absolute === null, 404, 'Dokumentasi tidak ditemukan di server.');

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION) ?: 'jpg');
        $filename = 'dokumentasi-logbook-'.$this->id.'.'.$extension;

        return response()->file($absolute, [
            'Content-Type' => match ($extension) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'application/octet-stream',
            },
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
