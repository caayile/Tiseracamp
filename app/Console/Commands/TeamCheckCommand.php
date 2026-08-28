<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class TeamCheckCommand extends Command
{
    protected $signature = 'team:check';

    protected $description = 'Cek setting tim: DB region, session/cache, dan kelengkapan route admin/mentor';

    public function handle(): int
    {
        $this->info('=== Cek setup Tiga Serangkai ===');
        $this->newLine();

        $session = config('session.driver');
        $cache = config('cache.default');
        $queue = config('queue.default');

        $this->line('SESSION_DRIVER = '.$session.($session === 'file' ? ' ✓' : ' ✗ harus file'));
        $this->line('CACHE_STORE    = '.$cache.($cache === 'file' ? ' ✓' : ' ✗ harus file'));
        $this->line('QUEUE_CONNECTION = '.$queue.($queue === 'sync' ? ' ✓' : ' (disarankan sync di lokal)'));

        $dbUrl = (string) env('DB_URL', '');
        $host = (string) (config('database.connections.pgsql.host') ?: '');
        $haystack = strtolower($dbUrl.' '.$host);

        if (str_contains($haystack, 'us-east')) {
            $this->error('DB region US East terdeteksi — ini penyebab utama lemot dari Indonesia.');
            $this->warn('Buat project Neon baru di Singapore (ap-southeast-1), ganti DB_URL, lalu migrate + demo:fix.');
        } elseif (str_contains($haystack, 'ap-southeast') || str_contains($haystack, 'singapore')) {
            $this->info('DB region Singapore ✓');
        } elseif (str_contains($haystack, '127.0.0.1') || str_contains($haystack, 'localhost')) {
            $this->info('DB lokal ✓ (data tidak sinkron antar laptop kecuali pakai host bersama)');
        } else {
            $this->warn('Region DB tidak dikenali. Pastikan Neon di Singapore.');
        }

        if (str_contains($haystack, 'neon.tech') && ! str_contains($haystack, 'pooler')) {
            $this->warn('Neon tanpa -pooler — pakai connection string pooled agar lebih stabil.');
        }

        try {
            DB::connection()->getPdo();
            $this->info('Koneksi database ✓');
        } catch (\Throwable $e) {
            $this->error('Koneksi database gagal: '.$e->getMessage());
        }

        $adminRoutes = [
            'admin.dashboard',
            'admin.users.index',
            'admin.programs.index',
            'admin.applications.index',
            'admin.grades.index',
            'admin.schedules.index',
            'admin.chat.index',
            'admin.payments.index',
            'admin.content.index',
        ];

        $missingAdmin = collect($adminRoutes)->reject(fn ($name) => Route::has($name));
        if ($missingAdmin->isEmpty()) {
            $this->info('Route admin lengkap ✓');
        } else {
            $this->error('Route admin hilang: '.$missingAdmin->implode(', '));
            $this->warn('Kemungkinan kode belum di-git pull.');
        }

        $mentorRoutes = [
            'mentor.dashboard',
            'mentor.programs.index',
            'mentor.submissions.bootcamp',
            'mentor.submissions.internship',
            'mentor.schedules.index',
            'mentor.chat.index',
        ];
        $missingMentor = collect($mentorRoutes)->reject(fn ($name) => Route::has($name));
        if ($missingMentor->isEmpty()) {
            $this->info('Route mentor lengkap ✓');
        } else {
            $this->error('Route mentor hilang: '.$missingMentor->implode(', '));
        }

        $this->newLine();
        $this->line('Setelah pull, jalankan:');
        $this->line('  composer install && npm install && npm run build');
        $this->line('  php artisan config:clear && php artisan view:clear');
        $this->line('  php artisan migrate && php artisan demo:fix');
        $this->line('  php artisan team:check');

        return self::SUCCESS;
    }
}
