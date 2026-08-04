<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;

class SyncMitraPartners extends Command
{
    protected $signature = 'partners:sync-mitra';

    protected $description = 'Sinkronkan mitra kampus Tiga Serangkai dari logo di public/logosmitra';

    public function handle(): int
    {
        $mitra = [
            [
                'name' => 'Universitas Sebelas Maret',
                'logo' => 'logosmitra/logo-uns.png',
                'website' => 'https://uns.ac.id',
            ],
            [
                'name' => 'Universitas Muhammadiyah Surakarta',
                'logo' => 'logosmitra/logo-ums.png',
                'website' => 'https://ums.ac.id',
            ],
            [
                'name' => 'UIN Raden Mas Said Surakarta',
                'logo' => 'logosmitra/logo-uin.png',
                'website' => 'https://uinsaid.ac.id',
            ],
            [
                'name' => 'Universitas Duta Bangsa Surakarta',
                'logo' => 'logosmitra/logo-udb.jpg',
                'website' => 'https://udb.ac.id',
            ],
            [
                'name' => 'Tiga Serangkai University',
                'logo' => 'logosmitra/logo-tsu.png',
                'website' => 'http://www.tsu.ac.id/',
            ],
        ];

        // Rename lama jika masih "Tiga Serangkai"
        Partner::query()
            ->where('logo', 'logosmitra/logo-tsu.png')
            ->orWhere('name', 'Tiga Serangkai')
            ->update(['name' => 'Tiga Serangkai University']);

        foreach ($mitra as $item) {
            $publicPath = public_path($item['logo']);
            if (! is_file($publicPath)) {
                $this->warn('Logo belum ada: '.$item['logo']);
                continue;
            }

            Partner::updateOrCreate(
                ['logo' => $item['logo']],
                [
                    'name' => $item['name'],
                    'website' => $item['website'],
                ]
            );

            $this->info('OK '.$item['name']);
        }

        $this->info('Mitra tersinkron: '.Partner::whereNotNull('logo')->count());

        return self::SUCCESS;
    }
}
