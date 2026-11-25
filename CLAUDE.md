# CLAUDE.md

Panduan kerja untuk coding agent di repository **Weeding Invitation Platform** (selaras dengan [AGENTS.md](AGENTS.md)).

---

## 1. Ringkasan Proyek & Referensi Utama
- **Produk**: Platform web SaaS self-service untuk membuat, mengelola, dan mempublikasikan undangan pernikahan digital dengan 4 tier langganan (`Free`, `Basic`, `Premium`, `Enterprise`) dan pembayaran Midtrans Snap.
- **PRD Lengkap**: [docs/PRD-Wedding-Invitation-Platform.md](docs/PRD-Wedding-Invitation-Platform.md)
- **Backlog & Tracking**: GitHub Issues `#1`–`#14` (tracer-bullet vertical slices). Cek status terkini dengan `gh issue list --state all`.
- **Scope MVP**: Web-only, antarmuka **Bahasa Indonesia**, mata uang **IDR** (tanpa multi-bahasa, mobile app, atau custom domain mapping di Phase 1).

---

## 2. Tech Stack & Struktur Penting
- **Backend**: PHP `^8.3` (Laragon Windows) + Laravel `^13.17` (`laravel/framework`), Laravel Breeze, Livewire `^3.6.4`, Livewire Volt `^1.7.0`.
- **Frontend**: Blade + Livewire Volt + Alpine.js, Tailwind CSS `^3.1.0` (`tailwind.config.js` + `@tailwindcss/forms`), Vite `^8.0.0`.
- **Database**:
  - Dev/Prod: PostgreSQL (`DB_CONNECTION=pgsql`, database `weeding_invitation` di `127.0.0.1:5432`).
  - Testing: SQLite `:memory:` (`phpunit.xml`), dengan `RefreshDatabase` aktif otomatis untuk `tests/Feature` via `tests/Pest.php`.
- **Testing & Quality**: Pest `^5.3` (`php artisan test`), Laravel Pint (`.\vendor\bin\pint`).

### Lokasi File Kunci
- Routing: `routes/web.php`, `routes/auth.php`
- Bootstrap & Middleware Aliases: `bootstrap/app.php` (`role` -> `App\Http\Middleware\EnsureUserHasRole`)
- App Defaults: `app/Providers/AppServiceProvider.php` (password defaults)
- Models & Seeders: `app/Models/User.php`, `app/Models/SubscriptionTier.php`, `database/seeders/SubscriptionTierSeeder.php`, `database/seeders/AdminUserSeeder.php`
- Auth & Volt Components: `resources/views/livewire/pages/auth/`, `app/Livewire/Forms/LoginForm.php`, `app/Http/Controllers/Auth/MagicLoginController.php`, `app/Notifications/MagicLoginNotification.php`
- Layouts & Styling: `resources/views/layouts/guest.blade.php`, `resources/views/layouts/app.blade.php`, `resources/css/app.css`, `tailwind.config.js`
- Referensi UI Statis: `templates/*.html` (`sign-in.html`, `dashboard-account.html`, `dashboard-template.html`, `create-edit-invitation.html`, `landing-page.html`, `public-invitation.html`)

---

## 3. Aturan Bisnis & Invariant Arsitektur
1. **Locked `themeConfig` (Aturan Ketat)**:
   - Konfigurasi visual template (`theme_config` JSON: warna, font, pattern, button style) ditentukan oleh Admin pada template.
   - **Jangan pernah** membuat UI atau endpoint bagi end-user untuk mengubah/override warna atau font template pada undangan mereka.
2. **Otentikasi & Verifikasi Email Ketat**:
   - `App\Models\User` mengimplementasikan `MustVerifyEmail`. Route `/dashboard` dan `/admin/dashboard` dilindungi middleware `['auth', 'verified']` (plus `role:admin` untuk admin).
   - Registrasi user baru otomatis menetapkan `role = 'user'`, `subscription_tier_id` ke tier `Free` (`slug = 'free'`), dan membiarkan `email_verified_at` kosong hingga tautan verifikasi diklik.
   - Password minimal 8 karakter dengan huruf besar/kecil dan angka (`Password::min(8)->mixedCase()->numbers()`).
   - Jangan menambahkan bypass verifikasi atau kartu preview link dev pada halaman UI login/verifikasi.
   - Login mendukung password (`LoginForm`, rate-limit 5 percobaan/15 menit) dan Magic Link (`MagicLoginController`, signed URL 15 menit, normalisasi titik Gmail). Resend verifikasi dibatasi 3×/jam.
