<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Assignment;
use App\Models\Banner;
use App\Models\Batch;
use App\Models\CareerResource;
use App\Models\Category;
use App\Models\ClassSchedule;
use App\Models\Faq;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Partner;
use App\Models\Program;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'messages', 'conversations', 'discussion_replies', 'discussions', 'notifications',
            'submissions', 'quiz_questions', 'assignments', 'lesson_progress', 'certificates',
            'payments', 'enrollments', 'class_schedules', 'announcements', 'batches',
            'logbook_entries', 'internship_applications',
            'lessons', 'modules', 'programs', 'partners', 'categories', 'achievement_user',
            'achievements', 'portfolios', 'career_resources', 'articles', 'banners', 'faqs',
            'activity_logs', 'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                \DB::table($table)->delete();
            }
        }
        Schema::enableForeignKeyConstraints();

        $admin = User::create([
            'name' => 'Admin Tiga Serangkai',
            'email' => 'admin@tigaserangkai.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $mentor = User::create([
            'name' => 'Mentor Andi',
            'email' => 'mentor@tigaserangkai.test',
            'password' => 'password',
            'role' => 'mentor',
            'status' => 'active',
            'bio' => 'Mentor fullstack & product engineering.',
            'expertise' => ['Laravel', 'React', 'Career Coaching'],
            'rating' => 4.8,
            'email_verified_at' => now(),
        ]);

        $student = User::create([
            'name' => 'Siswa Demo',
            'email' => 'siswa@tigaserangkai.test',
            'password' => 'password',
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $categories = collect(['Web Development', 'Data', 'Design', 'Marketing', 'Product', 'Career Soft Skills'])
            ->map(fn ($name) => Category::create(['name' => $name, 'slug' => Str::slug($name)]));

        $partners = collect([
            ['name' => 'Universitas Sebelas Maret', 'logo' => 'logosmitra/logo-uns.png', 'website' => 'https://uns.ac.id'],
            ['name' => 'Universitas Muhammadiyah Surakarta', 'logo' => 'logosmitra/logo-ums.png', 'website' => 'https://ums.ac.id'],
            ['name' => 'UIN Raden Mas Said Surakarta', 'logo' => 'logosmitra/logo-uin.png', 'website' => 'https://uinsaid.ac.id'],
            ['name' => 'Universitas Duta Bangsa Surakarta', 'logo' => 'logosmitra/logo-udb.jpg', 'website' => 'https://udb.ac.id'],
            ['name' => 'Tiga Serangkai University', 'logo' => 'logosmitra/logo-tsu.png', 'website' => 'http://www.tsu.ac.id/'],
        ])->map(fn ($p) => Partner::create($p));

        $catalog = [
            [
                'title' => 'Fullstack Web Bootcamp',
                'type' => 'bootcamp',
                'level' => 'Beginner',
                'duration_months' => 3,
                'price' => 2500000,
                'is_featured' => true,
                'excerpt' => 'Bangun skill fullstack dari nol hingga siap kerja dengan project berbasis industri.',
                'benefits' => ['Mentor industri', 'Project portfolio', 'Job preparation', 'Sertifikat digital'],
            ],
            [
                'title' => 'Data Analyst Intensive',
                'type' => 'bootcamp',
                'level' => 'Intermediate',
                'duration_months' => 3,
                'price' => 2200000,
                'is_featured' => true,
                'excerpt' => 'Analisis data, visualisasi, dan storytelling untuk keputusan bisnis.',
                'benefits' => ['SQL & Python', 'Dashboard project', 'Case study perusahaan'],
            ],
            [
                'title' => 'UI/UX Product Design',
                'type' => 'bootcamp',
                'level' => 'Beginner',
                'duration_months' => 2,
                'price' => 1800000,
                'is_featured' => false,
                'excerpt' => 'Rancang pengalaman produk yang rapi, modern, dan siap diuji pengguna.',
                'benefits' => ['Design system', 'Prototype Figma', 'Usability testing'],
            ],
            [
                'title' => 'Magang Frontend Engineer',
                'type' => 'internship',
                'level' => 'Intermediate',
                'duration_months' => 4,
                'price' => 0,
                'is_featured' => true,
                'excerpt' => 'Magang online bersama partner industri: kerjakan task nyata dan dapatkan feedback mentor.',
                'benefits' => ['Real project', 'Mentoring mingguan', 'Surat rekomendasi'],
            ],
            [
                'title' => 'Magang Digital Marketing',
                'type' => 'internship',
                'level' => 'Beginner',
                'duration_months' => 3,
                'price' => 0,
                'is_featured' => false,
                'excerpt' => 'Praktik campaign, content, dan analytics bersama brand partner Tiga Serangkai.',
                'benefits' => ['Campaign live', 'Content calendar', 'Performance report'],
            ],
            [
                'title' => 'Backend API Specialist',
                'type' => 'bootcamp',
                'level' => 'Advanced',
                'duration_months' => 3,
                'price' => 2800000,
                'is_featured' => false,
                'excerpt' => 'Kuasai API, auth, testing, dan arsitektur service yang scalable.',
                'benefits' => ['Laravel & Node', 'API design', 'Testing strategy'],
            ],
        ];

        foreach ($catalog as $index => $item) {
            $program = Program::create([
                ...$item,
                'slug' => Str::slug($item['title']),
                'description' => $item['excerpt'].' Program Tiga Serangkai dirancang ala bootcamp modern dengan jalur magang yang terhubung ke partner industri.',
                'partner_id' => $partners[$index % $partners->count()]->id,
                'category_id' => $categories[$index % $categories->count()]->id,
                // Magang dikelola admin — mentor hanya untuk bootcamp
                'mentor_id' => $item['type'] === 'bootcamp' ? $mentor->id : null,
                'is_published' => true,
                'approval_status' => 'approved',
            ]);

            Batch::create([
                'program_id' => $program->id,
                'name' => 'Batch 1 — '.now()->format('M Y'),
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths($item['duration_months'])->toDateString(),
                'quota' => 40,
                'status' => 'active',
            ]);

            ClassSchedule::create([
                'program_id' => $program->id,
                'mentor_id' => $mentor->id,
                'title' => 'Live Mentoring Kickoff',
                'description' => 'Sesi pembukaan dan QnA bersama mentor.',
                'starts_at' => now()->addDays(2)->setTime(19, 0),
                'ends_at' => now()->addDays(2)->setTime(20, 30),
                'meeting_url' => 'https://meet.google.com/demo-tiga-serangkai',
                'status' => 'scheduled',
            ]);

            foreach ([
                'Fondasi & Mindset',
                'Core Skills',
                'Project Capstone',
            ] as $mIndex => $moduleTitle) {
                $module = Module::create([
                    'program_id' => $program->id,
                    'title' => $moduleTitle,
                    'sort_order' => $mIndex + 1,
                ]);

                $lessons = [
                    ['Pengantar program', 'text'],
                    ['Praktik inti (video)', 'video'],
                    ['Modul PDF pendukung', 'pdf'],
                    ['Artikel mendalam', 'article'],
                    ['Checkpoint quiz', 'quiz'],
                ];

                foreach ($lessons as $lIndex => [$lessonTitle, $type]) {
                    $lesson = Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonTitle,
                        'type' => $type,
                        'content' => "Materi {$lessonTitle} pada modul {$moduleTitle}. Pelajari konsep, kerjakan latihan, lalu tandai selesai untuk naik progress.",
                        'video_url' => $type === 'video' ? 'https://www.youtube.com/embed/dQw4w9WgXcQ' : null,
                        'file_url' => $type === 'pdf' ? 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf' : null,
                        'file_type' => $type === 'pdf' ? 'pdf' : null,
                        'duration_minutes' => 15 + ($lIndex * 5),
                        'sort_order' => $lIndex + 1,
                    ]);

                    if ($type === 'quiz' && $mIndex === 0) {
                        $assignment = Assignment::create([
                            'lesson_id' => $lesson->id,
                            'title' => 'Quiz checkpoint',
                            'instructions' => 'Jawab pertanyaan singkat.',
                            'deadline' => now()->addWeeks(2),
                            'kind' => 'quiz',
                        ]);
                        QuizQuestion::create([
                            'assignment_id' => $assignment->id,
                            'question' => 'Apa tujuan utama program Tiga Serangkai?',
                            'options' => ['Hiburan saja', 'Siap karier lewat belajar & praktik', 'Hanya sertifikat', 'Tidak ada tujuan'],
                            'correct_index' => 1,
                            'points' => 10,
                        ]);
                    }

                    if ($type === 'text' && $mIndex === 1) {
                        Assignment::create([
                            'lesson_id' => $lesson->id,
                            'title' => 'Tugas praktik',
                            'instructions' => 'Upload link GitHub/Figma atau file hasil kerja kamu.',
                            'deadline' => now()->addWeeks(3),
                            'kind' => 'assignment',
                        ]);
                    }
                }
            }
        }

        Achievement::create(['name' => 'First Step', 'icon' => '🚀', 'description' => 'Menyelesaikan materi pertama']);
        Achievement::create(['name' => 'Consistent Learner', 'icon' => '🔥', 'description' => 'Aktif belajar 7 hari']);
        CareerResource::create(['title' => 'Template CV ATS-Friendly', 'type' => 'cv', 'content' => 'Gunakan struktur ringkas: ringkasan, skill, pengalaman, project.']);
        CareerResource::create(['title' => 'Persiapan Interview Technical', 'type' => 'interview', 'content' => 'Latihan STAR method + penjelasan project portfolio.']);
        CareerResource::create(['title' => 'Info Magang Partner', 'type' => 'job', 'content' => 'Cek lowongan magang dari partner Tiga Serangkai setiap bulan.']);
        Banner::create([
            'title' => 'Batch baru Tiga Serangkai dibuka',
            'subtitle' => 'Bootcamp & magang online dengan mentoring industri',
            'cta_text' => 'Lihat program',
            'cta_link' => '/programs',
            'is_active' => true,
        ]);
        Faq::create(['question' => 'Apakah bisa daftar sebagai mentor?', 'answer' => 'Ya, pilih role Mentor saat register. Course yang dibuat mentor perlu approve admin.', 'sort_order' => 1]);
        Faq::create(['question' => 'Bagaimana pembayaran course berbayar?', 'answer' => 'Checkout lalu upload bukti transfer. Admin akan verifikasi sebelum akses kelas dibuka.', 'sort_order' => 2]);

        \App\Models\Article::create([
            'title' => 'Orientasi Mahasiswa Magang Baru',
            'slug' => 'orientasi-mahasiswa-magang-baru-demo',
            'excerpt' => 'Selamat datang di program magang Tiga Serangkai. Berikut ringkasan kegiatan orientasi.',
            'body' => "Hari pertama orientasi fokus pada pengenalan budaya kerja, alur komunikasi dengan mentor, dan target pembelajaran selama masa magang.\n\nPeserta diminta menyiapkan dokumen pendukung, mengaktifkan akun learning, serta mengikuti sesi ice breaking bersama batch.",
            'is_published' => true,
            'published_at' => now()->subDays(3),
        ]);

        unset($admin, $student);
    }
}
