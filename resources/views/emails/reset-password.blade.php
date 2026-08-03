<x-mail::message>
# Reset Password

Halo **{{ $user->name }}**,

Kami menerima permintaan reset password untuk akun Tiga Serangkai.

<x-mail::button :url="$resetUrl">
Atur Password Baru
</x-mail::button>

Link berlaku terbatas. Jika kamu tidak meminta reset, abaikan email ini.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
