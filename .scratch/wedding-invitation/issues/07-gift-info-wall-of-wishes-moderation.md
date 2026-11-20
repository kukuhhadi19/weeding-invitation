# 07: Public Engagement: Digital Gift Copy & Real-Time Wall of Wishes

**What to build:** Tamu undangan dapat menyalin nomor rekening/e-wallet untuk hadiah digital, serta mengirimkan ucapan selamat dan konfirmasi kehadiran di Wall of Wishes publik. Ucapan tamu muncul secara real-time dan pemilik undangan dapat memoderasi (menghapus/menyembunyikan) pesan melalui dashboard.

**Blocked by:** 04: Content: Dual-Event (Akad & Resepsi) Schedule, Maps & Countdown

**Status:** ready-for-agent

- [ ] Pemilik undangan dapat memasukkan informasi rekening bank (nama bank, no rek, atas nama), nomor e-wallet, dan alamat kirim fisik kado.
- [ ] Halaman publik menampilkan kartu hadiah digital dengan tombol satu-klik "Salin Nomor Rekening" yang memberikan indikator feedback visual berhasil disalin.
- [ ] Tamu dapat mengirim ucapan melalui form Wall of Wishes publik (nama tamu, isi pesan max 500 karakter, dan pilihan konfirmasi kehadiran: Hadir/Tidak Hadir/Belum Pasti).
- [ ] Perlindungan terhadap spam: pembatasan rate limit 1 pengiriman per IP per 5 menit serta sensor filter kata kasar (profanity filter).
- [ ] Daftar ucapan tamu diperbarui secara berkala di halaman publik secara otomatis tanpa reload seluruh halaman.
- [ ] Pemilik undangan memiliki menu moderasi ucapan di dashboard untuk menyembunyikan atau menghapus ucapan yang tidak diinginkan.
