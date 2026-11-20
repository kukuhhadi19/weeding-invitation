# 06: Subscription: 4-Tier Plan Quota & Midtrans Snap Payment

**What to build:** Pengguna dapat melihat 4 paket langganan (Free, Basic, Premium, Enterprise), melakukan upgrade melalui checkout Midtrans Snap (QRIS, GoPay, Virtual Account, Kartu Kredit), dan sistem secara otomatis mengaktifkan paket langganan saat notifikasi pembayaran (webhook) berhasil divalidasi.

**Blocked by:** 03: Invitation: Multi-Step Wizard & Public Slug Resolution

**Status:** ready-for-agent

- [ ] Tabel paket langganan database-driven (Free: Rp 0, Basic: Rp 49k, Premium: Rp 99k, Enterprise: Rp 249k) dengan limit undangan aktif dan fitur yang sesuai.
- [ ] Tombol upgrade membuka popup Midtrans Snap dengan Snap Token yang digenerate aman dari server.
- [ ] Endpoint webhook `/api/midtrans/notification` memverifikasi signature hash Midtrans sebelum memperbarui status transaksi menjadi `success`.
- [ ] Transaksi yang berhasil otomatis memperbarui `subscription_tier_id` dan masa aktif akun pengguna.
- [ ] Sistem membatasi pembuatan undangan baru jika pengguna telah mencapai batas maksimum undangan aktif di paketnya.
- [ ] Pengguna dapat melihat riwayat transaksi pembayaran dan status pembayarannya di dashboard akun.
