## What to build
Implementasi sistem login, session management, dan alur reset password (forgot password) end-to-end dengan mengadopsi UI split-screen modern dari `templates/sign-in.html`. Termasuk proteksi rate limiting untuk percobaan login dan invalidasi sesi lama setelah pergantian password.

## Acceptance criteria
- [x] Form login dengan validasi kredensial dan remember-me checkbox
- [x] Rate limiting login: maksimal 5 percobaan gagal per 15 menit
- [x] Notifikasi jika akun belum verifikasi email saat login
- [x] Durasi sesi: 7 hari untuk remember-me, 2 jam untuk default
- [x] Form lupa password: kirim email link reset token (valid 60 menit)
- [x] Form set password baru dan invalidasi semua sesi aktif setelah reset
- [x] Feature test untuk login rate-limit dan flow reset password

## Blocked by
- Blocked by #1

## Implementation Status
✅ **Completed & Verified**
- Mengadopsi UI split-screen `templates/sign-in.html` dengan hero wedding couple & frosted glass badge
- Font Lexend Deca & tema warna custom Tailwind
- Toggle password show/hide dengan Alpine.js
- Custom checkbox styling
- 29/29 Pest automated tests passing (termasuk rate-limiting & redirect unverified user)
