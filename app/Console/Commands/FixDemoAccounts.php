<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixDemoAccounts extends Command
{
    protected $signature = 'demo:fix';

    protected $description = 'Reset akun demo (admin / mentor / siswa) ke role & password yang benar';

    public function handle(): int
    {
        $accounts = [
            [
                'email' => 'admin@tigaserangkai.test',
                'name' => 'Admin Tiga Serangkai',
                'role' => 'admin',
            ],
            [
                'email' => 'mentor@tigaserangkai.test',
                'name' => 'Mentor Andi',
                'role' => 'mentor',
            ],
            [
                'email' => 'siswa@tigaserangkai.test',
                'name' => 'Siswa Demo',
                'role' => 'student',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'password',
                    'role' => $account['role'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $this->info("OK {$user->email} → role={$user->role}");
        }

        $this->newLine();
        $this->line('Password semua akun demo: password');
        $this->line('Email harus lengkap, contoh: siswa@tigaserangkai.test');

        return self::SUCCESS;
    }
}
