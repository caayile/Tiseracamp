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
            'submitted_at' => 'datetime:Asia/Jakarta',
            'reviewed_at' => 'datetime:Asia/Jakarta',
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

    /** Current step 1–4 matching proses pendaftaran magang UI */
    public function processStep(): int
    {
        return match ($this->status) {
            'submitted' => 2,
            'under_review' => 2,
            'accepted' => 4,
            'rejected' => 3,
            default => 1,
        };
    }

    /**
     * Stepper steps shown before the user submits a registration.
     *
     * @return array<int, array{label: string, description: string, state: 'completed'|'active'|'pending'|'rejected'}>
     */
    public static function previewSteps(): array
    {
        return [
            1 => [
                'label' => 'Pendaftaran',
                'description' => 'Isi formulir & upload CV + portfolio',
                'detail' => 'Isi formulir data diri, lalu unggah CV dan portfolio kamu. Bisa juga memakai dokumen yang sudah tersimpan di Galeri Karier supaya lebih cepat.',
                'state' => 'active',
            ],
            2 => [
                'label' => 'Seleksi Administrasi',
                'description' => 'Tim kami memeriksa berkas & kualifikasi',
                'detail' => 'Tim admin memeriksa kelengkapan berkas dan kesesuaian kualifikasi program — jenjang pendidikan, jurusan, dan semester. Tidak ada tes khusus di tahap ini.',
                'state' => 'pending',
            ],
            3 => [
                'label' => 'Pengumuman Hasil Seleksi',
                'description' => 'Hasil dikabarkan lewat notifikasi',
                'detail' => 'Hasil seleksi dikabarkan lewat notifikasi akun dan halaman status pendaftaran. Pastikan notifikasi aktif supaya tidak terlewat.',
                'state' => 'pending',
            ],
            4 => [
                'label' => 'Mulai Program Magang',
                'description' => 'Onboarding & mulai magang bareng mentor',
                'detail' => 'Jika diterima, kamu langsung masuk ke program: onboarding, berkenalan dengan mentor pembimbing, lalu memulai aktivitas magang sesuai divisi.',
                'state' => 'pending',
            ],
        ];
    }

    public function submittedAtLabel(): ?string
    {
        return $this->submitted_at
            ? $this->submitted_at->locale('id')->translatedFormat('d M Y, H.i').' WIB'
            : null;
    }

    public function reviewedAtLabel(): ?string
    {
        return $this->reviewed_at
            ? $this->reviewed_at->locale('id')->translatedFormat('d M Y, H.i').' WIB'
            : null;
    }

    public function statusHeadline(): string
    {
        return match ($this->status) {
            'submitted' => 'Berkas kamu sudah masuk!',
            'under_review' => 'Pemeriksaan sedang berlangsung',
            'accepted' => 'Selamat, kamu diterima!',
            'rejected' => 'Belum lolos di kesempatan ini',
            default => $this->statusLabel(),
        };
    }

    public function statusMessage(): string
    {
        $program = $this->program?->title ?? 'program ini';

        return match ($this->status) {
            'submitted' => 'Terima kasih sudah mendaftar di '.$program.'. Tim admin akan memeriksa kelengkapan CV, portfolio, dan kualifikasimu. Begitu ada perkembangan, kami kabarin lewat notifikasi — pantau terus ya.',
            'under_review' => 'Berkasmu sedang ditinjau oleh tim kami. Proses ini butuh waktu sebentar — kamu akan menerima notifikasi begitu hasilnya keluar.',
            'accepted' => 'Kamu resmi menjadi bagian dari '.$program.'. Klik tombol "Mulai magang" di bawah untuk masuk ke program dan berkenalan dengan mentor pembimbingmu.',
            'rejected' => 'Sayangnya berkasmu belum memenuhi kualifikasi '.$program.' kali ini. Jangan berkecil hati — setiap program punya kebutuhan yang berbeda. Yuk intip program magang lain yang mungkin lebih cocok untukmu.',
            default => '',
        };
    }

    /**
     * Return stepper steps with their state for the vertical stepper UI.
     *
     * @return array<int, array{label: string, description: string, state: 'completed'|'active'|'pending'|'rejected'}>
     */
    public function stepperSteps(): array
    {
        $current = $this->processStep();
        $isRejected = $this->status === 'rejected';

        $steps = [
            1 => [
                'label' => 'Pendaftaran',
                'description' => 'Formulir & berkas terkirim',
                'state' => 'completed',
            ],
            2 => [
                'label' => 'Seleksi Administrasi',
                'description' => match ($this->status) {
                    'submitted' => 'Berkas sedang diperiksa tim admin',
                    'under_review' => 'Berkas sedang ditinjau',
                    'accepted' => 'Berkas lolos verifikasi',
                    'rejected' => 'Berkas belum memenuhi kualifikasi',
                    default => 'Tim admin memeriksa berkas & kualifikasi',
                },
                'state' => $isRejected ? 'rejected' : ($current > 2 ? 'completed' : ($current === 2 ? 'active' : 'pending')),
            ],
            3 => [
                'label' => 'Pengumuman Hasil Seleksi',
                'description' => match ($this->status) {
                    'accepted' => 'Kamu diterima di program ini',
                    'rejected' => 'Belum lolos seleksi kali ini',
                    default => 'Hasil dikabarkan lewat notifikasi',
                },
                'state' => $isRejected ? 'rejected' : ($current >= 3 ? ($this->status === 'accepted' ? 'completed' : 'active') : 'pending'),
            ],
            4 => [
                'label' => 'Mulai Program Magang',
                'description' => match ($this->status) {
                    'accepted' => 'Siap dimulai kapan saja',
                    default => 'Onboarding bareng mentor pembimbing',
                },
                'state' => $this->status === 'accepted' ? 'active' : 'pending',
            ],
        ];

        return $steps;
    }

    /** Teks pengantar panel tahap Seleksi Administrasi, menyesuaikan status. */
    public function seleksiPanelIntro(): string
    {
        return match ($this->status) {
            'submitted' => 'Berkas kamu sedang antre untuk diperiksa tim admin: kelengkapan CV, portfolio, dan kesesuaian kualifikasi program.',
            'under_review' => 'Tim sedang meninjau berkasmu lebih detail. Proses ini biasanya tidak lama — ditunggu kabarnya ya.',
            'accepted' => 'Berkasmu dinyatakan lengkap dan memenuhi kualifikasi program. Kamu bisa melanjutkan ke tahap pengumuman.',
            'rejected' => 'Setelah ditinjau, berkasmu belum memenuhi kualifikasi program ini. Kamu tetap bisa mendaftar lagi di periode berikutnya atau mencoba program lain.',
            default => 'Di tahap ini tim admin memeriksa berkas yang kamu kirim.',
        };
    }

    public function internshipPeriodLabel(): ?string
    {
        if (! $this->internship_start_date || ! $this->internship_end_date) {
            return null;
        }

        return $this->internship_start_date->locale('id')->translatedFormat('d M Y')
            .' – '.$this->internship_end_date->locale('id')->translatedFormat('d M Y');
    }

    /** @return array<int, array{label: string, state: 'done'|'pending'|'failed'|'missing'}> */
    public function seleksiChecklist(): array
    {
        return [
            ['label' => 'Data diri & kontak', 'state' => 'done'],
            ['label' => 'Kelengkapan CV', 'state' => filled($this->cv_path) ? 'done' : 'missing'],
            ['label' => 'Kelengkapan portfolio', 'state' => (filled($this->portfolio_path) || filled($this->portfolio_url)) ? 'done' : 'missing'],
            [
                'label' => 'Kesesuaian kualifikasi program',
                'state' => match ($this->status) {
                    'accepted' => 'done',
                    'rejected' => 'failed',
                    default => 'pending',
                },
            ],
        ];
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

    /** Public URL for the applicant to view their own uploaded document. */
    public function publicDocumentUrl(string $type): ?string
    {
        $path = $this->documentFiles()[$type]['path'] ?? null;
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('uploads/'.$path);
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
