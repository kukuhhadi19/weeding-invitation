# 03: Invitation: Multi-Step Wizard & Public Slug Resolution

**What to build:** Pengguna terdaftar dapat memilih template dari katalog, membuat draf undangan melalui form interaktif (nama mempelai pria dan wanita), mengatur slug URL kustom, serta mempublikasikan atau mengarsipkan undangan. Pengunjung publik dapat membuka tautan `domain.com/{slug}` untuk melihat halaman undangan terpublikasi.

**Blocked by:** 02: Admin: Category & Template Management with Locked themeConfig

**Status:** ready-for-agent

- [ ] Pengguna dapat menjelajahi katalog template dengan filter kategori serta melihat pratinjau responsif (desktop & mobile viewport) sebelum memilih.
- [ ] Wizard pembuatan undangan memvalidasi kuota undangan aktif pengguna sebelum membuat rekaman undangan baru.
- [ ] Validasi keunikan slug publik dengan resolusi otomatis (misal: penambahan nomor urut jika slug telah digunakan) serta larangan menggunakan reserved slug (seperti `admin`, `login`, `dashboard`, `api`).
- [ ] Pengguna dapat menyimpan draf undangan dan mengubah status menjadi `published`, `draft`, atau `archived`.
- [ ] Route publik `/{slug}` menampilkan halaman undangan sesuai template dan `theme_config` jika status `published`, atau menampilkan pesan tidak tersedia jika draft/archived.
- [ ] Halaman publik menyediakan tombol berbagi instan (WhatsApp share link & salin URL undangan).
