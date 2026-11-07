# FAQ

Kumpulan pertanyaan yang sering muncul tentang **POS Hybrid Situ Awi — Equivyn Team 025**.

## Apa itu POS Hybrid Situ Awi?

POS Hybrid Situ Awi adalah sistem point of sale dan IoT untuk Saung Situ Awi. Sistem ini menangani katalog menu, pesanan meja, pembayaran tunai dan QRIS, dapur/bar (KDS), pengantaran pesanan, laporan penjualan, serta layar LCD ESP32 per meja.

## Siapa saja pengguna sistem?

| Peran | Tugas utama |
| --- | --- |
| Admin | Mengelola menu, stok, pengguna, pengaturan, dan laporan |
| Kasir | Membuat pesanan, menerima pembayaran, mencetak struk, dan mengelola sesi meja |
| Pelayan | Mengelola meja, mengantar pesanan, dan menangani panggilan pelanggan |
| Koki | Memproses item KDS stasiun dapur |
| Barista | Memproses item KDS stasiun bar |
| Pelanggan | Memindai QR meja, membuka e-menu, memesan, dan membayar |

## Bagaimana pelanggan membuka e-menu?

1. Pelanggan memindai QR meja.
2. Pelanggan memasukkan kode unik 4 digit yang tampil di LCD meja.
3. Setelah kode benar, e-menu terbuka dan pelanggan dapat memesan.

Satu browser pelanggan hanya terikat ke satu meja. Jika ingin berpindah meja, pelanggan harus keluar terlebih dahulu melalui tombol panah kembali.

## Mengapa pelanggan diminta memasukkan kode lagi?

Kode sesi dibuat ulang setiap sesi meja berakhir. Saat kasir atau pelayan menutup sesi, sesi lama tidak berlaku dan pelanggan harus memakai kode baru dari LCD. Ini mencegah pesanan masuk ke sesi meja yang sudah selesai.

## Metode pembayaran apa yang didukung?

- **Tunai** — dikonfirmasi oleh kasir.
- **QRIS** — diproses melalui Midtrans, baik dari e-menu pelanggan maupun dari halaman kasir.

Pesanan yang dibuat pelayan wajib memakai tunai atau QRIS yang sudah terverifikasi sebelum boleh masuk KDS.

## Kapan pesanan masuk ke KDS?

Setelah pembayaran terverifikasi:

- QRIS dari e-menu menunggu verifikasi Midtrans.
- Tunai dari e-menu menunggu konfirmasi kasir.
- Pesanan yang belum lunas tidak dapat dipindahkan ke status `diproses` dan tidak masuk antrean dapur/bar.

## Apa arti status pesanan?

| Status | Arti |
| --- | --- |
| `menunggu` | Pesanan dibuat staf dan menunggu diproses |
| `menunggu_pembayaran` | Pesanan e-menu tunai menunggu pembayaran kasir |
| `menunggu_verifikasi` | Pesanan e-menu QRIS menunggu verifikasi Midtrans |
| `diproses` | Pembayaran sudah lunas; pesanan dikerjakan dapur/bar |
| `siap` | Pesanan siap diantar |
| `diantar` | Pesanan sedang diantar ke meja |
| `selesai` | Pelayan sudah mengonfirmasi pesanan tiba di meja |
| `dibatalkan` | Pesanan dibatalkan sebelum lunas |

Pesanan berstatus `diantar` ditampilkan sebagai **diantar**, bukan **selesai**, di halaman pelanggan dan LCD. Status `selesai` baru muncul setelah pelayan menekan **Pesanan Sudah Dikirim**.

## Mengapa tampilan tidak berubah secara realtime?

Periksa hal berikut:

1. Pastikan `php artisan reverb:start` berjalan.
2. Jalankan `php artisan config:clear` setelah mengubah `.env`.
3. Pastikan `BROADCAST_CONNECTION=reverb` dan nilai `REVERB_*` cocok dengan `VITE_REVERB_*`.
4. Jalankan `npm run build`, lalu muat ulang halaman.

