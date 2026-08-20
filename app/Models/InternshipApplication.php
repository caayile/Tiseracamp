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
        'internship_start_date', 'internship_end_date',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'internship_start_date' => 'date',
            'internship_end_date' => 'date',
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

    public function fileSlug(): string
    {
        $name = $this->displayName();
        $slug = trim((string) preg_replace('/[^\pL\pN]+/u', '-', $name), '-');

        return $slug !== '' ? $slug : 'pendaftar-'.$this->id;
    }

    public function whatsappNumber(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone) ?: '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        } elseif (str_starts_with($digits, '620')) {
            $digits = '62'.ltrim(substr($digits, 2), '0');
        }

        return $digits !== '' ? $digits : null;
    }

    public function whatsappUrl(): ?string
    {
        $number = $this->whatsappNumber();

        return $number ? 'https://wa.me/'.$number : null;
    }

    public function institutionLabel(): string
    {
        return collect([$this->university, $this->major])
            ->filter()
            ->implode(', ') ?: '—';
    }

    public function documentFilename(string $type, string $extension = 'pdf'): string
    {
        $label = match ($type) {
            'cv' => 'CV',
            default => 'Portfolio',
        };

        $ext = strtolower(ltrim($extension, '.')) ?: 'pdf';

        return $label.'-'.$this->fileSlug().'.'.$ext;
    }

    /**
     * @return array<string, array{label: string, path: ?string}>
     */
    public function documentFiles(): array
    {
        return [
            'cv' => ['label' => 'CV', 'path' => $this->cv_path],
            'portfolio' => ['label' => 'Portfolio', 'path' => $this->portfolio_path],
        ];
    }

    public function documentUrl(string $type, bool $download = false, bool $absolute = false): ?string
    {
        $path = $this->documentFiles()[$type]['path'] ?? null;
        if (! filled($path)) {
            return null;
        }

        $url = route('admin.applications.document', [$this, $type], $absolute);

        return $download ? $url.(str_contains($url, '?') ? '&' : '?').'download=1' : $url;
    }

    /**
     * @return array<int, array{key: string, label: string, url: ?string, download_url: ?string, missing: bool}>
     */
    public function documentSlots(): array
    {
        $items = [];

        foreach ($this->documentFiles() as $type => $file) {
            $url = $this->documentUrl($type);
            $items[] = [
                'key' => $type,
                'label' => $file['label'],
                'url' => $url,
                'download_url' => $this->documentUrl($type, true),
                'missing' => $url === null,
            ];
        }

        if ($this->portfolio_url) {
            $items[] = [
                'key' => 'portfolio-link',
                'label' => 'Link portfolio',
                'url' => $this->portfolio_url,
                'download_url' => $this->portfolio_url,
                'missing' => false,
            ];
        }

        return $items;
    }

    /** @return array<int, array{label: string, url: string}> */
    public function documents(): array
    {
        return collect($this->documentSlots())
            ->reject(fn (array $doc) => $doc['missing'] || ! filled($doc['url']))
            ->map(fn (array $doc) => [
                'label' => $doc['label'],
                'url' => $doc['url'],
            ])
            ->values()
            ->all();
    }
}
