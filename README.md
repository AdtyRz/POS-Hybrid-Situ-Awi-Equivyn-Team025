<p align="center">
  <img src="public/asset/Logo.png" alt="Logo Saung Situ Awi" width="260">
</p>

# 🛒 Saung Situ Awi — POS Hybrid & IoT

**Equivyn Team 025 · SMK Budi Bakti Ciwidey · UKK RPL 2026/2027**

Sistem Point of Sale (POS) hybrid dan IoT untuk resto/saung lesehan **Saung Situ Awi, Ciwidey**. Satu alur data dari tamu memindai QR meja, dapur/bar memasak, pelayan mengantar, kasir menagih, sampai pemilik melihat laporan — tanpa rekap manual.

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Supabase-4169E1?logo=postgresql&logoColor=white)](https://supabase.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Tests](https://img.shields.io/badge/tests-192%20passing-brightgreen)](docs/08-evidence/test-summary.txt)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)
[![Version](https://img.shields.io/badge/version-v1.0.0--rc-orange)](docs/01-release/RELEASE_NOTES_v1.0.0-rc.md)

---

## 📖 Daftar Isi

- [Latar Belakang](#-latar-belakang)
- [Sekilas Produk](#-sekilas-produk)
- [Matriks Teknologi](#-matriks-teknologi)
- [Akun Uji Coba](#-akun-uji-coba)
- [Tim Pengembang](#-tim-pengembang)
- [Dokumentasi](#-dokumentasi)
- [Panduan Instalasi Lokal](#️-panduan-instalasi-local)
- [Realtime](#-realtime-laravel-reverb--laravel-echo)
- [Alur Sesi Meja](#-alur-sesi-meja-kode-unik)
- [Pengujian & Kualitas](#-pengujian--kualitas)
- [Deployment & Fallback](#-deployment--fallback)
- [Lisensi](#-lisensi)

---

## 🧭 Latar Belakang

Resto Saung Situ Awi melayani tamu di 11 saung lesehan yang tersebar di tiga area. Sebelumnya, pesanan dicatat manual, diteruskan ke dapur/bar secara lisan, dan omzet direkap ulang di buku/Excel pada akhir hari. Akibatnya:

- pesanan mudah salah saji atau tertukar antar-saung;
- tamu menunggu lama karena tidak tahu status pesanan;
- kasir sulit memastikan meja mana yang belum membayar;
- pemilik tidak bisa melihat omzet dan stok saat resto masih ramai;
- tidak ada jejak audit saat stok atau pembayaran tidak cocok.

Aplikasi ini menjawab masalah tersebut dengan **satu sumber data**: pesanan dibuat sekali, diteruskan ke stasiun yang benar, dibayar sekali, dan langsung menjadi laporan. Tamu tidak perlu mengunduh aplikasi — cukup memindai QR meja dan memasukkan kode sesi yang tampil di LCD.

---

## 🎯 Sekilas Produk

| Aspek | Keterangan |
|---|---|
| Pengguna | Tamu, kasir, pelayan, koki, barista, admin/owner |
| Perangkat | Browser HP/tablet/laptop + LCD ESP32 (opsional) |
| Alur inti | Scan QR → kode sesi → pilih menu → checkout → KDS → antar → bayar → laporan |
| Pembayaran | Tunai di kasir dan QRIS Midtrans (webhook terverifikasi) |
| Realtime | Laravel Reverb + fallback polling |
| Keamanan | RBAC 5 role, sesi meja, QR token, signature webhook, rate limit |
| Database | PostgreSQL 3NF (Supabase untuk cloud, PostgreSQL lokal untuk demo/offline) |
| Status rilis | `v1.0.0-rc` — 192 test, 1.105 assertion, 0 failure |

---

## 🚀 Matriks Teknologi

| Lapisan | Teknologi | Dipakai untuk |
|---|---|---|
| Backend | **Laravel 13**, PHP 8.3+ | MVC, routing, validasi, queue, event |
| Frontend | **Blade**, Tailwind CSS, Alpine.js, Vite | UI tamu/kasir/KDS/pelayan/admin |
| Database | **PostgreSQL** (Supabase) | Data transaksi, laporan, constraint, row lock |
| ORM | Eloquent + Query Builder | Query binding, relasi, transaksi |
| Realtime | **Laravel Reverb** + Echo | KDS, kasir, pelayan, admin, pelacakan tamu |
| Pembayaran | **Midtrans QRIS** + tunai | Charge QRIS, webhook signature SHA-512 |
| IoT | **ESP32**, LCD I2C, buzzer, tombol sentuh | Kode sesi, fase pembayaran, panggil pelayan |
| Testing | PHPUnit / Pest-style Laravel test | 192 test, 1.105 assertion |
| Deployment | Nginx/Apache, Supervisor, Cloudflare Tunnel, LAN | Staging publik + fallback offline |
| Dokumen | Markdown, PDF, DOCX, OpenAPI 3.0.3, Postman v2.1 | Manual, API, presentasi, defensa |

---

## 🔑 Akun Uji Coba

Semua akun staf dibuat oleh `database/seeders/SituAwiSeeder.php`. Sandi **tidak** ditulis di kode; nilainya diambil dari environment `SEED_*_PASSWORD`.

| Peran | Email | Halaman utama | Sandi |
|---|---|---|---|
| Admin / Owner | `admin@situawi.com` | `/admin/dashboard` | dari `SEED_ADMIN_PASSWORD` |
| Kasir | `kasir@situawi.com` | `/kasir/dashboard` | dari `SEED_KASIR_PASSWORD` |
| Pelayan | `pelayan@situawi.com` | `/pelayan/tab-antar` | dari `SEED_PELAYAN_PASSWORD` |
| Koki | `koki@situawi.com` | `/kds/dapur` | dari `SEED_KOKI_PASSWORD` |
| Barista | `barista@situawi.com` | `/kds/bar` | dari `SEED_BARISTA_PASSWORD` |
| Tamu | — | `/` (scan QR / kode meja) | kode sesi dari LCD |

> **Demo lokal:** untuk latihan di laptop, sandi seragam `Demo#Saung2026` dipakai pada database demo terisolasi (`saung_demo`, port 5433). **Jangan pernah memakai sandi demo di staging/produksi.** Detail: [`docs/06-rehearsal/DEMO_ENVIRONMENT.md`](docs/06-rehearsal/DEMO_ENVIRONMENT.md).

Di lingkungan `local`/`testing`, seeder membuat sandi acak per peran bila `SEED_*` kosong dan menampilkannya di console. Di luar `local`/`testing`, seeder **menolak jalan** bila `SEED_*` belum diisi.

---

## 👥 Tim Pengembang

| Nama | Peran | Tanggung jawab utama |
|---|---|---|
| **Dzakwan Fadlurohman Hisyam** | Frontend / UI-UX / IoT | Blade, Tailwind, alur tamu, tampilan kasir/KDS/pelayan, ESP32 + LCD |
| **Aditya Riqi** | Backend / Database / Security / QA | Skema 3NF, service transaksi, API, RBAC, webhook, test suite |

**Pembimbing:** Nurdiansyah Permana, S.Pd.  
**Mitra:** Rodiansyah Ariwibowo — Owner Saung Situ Awi  
**Sekolah:** SMK Budi Bakti Ciwidey

Pembagian kerja tercatat di commit log dan di [`docs/07-defense/INDIVIDUAL_CHECKLIST.md`](docs/07-defense/INDIVIDUAL_CHECKLIST.md).

---

## 📚 Dokumentasi

| Dokumen | Isi |
|---|---|
| [Release Notes v1.0.0-rc](docs/01-release/RELEASE_NOTES_v1.0.0-rc.md) | Ringkasan rilis, fitur, risiko yang diketahui |
| [Bug Closure Log](docs/01-release/BUG_CLOSURE_LOG.md) | Tiket cacat yang ditutup |
| [Code Freeze Checklist](docs/01-release/CODE_FREEZE_CHECKLIST.md) | Kriteria pembekuan kode |
| [Deployment Guide](docs/02-deployment/DEPLOYMENT_GUIDE.md) | Staging publik, Nginx, Supervisor, queue, Reverb |
| [Offline / LAN Fallback](docs/02-deployment/OFFLINE_LAN_FALLBACK.md) | Kontinjensi demo tanpa internet |
| [User Manual](docs/03-manual/USER_MANUAL.pdf) | Panduan bergambar untuk tamu, kasir, pelayan, admin |
| [Technical Docs](docs/03-manual/TECHNICAL_DOCS.pdf) | Arsitektur, ERD, dependensi, keamanan |
| [API Reference](docs/04-api/API_REFERENCE.md) | 12 endpoint API + webhook, contoh request/response |
| [OpenAPI 3.0.3](docs/04-api/openapi.yaml) | Spesifikasi mesin-terbaca |
| [Postman Collection](docs/04-api/api_collection.json) | Koleksi siap impor |
| [Pitch Deck](docs/05-presentation/Saung-Situ-Awi_Sprint-4_Pitch-Deck.pdf) | 10 slide, 7 menit |
| [Demo Script](docs/06-rehearsal/DEMO_SCRIPT.md) | Naskah demo 8 menit |
| [Backup Video](docs/06-rehearsal/BACKUP_DEMO_v1.0.0-rc.mp4) | Fallback demo 3 menit |
| [Question Bank](docs/07-defense/QUESTION_BANK.md) | 10 pertanyaan wajib + 12 tambahan |
| [Live Debug Drills](docs/07-defense/LIVE_DEBUG_DRILLS.md) | 6 drill sabotase ≤ 5 menit |

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
- **PHP**: `>= 8.3`
- **Composer**: `>= 2.x`
- **Node.js**: `20.19+` atau `22.12+` & **npm**

### 2. Clone & Install Dependency
```bash
git clone https://github.com/AdtyRz/POS-Hybrid-Situ-Awi-Equivyn-Team025.git
cd POS-Hybrid-Situ-Awi-Equivyn-Team025

# Install dependency Backend & Frontend
composer install
npm install
```

### 3. Konfigurasi, Migrasi & Seed

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Seeder staf memakai sandi terpisah per peran melalui `.env`:
`SEED_ADMIN_PASSWORD`, `SEED_KASIR_PASSWORD`, `SEED_PELAYAN_PASSWORD`,
`SEED_KOKI_PASSWORD`, dan `SEED_BARISTA_PASSWORD`. Di lingkungan `local`/`testing`,
variabel yang kosong akan diisi sandi acak unik dan ditampilkan di console saat
seeder dijalankan. Di luar `local`/`testing`, semua variabel tersebut wajib diisi.

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

> **Akses dari HP/LAN:** jalankan `npm run build`, bukan `npm run dev`. Vite dev
> server default hanya mendengarkan loopback, sehingga `public/hot` menunjuk ke
> `http://[::1]:5173` dan aset gagal dimuat di HP. Untuk server Laravel, pakai
> `php artisan serve --host=0.0.0.0 --port=8000` lalu buka
> `http://IP-LAPTOP:8000`, jangan `http://0.0.0.0:8000`.

Pastikan kredensial Reverb ada di `.env` (juga yang ber-prefix `VITE_` untuk sisi browser):

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_ALLOWED_ORIGINS=http://localhost:8000

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Setelah mengubah `.env`, bersihkan cache: `php artisan config:clear`.

### Arsitektur Singkat

- **Events** (`app/Events`, `ShouldBroadcastNow`): `PesananBaru`, `StatusPesananBerubah`,
  `PanggilanPelayan`, `MenuDiperbarui`, `SesiMejaBerubah`.
- **Channel privat** (`routes/channels.php`): `kds.dapur`, `kds.bar`, `kasir`, `pelayan`,
  `admin`, `menu`, `meja.{kode_meja}` per meja, dan `pesanan.{kode_pesanan}` per pesanan.
  Channel staf hanya bisa dilanggan oleh akun aktif dengan role yang sesuai; channel meja
  dan pesanan hanya bisa dilanggan oleh pemilik sesi meja (atau staf). Atur
  `REVERB_ALLOWED_ORIGINS` ke domain aplikasi pada produksi agar origin WebSocket dibatasi.
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
- **Gate verifikasi** berlaku pada e-menu, `checkout`, `pelacakan`, dan **semua halaman detail
  pesanan** (`status`, `verifikasi`, `bayar-tunai`, polling QRIS). Halaman detail hanya bisa
  dibuka bila ada bukti akses yang cocok: cookie sesi meja, kode pesanan yang tersimpan di
  riwayat browser, atau URL status bertanda tangan sementara yang diterbitkan setelah
  pembayaran terverifikasi. Pesanan juga harus dibuat pada sesi aktif tersebut. Kode saung
  tidak pernah diterima dari query string atau header, sehingga URL yang bocor tidak bisa
  dipakai ulang sebagai kredensial.
- Verifikasi disimpan di session dengan key `sesi_meja_{id}`, dibandingkan dengan sesi aktif
  saat ini; begitu sesi ditutup, pelanggan otomatis diminta memasukkan kode baru. Riwayat
  pesanan tamu sebelumnya juga dihapus saat sesi baru diverifikasi.
- **Satu browser hanya terhubung ke satu meja.** Setelah pelanggan membuka LB-01, percobaan
  membuka meja lain (mis. LB-02) lewat PIN, QR, atau URL langsung akan ditolak dan diarahkan
  kembali ke beranda dengan pesan kesalahan.
- Tombol panah kembali di header e-menu menjadi tombol **keluar pelanggan** (tanpa teks
  tambahan). Tombol ini menghapus verifikasi meja/riwayat di browser lalu membuka beranda
  `GET /` (bukan halaman login staf) melalui `POST /keluar`.
- Session ID dirotasi setelah kode saung benar untuk mencegah session fixation. Endpoint
  `buka` dibatasi 5 percobaan/menit, dan halaman pesanan memakai header `no-store`.

### Mengakhiri Sesi (Kasir & Pelayan)

- Saat kasir menekan **Cetak Struk**, muncul modal: **Selesaikan Sesi** atau **Tidak**.
  Memilih "selesaikan" akan menutup sesi, menandai semua pesanan aktif menjadi selesai, dan
  membuat kode baru.
- Kasir juga dapat menyelesaikan sesi secara manual dari halaman **Sesi Meja**.
- Pelayan dapat mengakhiri sesi aktif dari kartu meja di menu **Saung** (`PATCH /pelayan/session/{meja}`).
  Tombol **Akhiri Sesi** selalu memunculkan popup konfirmasi **Tidak** / **Ya, Akhiri Sesi**
  sebelum sesi ditutup. Sesi dengan tagihan belum lunas ditolak dan popup menampilkan pesan
  kesalahan.
- Status meja di halaman pelayan (Saung dan Panggilan) dihitung dari isi sesi berjalan,
  termasuk pesanan yang sudah selesai/dibayar. Meja baru kembali **Tersedia** setelah sesi
  ditutup dan kode baru dibuat.

### Integrasi IoT

Endpoint API untuk perangkat IoT meja menyertakan kode sesi dan **wajib** mengirim header
`X-IoT-Token` yang nilainya sama dengan `IOT_API_TOKEN` di `.env` server:

- `GET /api/iot/status-meja` — status semua meja + `kode_sesi`.
- `GET /api/iot/meja/{ident}` — detail satu meja (dicari via `id_device` atau `kode_meja`) + `kode_sesi`,
  `fase` (`standby`/`pembayaran`/`lunas`), `metode_pembayaran` (`tunai`/`qris`/`campuran`),
  `semua_lunas`, `pesanan[]` (kode, status pesanan/pembayaran, metode, total) pada sesi berjalan,
  `total_bayar`, `jumlah_pesanan`, dan `jumlah_selesai`.
- `POST /api/iot/panggil-pelayan` — body `{"kode_meja":"LB-01","kode_sesi":"1234"}`. Kode sesi
  harus kode yang sedang aktif; kode lama ditolak.

Panggilan pelayan yang masuk hanya dapat dikonfirmasi oleh **pelayan**, lewat tombol
**Konfirmasi Panggilan** di kartu panggilan/notifikasi halaman pelayan. Dashboard kasir
bersifat **notifikasi saja**: banner panggilan dan indikator merah **PANGGILAN** / **Buzzer
Aktif** tampil di kartu saung, tetapi tidak ada tombol konfirmasi di sana. Setelah pelayan
mengonfirmasi, status panggilan menjadi `selesai` dan indikator di kasir maupun pelayan
sama-sama hilang.

Bila `IOT_API_TOKEN` kosong, semua endpoint IoT mengembalikan `503`. Token tidak boleh ditaruh
di URL. Isi konstanta `IOT_API_TOKEN` pada sketch ESP32 dengan nilai yang sama.

**Layar LCD (ESP32, `esp32-iot/esp32_iot_complete.ino`)** — tampilan mengikuti fase sesi meja:

- **Standby** (sesi aktif tetapi belum ada pesanan): layar hanya menampilkan kode meja dan kode
  verifikasi, misalnya:

  ```text
  Meja : LB-01
  Kode : 8754
  ```

  Touch diabaikan pada fase ini agar tamu tidak mengubah layar sebelum memesan.
- **Pembayaran** (ada pesanan belum lunas): QRIS/Midtrans menampilkan `Pembayaran TF` beserta
  total; tunai menampilkan `Pembayaran` / `di Kasir`. Touch tetap diabaikan.
- **Lunas** (semua pesanan sudah dibayar): layar bergilir otomatis setiap 20 detik:
  `Status Pesanan` → `Total Bayar` → `Meja/Kode`. Tap singkat mempercepat giliran layar;
  tahan ~3 detik untuk memanggil pelayan.
- **Tap saat layar error** langsung memicu percobaan koneksi ulang ke server.
- **WiFi terputus** → perangkat menyambung ulang otomatis setiap 15 detik tanpa perlu
  disentuh. Radio WiFi di-reset ringan setiap 4 percobaan. LCD **tetap menampilkan teks
  terakhir** selama proses reconnect, bukan layar kosong/error.
- **Server tidak terjangkau terlalu lama** → setelah 180 detik tanpa polling sukses,
  ESP32 restart otomatis. Watchdog hanya aktif setelah setidaknya satu polling sukses pada
  boot tersebut, sehingga perangkat yang menyala saat server mati tidak mengalami reboot
  berulang.
- Setelah sesi ditutup kasir atau pelayan, LCD kembali ke layar kode standby dengan kode
  sesi baru.

Panduan wiring, library, upload, dan troubleshooting ada di
[`esp32-iot/README.md`](esp32-iot/README.md).

### Akses API Detail Pesanan

`GET /api/v1/pesanan/{kode}` tidak lagi publik. Akses diberikan bila salah satu terpenuhi:

1. Login staf aktif (admin/kasir/pelayan/koki/barista), atau
2. Query/header `qr_token` / `X-QR-Token` milik meja.

Kode saung tidak diterima sebagai kredensial API. Pesanan juga harus dibuat di dalam sesi
meja yang sedang aktif. Respons tidak lagi memuat `qr_token`/`id_device`.

### KDS API per Stasiun

`GET /api/v1/kds/antrean` otomatis memfilter berdasarkan role: akun **koki** hanya menerima
item kategori `target_kds=dapur`, akun **barista** hanya `target_kds=bar`. Parameter
`?target=dapur|bar` boleh dikirim selama sama dengan stasiun akun; mismatch ditolak `403`.

### Realtime

Event **`SesiMejaBerubah`** disiarkan di channel `meja.{kode_meja}`, `kasir`, dan `admin`.
Halaman pelanggan mendengarkan `sesi.berubah` lalu memuat ulang sehingga e-menu kembali
terkunci saat sesi ditutup.

Halaman staf hanya berlangganan channel yang dibutuhkan: KDS ke `kds.dapur`/`kds.bar`,
kasir ke `kasir`, pelayan ke `pelayan`, dan admin ke `admin`. Channel default sengaja kosong
agar halaman publik tidak menerima event staf.

WebSocket memakai Reverb (`php artisan reverb:start`). Bila Reverb tidak berjalan, fallback
polling `/realtime/version` tetap menyegarkan halaman setiap 4 detik (kasir juga memakai state
refresh 8 detik dan QRIS polling 3 detik). API create/update/payment dan panggilan IoT sekarang
juga menyiarkan event realtime.

---

## 🧪 Pengujian & Kualitas

```bash
# Seluruh test (SQLite in-memory)
php artisan test --compact

# Contoh test kritis
php artisan test --filter=test_pembayaran_kedua_ditolak_dan_stok_tidak_menjadi_negatif
php artisan test --filter=test_api_kds_memfilter_antrean_sesuai_stasiun_dan_menolak_target_lain
php artisan test --filter=test_webhook_midtrans_menolak_signature_tidak_valid
```

| Area | Yang diuji |
|---|---|
| Autentikasi & RBAC | Login, shift, akun nonaktif, 401/403, role 5 peran |
| Sesi meja | Kode benar/salah, sesi lama, satu browser satu meja |
| Pesanan | QR token, stok, harga server, detail pesanan |
| Pembayaran | Tunai, QRIS, double payment, stok tidak negatif |
| KDS | Pemisahan dapur/bar, transisi status, runner |
| Pelayan | Panggilan, antar, selesai, akhir sesi |
| Admin | Laporan, ekspor, pengaturan, upload logo/foto |
| Keamanan | IDOR, XSS, upload, signature webhook, rate limit |
| Realtime | Event ke channel privat yang benar |

**Hasil rilis:** `192 test`, `1.105 assertion`, `0 failure` — bukti di [`docs/08-evidence/test-summary.txt`](docs/08-evidence/test-summary.txt) dan [`phpunit-junit.xml`](docs/08-evidence/phpunit-junit.xml).

---

## 🚀 Deployment & Fallback

| Lingkungan | Alamat | Catatan |
|---|---|---|
| Staging publik | `https://syndication-discount-sufficient-evaluated.trycloudflare.com` | Cloudflare Quick Tunnel; URL sementara |
| LAN fallback | `http://192.168.94.171:8020` | Server produksi lokal di jaringan sekolah |
| Health check | `/aktif` | HTTP 200, ±0,02 s (LAN) |
| Demo terisolasi | `http://127.0.0.1:8031` | PostgreSQL `saung_demo` port 5433 |

Panduan lengkap: [`DEPLOYMENT_GUIDE.md`](docs/02-deployment/DEPLOYMENT_GUIDE.md) dan [`OFFLINE_LAN_FALLBACK.md`](docs/02-deployment/OFFLINE_LAN_FALLBACK.md).

> **Risiko yang diketahui:** URL staging memakai Quick Tunnel sementara, CSP belum diaktifkan, dan `X-Powered-By` masih terbuka di sebagian konfigurasi. Semua tercatat di release notes; tidak diklaim selesai.

---

## 📄 Lisensi

Proyek ini dilisensikan under **MIT License** — lihat [`LICENSE`](LICENSE).  
© 2026 Equivyn Team 025.

---

## 🤝 Komunitas & Dukungan

- [Kebijakan Keamanan](SECURITY.md) — cara melaporkan celah keamanan secara privat.
- [Dukungan](SUPPORT.md) — cara mendapatkan bantuan dan informasi yang perlu disertakan.
- [FAQ](FAQ.md) — pertanyaan umum tentang instalasi, pembayaran, KDS, realtime, dan IoT.
- [Lisensi MIT](LICENSE) — ketentuan penggunaan dan kontribusi.

Untuk celah keamanan, **jangan** membuka issue publik. Ikuti [SECURITY.md](SECURITY.md).
