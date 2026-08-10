<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('reviews')->nullable();
            $table->unsignedInteger('days')->default(30);
            $table->string('badge')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $defaults = [
            'starter' => [
                'name' => 'Starter',
                'tagline' => 'Coba review CV dengan AI',
                'price' => 29000,
                'reviews' => 3,
                'days' => 30,
                'badge' => null,
                'features' => [
                    '3x Review CV AI',
                    'Skor per bagian CV',
                    'Analisa kecocokan karier',
                    'Cover Letter & Latihan Interview',
                    'Berlaku 30 hari',
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'tagline' => 'Paling dipilih untuk siap melamar',
                'price' => 79000,
                'reviews' => 10,
                'days' => 30,
                'badge' => 'Populer',
                'features' => [
                    '10x Review CV AI',
                    'Skor per bagian CV',
                    'Analisa kecocokan karier & skill',
                    'Cover Letter & Latihan Interview',
                    'Berlaku 30 hari',
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'tagline' => 'Review tanpa batas sebulan penuh',
                'price' => 149000,
                'reviews' => null,
                'days' => 30,
                'badge' => 'Best value',
                'features' => [
                    'Review CV AI tanpa batas',
                    'Skor per bagian + saran detail',
                    'Analisa kecocokan karier lengkap',
                    'Cover Letter & Latihan Interview',
                    'Berlaku 30 hari',
                ],
            ],
        ];

        $sort = 0;
        foreach ($defaults as $code => $plan) {
            DB::table('cv_plans')->insert([
                'code' => $code,
                'name' => $plan['name'],
                'tagline' => $plan['tagline'],
                'price' => $plan['price'],
                'reviews' => $plan['reviews'],
                'days' => $plan['days'],
                'badge' => $plan['badge'],
                'features' => json_encode($plan['features']),
                'is_active' => true,
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_plans');
    }
};
