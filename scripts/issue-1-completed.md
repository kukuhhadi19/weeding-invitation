## What to build
Implementasi alur registrasi pengguna baru dan verifikasi email end-to-end. Calon pengguna dapat mendaftar dengan nama, email, dan password kuat, menerima email verifikasi dengan signed URL berdurasi 60 menit, dan mengaktifkan akunnya. Akses dashboard dibatasi untuk akun yang belum terverifikasi.

## Acceptance criteria
- [x] Form registrasi (nama, email, password, konfirmasi password) dengan validasi lengkap
- [x] Email unik, password minimal 8 karakter (kombinasi huruf besar & angka)
- [x] Pengiriman email verifikasi otomatis menggunakan Laravel Notification / Mailable (link aktif 60 menit)
- [x] Status akun unverified sampai link verifikasi diklik
- [x] Tombol 'Kirim Ulang Email Verifikasi' dengan rate-limit maksimal 3x per jam
- [x] Redirect ke dashboard saat verifikasi berhasil
- [x] Feature test untuk skenario registrasi valid, email duplikat, dan verifikasi email

## Blocked by
None - can start immediately

## Implementation Status
✅ **Completed & Verified**
- 27/27 Pest automated feature tests passing
- Migrasi database PostgreSQL (`users`, `subscription_tiers`) aktif
- Default assignment: role `user` & tier `Free`
- Rate-limiting resend verifikasi 3×/jam terverifikasi
