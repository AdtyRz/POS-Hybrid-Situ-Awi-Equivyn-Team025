# 🛒 POS Hybrid Situ Awi — Equivyn Team 025

Sistem Point of Sale (POS) Hybrid & IoT terintegrasi untuk UMKM Kuliner / Wisata Situ Awi. Proyek ini dikembangkan menggunakan arsitektur MVC modern berbasis Laravel untuk mengelola transaksi, inventaris, katalog, dan laporan secara real-time.

---

## 🚀 Tech Stack

- **Backend**: Laravel 13 (PHP 8.3+)
- **Frontend**: Blade Templates, Tailwind CSS, Vite
- **Database**: PostgreSQL (Supabase)
- **Engine & Server**: PDO / Eloquent ORM, Artisan CLI

---

## 📌 Fitur & Roadmap Sprint

- [x] **Sprint 1: Design & Foundation** (User Flow, Wireframe, Schema DB PostgreSQL 3NF, Layout Blade & Token Tailwind)
- [x] **Sprint 2: Core POS & Catalog** (Manajemen Produk/Kamar, Keranjang Transaksi, Interface Kasir/Wisatawan)
- [x] **Sprint 3: Payment Engine & Order Flow** (Integrasi QRIS Midtrans & Tunai Kasir, Handling Status Transaksi, Stok Dipotong Setelah Pembayaran)
- [x] **Sprint 4: Dashboard Admin & Analytics** (Laporan Penjualan, Export Data, Tracking Stok)
- [x] **Sprint 5: Operasional Saung** (Sesi Meja Manual/Otomatis, Struk Thermal, Rekap Shift, Realtime Reverb)

---

## 🛠️ Panduan Instalasi Local

### 1. Prasyarat
- **PHP**: `>= 8.5`
- **Composer**: `>= 2.x`
- **Node.js**: `>= 18.x` & **npm**

### 2. Clone & Install Dependency
```bash
git clone https://github.com/AdtyRz/POS-Hybrid-Situ-Awi-Equivyn-Team025.git
cd POS-Hybrid-Situ-Awi-Equivyn-Team025

# Install dependency Backend & Frontend
composer install
npm install
```

---

## ⚡ Realtime (Laravel Reverb + Laravel Echo)

Semua halaman (KDS, Kasir, Pelayan, Admin, dan pelacakan pelanggan) diperbarui
secara **realtime** lewat WebSocket menggunakan **Laravel Reverb**. Bila WebSocket
tidak tersedia, halaman tetap punya **fallback polling** (interval beberapa detik).

### Menjalankan

Butuh **3 proses** yang berjalan bersamaan:

```bash
# 1. Web server Laravel
php artisan serve

# 2. Server WebSocket Reverb
php artisan reverb:start

# 3. Build aset frontend (dev)
npm run dev          # atau: npm run build (untuk produksi)
```

Pastikan kredensial Reverb ada di `.env` (juga yang ber-prefix `VITE_` untuk sisi browser):

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Setelah mengubah `.env`, bersihkan cache: `php artisan config:clear`.

### Arsitektur Singkat

- **Events** (`app/Events`, `ShouldBroadcastNow`): `PesananBaru`, `StatusPesananBerubah`,
  `PanggilanPelayan`, `MenuDiperbarui`, `SesiMejaBerubah`.
- **Channel publik** (`routes/channels.php`): `kds.dapur`, `kds.bar`, `kasir`, `pelayan`,
  `admin`, `menu`, `meja.{kode_meja}` per meja, dan `pesanan.{kode_pesanan}` per pesanan.
- **Relay browser**: `resources/js/echo.js` berlangganan channel lalu memancarkan DOM event
  `realtime` / `realtime:<nama>`. Halaman cukup mendengarkan event tersebut untuk reload/refresh.
  Halaman standalone (emenu pelanggan) memakai entry `resources/js/reverb.js` dan mengeset
  `window.__CHANNELS__ = ['pesanan.{kode}']`.

---

## 🔐 Alur Sesi Meja (Kode Unik)

Setiap meja dilindungi **kode unik 4 digit** yang tampil di layar (LCD) meja. Pelanggan
harus memasukkannya sebelum boleh membuka e-menu, sehingga hanya tamu di meja tersebut yang
dapat memesan.

### Alur Pelanggan

1. Pelanggan memindai **QR meja** → diarahkan ke `GET /meja/{meja}` (menerima id atau kode meja).
2. Bila sesi meja belum diverifikasi, diarahkan ke halaman **`GET /meja/{meja}/kunci`**.
3. Pelanggan mengetik 4 digit dari LCD dan menekan **Buka Menu** (`POST /meja/{meja}/buka`).
4. Kode benar → sesi ditandai terverifikasi (disimpan di session Laravel) → e-menu terbuka.

### Cara Kerja Sesi

- **Satu sesi aktif per meja**, dicatat di tabel **`sesi_meja`** (`meja_id`, `kode_unik`,
  `status` aktif/selesai, `dibuka_pada`, `ditutup_pada`).
- **Kode selalu aktif per meja**: kode di-generate saat seeder dijalankan dan setiap kali sesi
  berakhir — bukan lewat tombol manual. Kode baru dijamin berbeda dari kode sesi sebelumnya.
- **Gate verifikasi** berlaku pada halaman e-menu, `checkout`, dan `pelacakan`. Selama sesi
  aktif, halaman status per-pesanan tetap dapat diakses.
- Verifikasi disimpan di session dengan key `sesi_meja_{id}`, dibandingkan dengan sesi aktif
  saat ini; begitu sesi ditutup, pelanggan otomatis diminta memasukkan kode baru.

### Mengakhiri Sesi (sisi Kasir)

- Saat kasir menekan **Cetak Struk**, muncul modal: **Selesaikan Sesi** atau **Tidak**.
  Memilih "selesaikan" akan menutup sesi, menandai semua pesanan aktif menjadi selesai, dan
  membuat kode baru.
- Kasir juga dapat menyelesaikan sesi secara manual dari halaman **Sesi Meja**.

### Integrasi IoT

Endpoint API untuk perangkat IoT meja menyertakan kode sesi:

- `GET /api/iot/status-meja` — status semua meja + `kode_sesi`.
- `GET /api/iot/meja/{ident}` — detail satu meja (dicari via `id_device` atau `kode_meja`) + `kode_sesi`,
  `pesanan[]` (kode, status pesanan/pembayaran, total) pada sesi berjalan, `total_bayar`, dan `jumlah_pesanan`.

**Sensor sentuh (ESP32, `esp32-iot/esp32_iot_complete.ino`)** — sensor sentuh dipakai untuk
berpindah tampilan LCD 16x2:

- **Tap singkat** bergiliran: `Status Pesanan` → `Total Bayar` → `Kode Verif Saung` (kode unik sesi),
  sehingga pelanggan yang lupa kode verifikasi cukup tap untuk menampilkannya kembali.
- **Tahan ~1.5 detik** untuk memanggil pelayan (fungsi lama tetap ada).

### Realtime

Event **`SesiMejaBerubah`** disiarkan di channel `meja.{kode_meja}`, `kasir`, dan `admin`.
Halaman pelanggan mendengarkan `sesi.berubah` lalu memuat ulang sehingga e-menu kembali
terkunci saat sesi ditutup.
