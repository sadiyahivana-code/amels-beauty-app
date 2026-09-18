# Amels Beauty — Aplikasi Kasir & Manajemen Stok

Prototype aplikasi web untuk salon **Amels Beauty**, dibuat sesuai rancangan tugas RPL
(Perancangan Aplikasi Kasir dan Manajemen Stok Berbasis Web).

Dibuat pakai **PHP native + SQLite** (bukan Laravel penuh) supaya bisa langsung dites
tanpa install Composer/MySQL dulu. Struktur tabel & fiturnya tetap sesuai rancangan
(bisa "dinaikkan" ke Laravel nanti kalau kelompok mau, karena skema database & alur
logikanya sama).

## Penting Kalau Sudah Pernah Dijalankan Sebelumnya

Kalau kamu sudah pernah buka aplikasi ini sebelumnya (sudah ada file `data/amels.sqlite`),
**hapus file itu dulu** sebelum menjalankan ulang — supaya data layanan & harga ter-update
sesuai price list terbaru. File baru akan otomatis dibuat lagi dengan data yang benar.

## Cara Menjalankan

**Syarat:** PHP sudah terpasang di laptop (cek dengan `php -v` di terminal/VS Code).
Kalau belum ada, install PHP dulu (di Windows paling gampang pakai [Laragon](https://laragon.org/)
atau [XAMPP](https://www.apachefriends.org/id/index.html) — nanti taruh folder ini di
`htdocs`/`www`).

1. Buka folder ini di VS Code
2. Buka terminal di VS Code, jalankan:
   ```
   php -S localhost:8000
   ```
3. Buka browser ke `http://localhost:8000`
4. Database SQLite (`data/amels.sqlite`) otomatis dibuat + diisi data contoh saat pertama kali diakses

## Akun Contoh

| Role  | Username | Password  |
|-------|----------|-----------|
| Owner | owner    | owner123  |
| Kasir | kasir    | kasir123  |

## Supaya Bisa Diakses dari HP/Tablet Lain (satu WiFi)

Jalankan dengan opsi berikut, ganti port kalau perlu:
```
php -S 0.0.0.0:8000
```
Lalu di HP/tablet buka `http://[IP-laptop-kamu]:8000` (cek IP laptop dengan `ipconfig` di
Windows atau `ifconfig` di Mac/Linux). Pastikan laptop dan HP di jaringan WiFi yang sama.

## Struktur Folder

```
amels-app/
├── config.php              # koneksi DB + schema + seed data
├── login.php / logout.php
├── dashboard.php
├── kasir.php                # halaman transaksi (pilih layanan, checkout)
├── kasir_process.php        # proses simpan transaksi + kurangi stok
├── struk.php                # struk transaksi (bisa dicetak)
├── transaksi_riwayat.php
├── bahan.php / bahan_action.php       # CRUD bahan habis pakai
├── alat.php / alat_action.php         # CRUD alat/aset
├── pengeluaran.php / pengeluaran_action.php
├── laporan.php               # laporan penjualan, stok, alat, laba rugi
├── users.php / users_action.php       # manajemen user (owner only)
├── includes/                 # header, footer, auth
├── assets/css/style.css
└── data/amels.sqlite         # dibuat otomatis
```

## Install sebagai "App" (PWA)

Aplikasi ini sudah bisa di-install ke HP/laptop supaya muncul icon-nya sendiri dan
kebuka fullscreen kayak app asli (tanpa address bar) — namanya **PWA (Progressive Web App)**.
Bukan file .apk dari Play Store, tapi pengalamannya mirip.

**Penting — soal HTTPS:** browser cuma mengizinkan fitur "Install App" di alamat
`https://` atau `http://localhost`. Jadi:
- **Di laptop sendiri** (buka lewat `http://localhost:8000`): install PWA langsung bisa jalan
- **Di HP/tablet lewat IP WiFi** (`http://192.168.x.x:8000`): tombol install **tidak akan muncul**
  karena bukan `https://` — ini batasan keamanan browser, bukan bug aplikasi

**Supaya bisa di-install dari HP juga (untuk demo ke dosen), ada 2 opsi:**
1. **Paling gampang — pakai ngrok** (bikin URL https sementara ke localhost kamu):
   - Download [ngrok](https://ngrok.com/), jalankan `ngrok http 8000`
   - Copy URL `https://xxxx.ngrok-free.app` yang muncul, buka itu di HP
   - Install PWA dari situ, jalan normal
2. **Upload ke hosting gratis yang sudah HTTPS** (misal 000webhost, InfinityFree, atau Railway) —
   lebih cocok kalau aplikasinya mau dipakai beneran sehari-hari, bukan cuma demo

**Cara install setelah alamat sudah https/localhost:**
- **Android (Chrome):** buka aplikasi → akan muncul banner "Tambahkan Amels Beauty ke layar utama", atau lewat menu titik tiga → "Install app"
- **iPhone (Safari):** tombol Share (kotak dengan panah ke atas) → "Tambah ke Layar Utama"
- **Laptop (Chrome/Edge):** ada ikon install (+) di ujung kanan address bar

## Aktifkan Pembayaran Otomatis (Midtrans)

Aplikasi ini sudah siap terhubung ke **Midtrans** (payment gateway) untuk pembayaran QRIS/kartu/transfer
bank otomatis. Selama belum dikonfigurasi, halaman kasir otomatis pakai QR dummy manual seperti biasa —
jadi aman, tidak akan error walau belum di-setup.

**Cara mengaktifkan:**
1. Daftar di [midtrans.com](https://midtrans.com), pakai mode **Sandbox** dulu (testing, bukan uang asli)
2. Buka dashboard → Settings → Access Keys, copy **Server Key** dan **Client Key**
3. Buka file `includes/midtrans_config.php`, ganti:
   ```php
   define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-ISI_DI_SINI');
   define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-ISI_DI_SINI');
   ```
   dengan key asli dari dashboard kamu
4. Simpan, refresh halaman Kasir — tombol QRIS sekarang otomatis berubah jadi "Bayar Sekarang (Midtrans)"

**Cara testing pembayaran (Sandbox):**
- Klik "Bayar Sekarang (Midtrans)" saat checkout
- Pilih metode pembayaran apa saja di popup yang muncul (misal QRIS atau Kartu Kredit simulasi)
- Untuk kartu simulasi, Midtrans sudah sediakan nomor kartu uji coba di dokumentasi mereka
  (cek [docs.midtrans.com](https://docs.midtrans.com) bagian "Testing")
- Setelah sukses, tombol "Proses Pembayaran" otomatis aktif, klik untuk menyimpan transaksi

**Catatan penting:**
- Ini pakai integrasi **Snap** (client-side), jadi tidak butuh Composer atau server tambahan
- Status pembayaran dikonfirmasi langsung dari popup (`onSuccess`), bukan dari webhook server —
  cukup untuk demo tugas, tapi untuk pemakaian produksi sungguhan sebaiknya ditambah verifikasi
  webhook/notification handler dari Midtrans supaya lebih aman dari manipulasi
- Kalau nanti sudah siap pakai akun live (uang asli), ganti juga `MIDTRANS_IS_PRODUCTION` jadi `true`
  dan pakai Server/Client Key dari akun production (bukan sandbox)

## Fitur yang Sudah Ada

- Login role-based (Owner vs Kasir)
- Kasir: pilih layanan → keranjang → metode bayar (Tunai / QRIS / EDC) → struk
- Struk bisa dikirim langsung ke WhatsApp pelanggan (isi nomor WA saat checkout, opsional) atau dicetak
- Stok bahan otomatis berkurang sesuai bahan yang dipakai tiap layanan terjual
- Alert stok menipis & alat perlu servis di dashboard
- CRUD bahan, alat, pengeluaran, user (khusus owner)
- Laporan penjualan, stok, kondisi alat, dan laba rugi per periode (bisa export PDF via print browser)
- Grafik penjualan harian di halaman Laporan, ikut filter tanggal yang dipilih
- Halaman **Pengaturan** (khusus Owner) untuk upload/ganti gambar QR pembayaran sendiri, tanpa perlu edit file manual
- Edit data user (nama, username, password, role) dari menu User, tidak perlu hapus & buat ulang
- Tampilan responsif — nyaman dipakai di laptop, tablet, maupun HP

## Yang Masih Perlu Disesuaikan

- **QRIS**: saat ini masih placeholder kotak QR. Ganti dengan gambar QR asli Amels Beauty
  (edit bagian `qr-box` di `kasir.php`, masukkan `<img src="assets/qr-amels.png">`)
- **Data layanan/bahan/alat**: sudah diisi sesuai data yang kamu kasih, tapi harga layanan
  di `config.php` masih contoh — sesuaikan dengan harga asli Amels Beauty
- Kalau kelompok mau tulis di laporan pakai Laravel/MySQL (sesuai proposal awal), skema
  tabel di `config.php` bisa langsung jadi acuan migration Laravel
