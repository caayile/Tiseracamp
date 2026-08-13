<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\CareerResource;
use App\Models\SitePage;
use Illuminate\Database\Seeder;

class PlatformContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'screening_done', 'name' => 'Siap Mulai', 'icon' => '🧭', 'description' => 'Menyelesaikan screening akun.'],
            ['code' => 'first_enrollment', 'name' => 'Peserta Baru', 'icon' => '🎓', 'description' => 'Bergabung di program pertama.'],
            ['code' => 'first_portfolio', 'name' => 'Portofolio Pertama', 'icon' => '📁', 'description' => 'Mengunggah CV atau portofolio.'],
            ['code' => 'first_logbook', 'name' => 'Rajin Mencatat', 'icon' => '📓', 'description' => 'Menulis entri logbook pertama.'],
            ['code' => 'first_cv_review', 'name' => 'CV Dicek AI', 'icon' => '🤖', 'description' => 'Menyelesaikan review CV AI pertama.'],
            ['code' => 'internship_accepted', 'name' => 'Diterima Magang', 'icon' => '🏆', 'description' => 'Lolos seleksi magang.'],
            ['code' => 'job_accepted', 'name' => 'Lolos Lowongan', 'icon' => '💼', 'description' => 'Lamaran kerja diterima.'],
            ['code' => 'course_complete', 'name' => 'Tuntas Belajar', 'icon' => '✅', 'description' => 'Menyelesaikan seluruh materi program.'],
        ] as $row) {
            Achievement::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'icon' => $row['icon'], 'description' => $row['description']]
            );
        }

        foreach ([
            [
                'title' => 'CV lolos ATS: 5 poin wajib',
                'type' => 'cv',
                'content' => "1. Pakai format sederhana (PDF, tanpa kolom rumit).\n2. Sesuaikan kata kunci dengan deskripsi lowongan.\n3. Tulis pencapaian dengan angka, bukan hanya tugas.\n4. Kontak, pendidikan, dan pengalaman mudah dibaca scanner.\n5. Cek ejaan dan tautan portofolio sebelum kirim.",
            ],
            [
                'title' => 'Latihan interview metode STAR',
                'type' => 'interview',
                'content' => "STAR: Situation, Task, Action, Result.\nSiapkan 3 cerita: kerja tim, menyelesaikan masalah, dan inisiatif.\nLatihan 60–90 detik per jawaban. Tutup dengan hasil yang terukur.",
            ],
            [
                'title' => 'Cara lamar di LinkedIn, Glints, dan Jobstreet',
                'type' => 'job',
                'content' => "LinkedIn: lengkapi headline + About, lalu Easy Apply dengan CV terbaru.\nGlints: filter remote/hybrid, isi preferensi gaji realistis.\nJobstreet: aktifkan job alert, cocokkan skill di profil dengan iklan lowongan.",
            ],
        ] as $row) {
            CareerResource::query()->updateOrCreate(
                ['title' => $row['title']],
                [
                    'type' => $row['type'],
                    'content' => $row['content'],
                    'is_published' => true,
                ]
            );
        }

        SitePage::bySlug('terms', 'Syarat & Ketentuan', SitePage::defaultTerms());
        SitePage::bySlug('privacy', 'Kebijakan Privasi', SitePage::defaultPrivacy());
    }
}
