# 01: Foundation: Latest Laravel + Breeze Auth, Roles & Base Schema

**What to build:** Pengunjung dapat mendaftarkan akun baru, memverifikasi email melalui tautan email, login dengan email dan password dengan perlindungan rate-limiting, serta melakukan reset password ketika lupa kata sandi. Menggunakan framework Laravel versi terbaru (PHP 8.4/8.5, Tailwind CSS 4, Livewire 3). Pengguna terautentikasi diarahkan ke dashboard pengguna biasa, sedangkan admin diarahkan ke portal admin berdasarkan role pengguna.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] Form registrasi memvalidasi nama, email unik, dan password kuat (min 8 karakter, 1 huruf kapital, 1 angka).
- [ ] Email verifikasi dikirimkan saat pendaftaran dengan tautan yang berlaku 60 menit; akun berstatus `unverified` sampai email dikonfirmasi.
- [ ] Pengguna unverified dibatasi dari dashboard pembuatan undangan dan diberikan opsi kirim ulang tautan verifikasi (maksimal 3x per jam).
- [ ] Form login memvalidasi kredensial dengan batas 5 percobaan gagal per 15 menit dan mendukung sesi "Remember Me".
- [ ] Alur forgot/reset password mengirimkan link reset yang kedaluwarsa setelah 60 menit dan menginvalidasi seluruh sesi aktif setelah password baru disetel.
- [ ] Schema database dasar untuk pengguna membedakan peran `user` dan `admin` serta menyiapkan relasi tier langganan awal.
