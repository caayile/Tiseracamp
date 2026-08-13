<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    protected $fillable = ['slug', 'title', 'body'];

    public static function bySlug(string $slug, string $fallbackTitle, string $fallbackBody): self
    {
        return static::query()->firstOrCreate(
            ['slug' => $slug],
            ['title' => $fallbackTitle, 'body' => $fallbackBody]
        );
    }

    public static function defaultTerms(): string
    {
        return <<<'TXT'
Dengan mendaftar dan menggunakan platform Tiga Serangkai, kamu menyetujui ketentuan berikut.

1. Akun
Kamu bertanggung jawab menjaga kerahasiaan akun. Data yang kamu berikan harus akurat dan terkini.

2. Program & layanan
Bootcamp, magang, lowongan, dan Review CV AI tunduk pada kuota, masa berlaku paket, serta kebijakan verifikasi pembayaran oleh admin.

3. Konten pengguna
CV, portofolio, dan dokumen yang diunggah hanya dipakai untuk keperluan platform (lamaran, review AI, seleksi). Jangan unggah file ilegal atau melanggar hak pihak lain.

4. Pembayaran
Akses berbayar aktif setelah admin memverifikasi bukti transfer. Penolakan bukti dapat terjadi jika nominal/identitas tidak sesuai.

5. Perubahan
Kami dapat memperbarui syarat ini. Penggunaan berkelanjutan setelah pembaruan berarti kamu menerima versi terbaru.
TXT;
    }

    public static function defaultPrivacy(): string
    {
        return <<<'TXT'
Kami menghormati privasi peserta platform Tiga Serangkai.

1. Data yang dikumpulkan
Nama, email, nomor telepon, data akademik, dokumen lamaran, bukti pembayaran, serta riwayat aktivitas di platform.

2. Penggunaan data
Data dipakai untuk autentikasi, seleksi program/lowongan, layanan Review CV AI, notifikasi, dan peningkatan layanan.

3. Berbagi data
Data dapat dibagikan ke mentor/admin terkait program, atau mitra lowongan jika kamu melamar. Kami tidak menjual data pribadi.

4. Keamanan
Kami menerapkan kontrol akses berbasis peran dan praktik penyimpanan yang wajar. Namun tidak ada sistem yang 100% bebas risiko.

5. Hakmu
Kamu dapat meminta pembaruan data profil atau menghubungi admin untuk pertanyaan terkait privasi.
TXT;
    }
}