3. **Database-Driven Subscription Tiers**:
   - Kuota dan harga berasal dari tabel `subscription_tiers`, bukan hard-coded. Nilai `-1` menandakan unlimited (`max_active_invitations`, `max_gallery_photos`).
4. **Urutan Route Publik `/{slug}`**:
   - Saat mengimplementasikan halaman undangan publik (Issue #12), daftarkan `GET /{slug}` **paling akhir** di `routes/web.php` serta proteksi terhadap collision route sistem (`admin`, `api`, `login`, `register`, `dashboard`, `templates`, `subscription`, `transactions`, `invitations`, `profile`, `verify-email`).
5. **Keamanan Pembayaran & Upload**:
   - Midtrans Snap (Issue #4) wajib memakai kredensial Sandbox saat development dan memverifikasi signature webhook sebelum mengubah status transaksi atau tier user.
   - Upload galeri foto wajib validasi MIME image, konversi/kompresi ke WebP (≤ 1MB), dan enforce kuota sesuai tier.

---

## 4. Konvensi UI & Design System
- **Bahasa & Format**: Gunakan **Bahasa Indonesia** yang rapi dan konsisten untuk seluruh teks UI/validasi baru, serta format mata uang **IDR** (`Rp`).
- **Tipografi**: Gunakan font **`Lexend Deca`** (`wght@100..900` via Google Fonts) di seluruh layout (`layouts/guest.blade.php`, `layouts/app.blade.php`, `tailwind.config.js`, `resources/css/app.css`). Jangan gunakan kembali `Figtree`.
- **Color Tokens (`tailwind.config.js`)**:
  - `primary`: `#165DFF` (`primary-hover`: `#0E4BD9`)
  - `foreground`: `#080C1A`
  - `secondary`: `#6A7686`
  - `muted`: `#EFF2F7`, `border`: `#F3F4F3`, `card-grey`: `#F1F3F6`
- **Referensi Desain**: Adaptasi struktur dan komponen dari `templates/*.html` ke Blade + Livewire + Tailwind v3 (`tailwind.config.js`). Jangan langsung menyalin script CDN Tailwind v4 dari `templates/` ke aplikasi Laravel.

---

## 5. Workflow Agent & Lingkungan Windows (Laragon)
- **Plan Sebelum Implementasi**: Untuk setiap GitHub issue atau fitur baru, susun dan ajukan rencana implementasi terlebih dahulu agar disetujui user sebelum mengedit kode (kecuali user meminta langsung tanpa plan).
- **PowerShell 5.1 Rules**:
  - Hindari operator `&&` di command PowerShell 5.1; gunakan `;` atau `if ($?) { ... }`.
  - Untuk perintah `gh` dengan body multi-baris atau karakter `&`, tulis isi body ke file sementara UTF-8 lalu gunakan `--body-file`.
- **Verifikasi Wajib**:
  - Jalankan `npm run build` setiap ada perubahan Blade/Tailwind/JS agar `public/build/manifest.json` selalu terbaru (tests yang me-render Blade dengan `@vite` akan gagal jika manifest belum di-build).
  - Jalankan `php artisan test` (atau target spesifik misalnya `php artisan test --filter=Auth`) dan pastikan seluruh test lulus tanpa error sebelum menyatakan selesai atau menutup issue.
  - Pastikan tidak ada `dd()`, `dump()`, `console.log()`, atau file sementara/sampah tertinggal sebelum commit.

### Perintah yang Sering Dipakai
```powershell
php artisan test                     # Jalankan semua Pest unit & feature tests
php artisan test --filter=Auth       # Jalankan test spesifik
npm run build                        # Compile aset Tailwind & Vite ke public/build
.\vendor\bin\pint                    # Format kode PHP sesuai standar Laravel Pint
php artisan migrate:fresh --seed     # Reset DB lokal & jalankan SubscriptionTierSeeder + AdminUserSeeder
```
