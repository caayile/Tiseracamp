<x-mail::message>
# Verifikasi Akun

Halo **{{ $user->name }}**,

Terima kasih sudah mendaftar di Tiga Serangkai. Klik tombol di bawah untuk memverifikasi email dan mengaktifkan akunmu.

<x-mail::button :url="$verifyUrl">
Verifikasi Akun
</x-mail::button>

Link berlaku **60 menit**. Jika tombol tidak bisa diklik, salin tautan ini ke browser:

{{ $verifyUrl }}

Jika kamu tidak mendaftar, abaikan email ini.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