Jika WebSocket tetap tidak tersedia, aplikasi memakai fallback polling. Halaman umumnya menyegarkan data setiap 4 detik, pembayaran QRIS setiap 3 detik, dan kasir memakai state refresh 8 detik.

## Mengapa CSS atau aset tidak muncul saat diakses dari HP?

Vite dev server default hanya mendengarkan loopback, sehingga `public/hot` dapat menunjuk ke `http://[::1]:5173` dan aset gagal dimuat dari perangkat lain. Untuk akses HP/LAN:

```bash
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

Buka `http://IP-LAPTOP:8000`, bukan `http://0.0.0.0:8000`.

## Bagaimana cara login sebagai staf?

Gunakan akun hasil seeder:

| Peran | Email |
| --- | --- |
| Admin | `admin@situawi.com` |
| Kasir | `kasir@situawi.com` |
| Pelayan | `pelayan@situawi.com` |
| Koki | `koki@situawi.com` |
| Barista | `barista@situawi.com` |

Sandi diatur lewat `SEED_ADMIN_PASSWORD`, `SEED_KASIR_PASSWORD`, `SEED_PELAYAN_PASSWORD`, `SEED_KOKI_PASSWORD`, dan `SEED_BARISTA_PASSWORD`. Di lingkungan `local`/`testing`, sandi kosong dibuat otomatis dan ditampilkan saat seeder berjalan. Di luar lingkungan tersebut, semua variabel wajib diisi.

## Bagaimana cara menjalankan test?

```bash
php artisan test
```

atau:

```bash
composer test
```

Test memakai SQLite `:memory:` dan tidak menyentuh database produksi.

## Bagaimana cara mengakhiri sesi meja?

- Kasir dapat menekan **Cetak Struk**, lalu memilih **Selesaikan Sesi** pada modal yang muncul.
- Kasir juga dapat menyelesaikan sesi dari halaman **Sesi Meja**.
- Pelayan dapat menekan **Akhiri Sesi** pada kartu meja, lalu mengonfirmasi **Ya, Akhiri Sesi**.

Sesi dengan tagihan belum lunas akan ditolak. Setelah sesi ditutup, kode baru dibuat dan LCD kembali ke layar standby.

## Bagaimana cara mereset database?

Untuk lingkungan pengembangan saja:

```bash
php artisan migrate:fresh --seed
```

Perintah ini **menghapus seluruh data** dan membuat ulang skema serta data seeder. Cadangkan data terlebih dahulu dan jangan menjalankannya di produksi.

## Apakah aplikasi bisa dijalankan di shared hosting?

Aplikasi membutuhkan PostgreSQL (`pdo_pgsql`), proses Reverb yang berjalan terus-menerus, dan queue worker. Shared hosting berbasis cPanel umumnya tidak cocok untuk kebutuhan tersebut. VPS KVM lebih disarankan agar Reverb dan worker dapat berjalan stabil.

## Bagaimana cara menghubungkan ESP32?

1. Buka `esp32-iot/esp32_iot_complete.ino` dan panduan di `esp32-iot/README.md`.
2. Isi `IOT_API_TOKEN` pada sketch dengan nilai yang sama seperti di `.env` server.
3. Kirim token melalui header `X-IoT-Token`; jangan menaruhnya di URL.
4. Pastikan perangkat dan server dapat saling menjangkau.

Jika `IOT_API_TOKEN` kosong, endpoint IoT mengembalikan `503`.

## Bagaimana cara berkontribusi atau melaporkan masalah?

- Kontribusi kode: buka pull request ke branch `feature/backend`.
- Pertanyaan dan bug: baca [SUPPORT.md](SUPPORT.md).
- Celah keamanan: ikuti [SECURITY.md](SECURITY.md) dan jangan membuka issue publik.

## Lisensi apa yang digunakan?

Proyek ini dilisensikan di bawah [MIT License](LICENSE).
