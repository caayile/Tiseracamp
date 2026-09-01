<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_url',
        'notes',
        'score',
        'feedback',
        'status',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExternalLink(): bool
    {
        $url = trim((string) $this->file_url);

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }

    public function viewerUrl(): ?string
    {
        $url = trim((string) $this->file_url);
        if ($url === '') {
            return null;
        }

        if ($this->isExternalLink()) {
            return $this->normalizedExternalUrl($url);
        }

        return media_url($url);
    }

    public function serveForViewer(): BinaryFileResponse|RedirectResponse
    {
        abort_unless(filled($this->file_url), 404, 'Berkas tugas belum dikumpulkan.');

        if ($this->isExternalLink()) {
            return redirect()->away($this->normalizedExternalUrl(trim((string) $this->file_url)));
        }

        $absolute = resolve_public_upload((string) $this->file_url);
        abort_if($absolute === null, 404, 'Berkas tugas tidak ditemukan di server.');

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION) ?: 'bin');
        $filename = Str::slug($this->assignment?->title ?: 'tugas').'-'.$this->id.'.'.$extension;
        $inline = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'], true);

        return response()->file($absolute, [
            'Content-Type' => match ($extension) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'application/octet-stream',
            },
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$filename.'"',
        ]);
    }

    private function normalizedExternalUrl(string $url): string
    {
        if (preg_match('~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~i', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/view';
        }

        if (preg_match('~drive\.google\.com/(?:open|uc)\?(?:[^#]*&)?id=([a-zA-Z0-9_-]+)~i', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/view';
        }

        if (preg_match('~docs\.google\.com/(document|spreadsheets|presentation)/d/([a-zA-Z0-9_-]+)~i', $url, $m)) {
            return 'https://docs.google.com/'.$m[1].'/d/'.$m[2].'/view';
        }

        return $url;
    }
}
