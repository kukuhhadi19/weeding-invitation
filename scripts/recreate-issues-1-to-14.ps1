# Script to populate issues 1 to 14 cleanly
$gh = "C:\Program Files\GitHub CLI\gh.exe"
$repo = "kukuhhadi19/weeding-invitation"

function Update-Issue-1 {
    param ([string]$title, [string]$body)
    $tmp = [System.IO.Path]::GetTempFileName()
    try {
        [System.IO.File]::WriteAllText($tmp, $body, [System.Text.Encoding]::UTF8)
        & $gh issue edit 1 --repo $repo --title $title --body-file $tmp
        Write-Host "Updated Issue #1" -ForegroundColor Green
    } finally {
        if (Test-Path $tmp) { Remove-Item $tmp -Force }
    }
}

function Create-Issue {
    param ([string]$title, [string]$body)
    $tmp = [System.IO.Path]::GetTempFileName()
    try {
        [System.IO.File]::WriteAllText($tmp, $body, [System.Text.Encoding]::UTF8)
        $out = & $gh issue create --repo $repo --title $title --body-file $tmp
        Write-Host $out -ForegroundColor Green
        if ($out -match '/issues/(\d+)') { return $matches[1] }
    } finally {
        if (Test-Path $tmp) { Remove-Item $tmp -Force }
    }
    return $null
}

# --- Issue 1 ---
$body1 = @"
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
✅ **Completed** in commit `ac8557a` (27/27 Pest tests passing)
"@
Update-Issue-1 -title "User Registration & Email Verification (End-to-End)" -body $body1

# --- Issue 2 ---
$body2 = @"
## What to build
Implementasi sistem login, session management, dan alur reset password (forgot password) end-to-end. Termasuk proteksi rate limiting untuk percobaan login dan invalidasi sesi lama setelah pergantian password.

## Acceptance criteria
- [ ] Form login dengan validasi kredensial dan remember-me checkbox
- [ ] Rate limiting login: maksimal 5 percobaan gagal per 15 menit
- [ ] Notifikasi jika akun belum verifikasi email saat login
- [ ] Durasi sesi: 7 hari untuk remember-me, 2 jam untuk default
- [ ] Form lupa password: kirim email link reset token (valid 60 menit)
- [ ] Form set password baru dan invalidasi semua sesi aktif setelah reset
- [ ] Feature test untuk login rate-limit dan flow reset password

## Blocked by
- Blocked by #1
"@
Create-Issue -title "User Authentication & Password Reset Flow" -body $body2

# --- Issue 3 ---
$body3 = @"
## What to build
Pondasi sistem langganan (Free, Basic, Premium, Enterprise). Mencakup skema database tabel subscription_tiers, migrasi kolom langganan pada users, seeder 4 tier awal, halaman perbandingan tier di dashboard, dan middleware/service penegak batas (limit) jumlah undangan aktif sesuai tier yang dimiliki user.

## Acceptance criteria
- [ ] Migration tabel subscription_tiers (name, price_monthly, max_active_invitations, max_gallery_photos, dll.)
- [ ] Relasi model User dan SubscriptionTier (default Free saat user mendaftar)
- [ ] Database seeder untuk 4 tier awal (Free: 1 aktif, Basic: 3 aktif, Premium: 10 aktif, Enterprise: unlimited)
- [ ] UI kartu perbandingan 4 tier langganan di halaman /subscription
- [ ] Service SubscriptionLimitService untuk mengecek kuota undangan aktif
- [ ] UI feedback jika user mencapai batas undangan (tombol 'Buat Undangan' disabled + CTA upgrade)

## Blocked by
- Blocked by #1
"@
Create-Issue -title "Subscription Tier Schema, Seeder & Tier Limit Enforcer" -body $body3

# --- Issue 4 ---
$body4 = @"
## What to build
Integrasi pembayaran Midtrans Snap untuk upgrade langganan. User memilih tier, membuka pop-up Snap, dan menyelesaikan transaksi (QRIS, VA, GoPay, Card). Webhook Midtrans memverifikasi signature dan memperbarui tier user secara otomatis, dilengkapi riwayat transaksi di dashboard.

## Acceptance criteria
- [ ] Setup config dan client library Midtrans (config/midtrans.php)
- [ ] Endpoint backend untuk generate Snap Token transaksi
- [ ] Frontend modal/pop-up Snap.js saat klik 'Upgrade'
- [ ] Route webhook /api/midtrans/notification dengan verifikasi signature hash SHA-512
- [ ] Update otomatis status transaksi dan masa berlaku tier user saat status settlement / capture
- [ ] Penanganan status pending, deny, expire, dan cancel
- [ ] Halaman riwayat transaksi /transactions dengan status badge
- [ ] Unit & Feature test webhook payload handler

## Blocked by
- Blocked by #3
"@
Create-Issue -title "Midtrans Snap Checkout, Webhook Listener & Transaction History" -body $body4

# --- Issue 5 ---
$body5 = @"
## What to build
Panel admin untuk mengelola kategori template (misal: Minimalis, Adat Tradisional, Floral, Modern Gold). Meliputi skema tabel categories, Livewire CRUD component dengan validasi, pengurutan, dan upload ikon/kategori.

