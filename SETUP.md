# Tiga Serangkai — setup singkat

## Kenapa lemot? (baca dulu)

Kalau tiap klik 2–5 detik, hampir pasti **database Neon di US East**.
Dari Indonesia ke US East round-trip lambat. Perbaikan terbesar:

1. Buat project Neon baru region **Singapore (`ap-southeast-1`)**
2. Copy connection string **pooled** (host biasanya ada `-pooler`)
3. Samakan `DB_URL` di `.env` semua anggota tim
4. Pastikan:
   ```env
   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```
5. Satu orang: `php artisan migrate && php artisan demo:fix`
6. Semua orang: `php artisan config:clear`

Cek cepat:

```bash
php artisan team:check
```

---

## Setelah pull (wajib)

Setelah `git pull`, jalankan perintah ini di folder project:

```bash
composer install
npm install
npm run build
php artisan config:clear
php artisan view:clear
php artisan migrate
php artisan demo:fix
php artisan team:check
php artisan serve
```

Kalau belum punya `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Lalu isi `DB_URL` Neon Singapore (sama dengan tim).

Kalau sedang develop UI, pakai `npm run dev` (biarkan jalan) bersama `php artisan serve`.

---

## Agar data sama di semua laptop (penting)

### Solusi A — 1 database cloud bersama (disarankan)

Pakai **Neon** (gratis) atau Supabase:

1. Buat project di [https://neon.tech](https://neon.tech)
2. **Pilih region Singapore (`ap-southeast-1`)** — jauh lebih cepat dari Indonesia. Hindari US East.
3. Copy connection string (**pooled** kalau ada opsi itu)
4. Semua anggota tim isi `.env` dengan credential **yang sama**
5. Di `.env` lokal, pakai juga:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

(Supaya session/cache/queue tidak bolak-balik ke Neon tiap klik halaman.)

```env
DB_CONNECTION=pgsql
DB_HOST=ep-xxxxx-pooler.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=password_dari_neon
```

Atau pakai URL lengkap:

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://USER:PASSWORD@HOST/DB?sslmode=require
```

Satu orang saja yang pertama kali jalankan:

```bash
php artisan migrate
php artisan db:seed
php artisan demo:fix
```

Teman lain cukup `git pull` + samakan bagian DB di `.env`, lalu:

```bash
php artisan config:clear
php artisan migrate
php artisan serve
```

Setelah itu, user yang daftar/login di laptop A **langsung terlihat** di panel admin laptop B.

### Solusi B — satu laptop jadi server (tanpa cloud)

Satu orang jalankan:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Teman buka `http://IP-LAPTOP-KAMU:8000` di browser.
Semua pakai **satu server + satu database** — data otomatis sama.
(Laptop host harus tetap nyala dan satu WiFi.)

### Jangan

- Jangan commit file `.env` ke GitHub (ada password)
- Jangan pakai `DB_HOST=127.0.0.1` kalau mau data sinkron antar laptop yang `php artisan serve` sendiri-sendiri
- Jangan biarkan `SESSION_DRIVER=database` / `CACHE_STORE=database` saat DB di Neon
- Bagikan credential DB lewat chat pribadi, bukan di repo

---

## Error umum

### Admin menu tidak lengkap / mentor masih acak-acakan
Kode di laptop temen belum update. Wajib:

```bash
git pull
composer install
npm install
npm run build
php artisan config:clear
php artisan view:clear
php artisan migrate
php artisan demo:fix
php artisan team:check
```

Login admin: `admin@tigaserangkai.test` / `password`
Login mentor: `mentor@tigaserangkai.test` / `password`

### Login demo gagal / admin masuk ke dashboard siswa
Email harus lengkap: `siswa@tigaserangkai.test` (ada `.test`). Password: `password`.

Kalau role salah, jalankan:

```bash
php artisan demo:fix
```

### Toggle magang error `column "is_open" does not exist`
```bash
php artisan migrate
```

### Kolom berita `published_at` belum ada
```bash
php artisan migrate
```

### Login tanpa blur / dark mode tidak jalan
```bash
npm install
npm run build
php artisan view:clear
```

Lalu hard refresh **Ctrl+F5**.

Kalau dark bisa ON tapi tidak bisa balik ke light, di Console:

```js
localStorage.setItem('ts-theme', 'light'); location.reload();
```

## Catatan

- Folder `public/build` ikut di-commit agar teman yang pull tetap dapat style.
- Gambar login: `public/images/auth-building.png` harus ada di repo.
- Jangan share file `.env` (berisi password DB / App Password email).
