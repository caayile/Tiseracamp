<?php

namespace Database\Seeders;

use App\Models\Achievement;
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

        SitePage::bySlug('terms', 'Syarat & Ketentuan', SitePage::defaultTerms());
        SitePage::bySlug('privacy', 'Kebijakan Privasi', SitePage::defaultPrivacy());
    }
}