## Acceptance criteria
- [ ] Migration tabel categories (name, slug, description, icon, sort_order, is_active)
- [ ] Middleware proteksi admin (role:admin)
- [ ] Halaman daftar kategori dengan filter pencarian dan toggle status aktif
- [ ] Form tambah & edit kategori dengan auto-generate slug
- [ ] Seeder awal kategori template pernikahan populer di Indonesia
- [ ] Feature test otorisasi admin dan CRUD kategori

## Blocked by
- Blocked by #2
"@
Create-Issue -title "Admin Template Category Management (CRUD)" -body $body5

# --- Issue 6 ---
$body6 = @"
## What to build
Fitur admin panel untuk mendaftarkan template undangan baru beserta konfigurasi tema yang terkunci (themeConfig JSON). Admin dapat mengunggah thumbnail, mendefinisikan warna utama, font, dan style tombol yang tidak dapat diubah oleh pengguna akhir.

## Acceptance criteria
- [ ] Migration tabel templates dengan kolom JSON theme_config
- [ ] Form input template di admin (nama, kategori, thumbnail, blade layout path, toggle premium/free)
- [ ] Editor / form input themeConfig (primaryColor, secondaryColor, fontHeading, fontBody, buttonStyle)
- [ ] Preview template dengan data dummy menggunakan layout dinamis
- [ ] Validasi struktur JSON theme_config agar tidak merusak rendering publik
- [ ] Feature test simpan dan validasi template

## Blocked by
- Blocked by #5
"@
Create-Issue -title "Admin Template CRUD with Locked ThemeConfig & Preview" -body $body6

# --- Issue 7 ---
$body7 = @"
## What to build
Katalog template untuk pengguna akhir yang menampilkan daftar desain tersedia berdasarkan kategori dan status tier. Dilengkapi fitur live preview layar penuh dengan toggle mode desktop dan mobile.

## Acceptance criteria
- [ ] Halaman /templates dengan layout grid thumbnail template
- [ ] Filter berdasarkan kategori dan tag gratis vs premium
- [ ] Modal/halaman preview template dengan viewport toggle (1440px desktop & 375px mobile)
- [ ] Badge 'Premium' untuk template berbayar
- [ ] Tombol 'Pilih Template' yang mengarahkan ke form pembuatan undangan (atau modal upgrade jika template premium terkunci)

## Blocked by
- Blocked by #6
"@
Create-Issue -title "User Template Catalog & Live Viewport Preview" -body $body7

# --- Issue 8 ---
$body8 = @"
## What to build
Wizard pembuatan undangan dan form editor data dasar pasangan. Menegakkan aturan strictly locked theme: pengguna hanya dapat mengedit teks dan konten, sedangkan warna dan tipografi secara otomatis diinjeksi dari theme_config template yang dipilih tanpa opsi override.

## Acceptance criteria
- [ ] Migration tabel invitations (slug, template_id, user_id, groom_name, bride_name, status)
- [ ] Pengecekan limit tier saat user memulai pembuatan undangan baru
- [ ] Form input data mempelai pria dan wanita
- [ ] Sistem rendering CSS custom properties (--color-primary, dll.) dari theme_config
- [ ] Pencegahan UI override tema (tidak ada input color picker / font picker)
- [ ] Auto-save draft otomatis setiap 30 detik pada editor

## Blocked by
- Blocked by #3
- Blocked by #7
"@
Create-Issue -title "Invitation Wizard & Locked Theming Core (Draft Creation)" -body $body8

# --- Issue 9 ---
$body9 = @"
## What to build
Pengelolaan detail acara ganda yang lazim pada pernikahan di Indonesia (Akad Nikah / Pemberkatan dan Resepsi). Mencakup tanggal, waktu, lokasi, embed Google Maps, serta komponen countdown otomatis menuju hari-H.

## Acceptance criteria
- [ ] Migration tabel events dengan relasi ke invitations (type: akad/resepsi, title, date, time, venue, maps_embed_url)
- [ ] Form input detail Akad dan Resepsi secara terpisah dengan opsi disable salah satu acara
- [ ] Input embed URL / koordinat Google Maps
- [ ] Komponen countdown hari-H (menghitung mundur ke waktu acara terdekat)
- [ ] State 'Acara telah selesai' jika tanggal acara telah lewat
- [ ] Validasi tanggal acara tidak boleh di masa lampau saat dibuat

## Blocked by
- Blocked by #8
"@
Create-Issue -title "Dual Event Scheduling (Akad & Resepsi) & Countdown Timer" -body $body9

# --- Issue 10 ---
$body10 = @"
## What to build
Fitur cerita perjalanan cinta pasangan dalam bentuk timeline interaktif dan informasi hadiah digital (transfer bank & dompet digital seperti GoPay/OVO/DANA) lengkap dengan tombol salin nomor rekening untuk para tamu.

