# Tiga Serangkai — setup singkat

## Setelah pull (wajib)

CSS blur login & dark/light mode butuh asset Vite. Setelah `git pull`, jalankan:

```bash
composer install
cp .env.example .env   # kalau belum punya .env
php artisan key:generate
npm install
npm run build
php artisan migrate
php artisan serve
```

Kalau sedang develop UI, pakai `npm run dev` (biarkan jalan) bersama `php artisan serve`.

## Catatan

- Folder `public/build` ikut di-commit agar teman yang pull tetap dapat style tanpa lupa build.
- Gambar login: `public/images/auth-building.png` harus ada di repo.
- Jangan share file `.env` (berisi password DB / App Password email).
