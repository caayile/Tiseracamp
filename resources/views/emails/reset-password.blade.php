<x-mail::message>
# Kode OTP Reset Password

Halo **{{ $user->name }}**,

Gunakan kode OTP berikut untuk mengatur ulang password akun Tiga Serangkai:

<x-mail::panel>
**{{ $otp }}**
</x-mail::panel>

Kode berlaku **{{ $expiresMinutes }} menit**. Jangan bagikan kode ini ke siapa pun.

Jika kamu tidak meminta reset password, abaikan email ini.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
