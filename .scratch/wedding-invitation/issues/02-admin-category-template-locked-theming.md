# 02: Admin: Category & Template Management with Locked themeConfig

**What to build:** Admin platform dapat mengelola kategori desain dan mengunggah/mengatur template undangan pernikahan lengkap dengan metadata konfigurasi tema visual (`themeConfig`). Sistem menerapkan tema tersebut secara server-side melalui CSS variables sehingga gaya tampilan (font, warna primer/sekunder/aksen, tombol) terkunci rapi dan tidak bisa dirusak oleh pengguna akhir.

**Blocked by:** 01: Foundation: Laravel 11 + Breeze Auth, Roles & Base Schema

**Status:** ready-for-agent

- [ ] Admin dapat melakukan CRUD kategori template (nama, slug unik, deskripsi, ikon, urutan tampil, status aktif).
- [ ] Admin dapat melakukan CRUD template (nama, slug unik, thumbnail preview, file Blade view, status premium/free, relasi kategori).
- [ ] Kolom `theme_config` JSON pada template menyimpan parameter warna (primary, secondary, accent), font heading/body Google Fonts, pola background, dan gaya tombol.
- [ ] Layout rendering menginjeksi CSS variables (`:root`) berdasarkan `theme_config` template yang aktif secara konsisten untuk preview admin dan halaman publik.
- [ ] Dipastikan tidak ada antarmuka atau input form yang mengizinkan pengguna untuk mengubah konfigurasi palet warna/font bawaan template.
- [ ] Tersedia minimal 2 template bawaan (1 free, 1 premium) sebagai template awal siap pakai.