## Acceptance criteria
- [ ] Migration tabel love_stories (title, story_date, description, sort_order)
- [ ] Form CRUD timeline Love Story (minimal 1, maksimal 10 entri)
- [ ] Field JSON gift_info pada tabel invitations untuk menyimpan rekening bank dan e-wallet
- [ ] Form pengisian info amplop digital / transfer hadiah di dashboard pengantin
- [ ] Komponen UI kartu rekening dengan tombol 'Salin Nomor Rekening' one-click

## Blocked by
- Blocked by #8
"@
Create-Issue -title "Couple Story (Love Story Timeline) & Digital Gift Info" -body $body10

# --- Issue 11 ---
$body11 = @"
## What to build
Modul galeri foto pre-wedding pasangan dengan optimasi kompresi otomatis ke format WebP untuk performa loading maksimal, penataan urutan foto (reorder), dan pembatasan jumlah foto sesuai kuota tier langganan.

## Acceptance criteria
- [ ] Migration tabel gallery_photos (invitation_id, photo_path, caption, sort_order)
- [ ] Upload multiple foto dengan validasi MIME (JPEG/PNG/WebP, max 5MB raw)
- [ ] Service kompresi gambar ke WebP (target file size <= 1MB) menggunakan GD/Imagick
- [ ] Pengecekan limit jumlah foto galeri sesuai tier user (Free: 5, Basic: 15, Premium: 30)
- [ ] Drag-and-drop / ordering foto galeri di dashboard pengantin
- [ ] Fitur hapus foto dan storage cleanup otomatis

## Blocked by
- Blocked by #8
"@
Create-Issue -title "Photo Gallery Upload with WebP Optimization" -body $body11

# --- Issue 12 ---
$body12 = @"
## What to build
Halaman publik undangan yang dapat diakses tamu melalui slug unik (domain.com/{slug}). Merender seluruh section (Cover, Profil Pasangan, Acara, Love Story, Galeri, Info Hadiah) dengan desain responsif mobile-first, background music toggle, dan optimasi performa (Lighthouse score >= 90).

## Acceptance criteria
- [ ] Route publik /{slug} yang aman dari konflik reserved routes
- [ ] Tombol Publish / Unpublish di dashboard (slug aktif hanya saat published)
- [ ] Validasi slug unik dan fallback URL untuk custom slug
- [ ] Tampilan responsif di viewport desktop (1440px), tablet (768px), dan mobile (375px)
- [ ] Injeksi CSS variables tema secara server-side
- [ ] Pemutar background audio (autoplay muted dengan opsi toggle unmute oleh tamu)
- [ ] Lazy loading gambar galeri untuk kecepatan rendering

## Blocked by
- Blocked by #8
- Blocked by #9
- Blocked by #10
- Blocked by #11
"@
Create-Issue -title "Public Invitation Slug Page (/{slug}) & Responsive Theme Renderer" -body $body12

# --- Issue 13 ---
$body13 = @"
## What to build
Fitur buku tamu digital (Wall of Wishes) pada halaman publik undangan. Tamu dapat mengirimkan ucapan, doa, dan konfirmasi kehadiran. Ucapan muncul secara dinamis dengan proteksi spam IP dan filter kata-kata kasar.

## Acceptance criteria
- [ ] Migration tabel wishes (invitation_id, guest_name, message, attendance, ip_address, is_visible)
- [ ] Form kirim ucapan: nama, pesan (max 500 karakter), dan pilihan kehadiran (Hadir/Tidak Hadir/Belum Pasti)
- [ ] Komponen Livewire untuk rendering daftar ucapan tamu secara real-time
- [ ] Rate limiting pengiriman: maksimal 1 ucapan per alamat IP per 5 menit
- [ ] Honeypot field dan profanity filter kata kasar dasar Bahasa Indonesia
- [ ] Pagination atau load-more untuk daftar ucapan (10 ucapan per batch)

## Blocked by
- Blocked by #12
"@
Create-Issue -title "Wall of Wishes (Real-time Livewire Guest Submission & Anti-Spam)" -body $body13

# --- Issue 14 ---
$body14 = @"
## What to build
Halaman moderasi ucapan bagi pemilik undangan di dashboard pengantin (hide/show/delete ucapan tamu) serta ringkasan metrik statistik untuk admin platform (total user, transaksi sukses, undangan aktif).

## Acceptance criteria
- [ ] Dashboard pengantin: tab moderasi ucapan (sembunyikan / hapus ucapan tamu yang tidak pantas)
- [ ] Pengaturan toggle 'Moderasi Manual' vs 'Auto-Approve' untuk ucapan baru
- [ ] Dashboard Admin: ringkasan kartu metrik (Total Users, Undangan Aktif, Total Revenue Midtrans)
- [ ] Tabel manajemen user di admin dengan aksi deactive / view
- [ ] Feature test fungsi moderasi ucapan dan akses dashboard admin

## Blocked by
- Blocked by #13
- Blocked by #4
"@
Create-Issue -title "Host Wish Moderation & Admin Overview Dashboard" -body $body14

# Close Issue 1 since it's already implemented
& $gh issue close 1 --repo $repo --reason "completed" --comment "Selesai diimplementasikan pada commit ac8557a dengan 27/27 automated Pest tests passing."
Write-Host "Issue #1 closed as completed!" -ForegroundColor Yellow
