<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncCategories extends Command
{
    protected $signature = 'categories:sync';

    protected $description = 'Isi kategori bootcamp default untuk katalog';

    public function handle(): int
    {
        $names = [
            'Web Development',
            'Data',
            'Design',
            'Marketing',
            'Product',
            'Career Soft Skills',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            $this->info('OK '.$name);
        }

        $this->info('Total kategori: '.Category::count());

        return self::SUCCESS;
    }
}
