# Panduan Kontribusi

Terima kasih telah tertarik berkontribusi pada **POS Hybrid Situ Awi — Equivyn Team 025**. Panduan ini menjelaskan cara menyiapkan lingkungan, menjaga kualitas kode, dan mengirimkan perubahan.

Dengan berkontribusi, Anda menyetujui [Kode Etik](CODE_OF_CONDUCT.md). Untuk melaporkan celah keamanan, ikuti [SECURITY.md](SECURITY.md) dan **jangan** membuka issue publik.

## Prasyarat

| Kebutuhan | Versi |
| --- | --- |
| PHP | `>= 8.3` |
| Composer | `>= 2.x` |
| Node.js | `20.19+` atau `22.12+` |
| npm | Disertakan bersama Node.js |
| Database | PostgreSQL (disarankan) atau SQLite untuk eksplorasi |

Test memakai SQLite `:memory:` dan tidak memerlukan database produksi.

## Menyiapkan Lingkungan

```bash
git clone https://github.com/AdtyRz/POS-Hybrid-Situ-Awi-Equivyn-Team025.git
cd POS-Hybrid-Situ-Awi-Equivyn-Team025

composer install
cp .env.example .env
php artisan key:generate
```

Isi variabel berikut di `.env`:

- `DB_*` — koneksi PostgreSQL/Supabase.
- `MIDTRANS_*` — kredensial QRIS (gunakan sandbox untuk pengembangan).
- `REVERB_*` dan `VITE_REVERB_*` — konfigurasi WebSocket.
- `IOT_API_TOKEN` — token endpoint IoT; kosongkan hanya bila fitur IoT tidak dipakai.
- `SEED_*_PASSWORD` — sandi akun staf hasil seeder.

```bash
php artisan migrate --seed
npm install
npm run build
```

Jalankan aplikasi:

```bash
php artisan serve
php artisan reverb:start
```

Untuk akses dari HP/LAN, jalankan `npm run build` (bukan `npm run dev`), lalu:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Buka `http://IP-LAPTOP:8000`, bukan `http://0.0.0.0:8000`. Detail lengkap ada di [README.md](README.md).

Akun staf hasil seeder:

| Peran | Email |
| --- | --- |
| Admin | `admin@situawi.com` |
| Kasir | `kasir@situawi.com` |
| Pelayan | `pelayan@situawi.com` |
| Koki | `koki@situawi.com` |
| Barista | `barista@situawi.com` |

Sandi diambil dari `SEED_ADMIN_PASSWORD`, `SEED_KASIR_PASSWORD`, `SEED_PELAYAN_PASSWORD`, `SEED_KOKI_PASSWORD`, dan `SEED_BARISTA_PASSWORD`. Di lingkungan `local`/`testing`, sandi kosong akan dibuat otomatis dan ditampilkan di console.

## Alur Branch

- `feature/backend` untuk perubahan backend, integrasi, database, dan API.
- `feature/frontend` untuk perubahan tampilan dan interaksi klien.
- Jangan mencampur perubahan dari kedua branch tanpa koordinasi.
- Selalu buat branch baru dari `feature/backend` yang terbaru:

```bash
git switch feature/backend
git pull origin feature/backend
git switch -c feat/nama-singkat
```

Gunakan awalan nama branch yang jelas: `feat/`, `fix/`, `docs/`, `test/`, `refactor/`, atau `chore/`.

## Standar Kode

- Ikuti konvensi Laravel dan gaya kode di sekitarnya.
- Gunakan type hint dan return type PHP 8.3+.
- Tempatkan logika bisnis di service class, bukan di controller atau view.
- Gunakan validasi request dan database transaction untuk operasi multi-tabel.
- Jalankan formatter sebelum commit:

```bash
./vendor/bin/pint
```

- Untuk frontend, pertahankan Blade, Tailwind, dan Alpine yang sudah ada. Ikuti token warna dan pola komponen di halaman sekitarnya.
- Gunakan `fetch` dengan CSRF token untuk aksi tanpa reload. Pertahankan fallback polling ketika Reverb tidak tersedia.
- Jangan mengubah nama route, URL, atau perilaku non-admin tanpa diskusi terlebih dahulu.
- Jangan meng-commit `.env`, kredensial Midtrans/Supabase/Reverb, token IoT, data pelanggan, atau berkas hasil build.

### Perilaku yang Harus Dipertahankan

Perubahan berikut menyangkut alur bisnis dan keamanan, jadi diskusikan dulu sebelum mengubahnya:

- `kode_saung` tidak pernah diterima lewat query string atau header sebagai kredensial. Akses setelah pembayaran memakai URL bertanda tangan sementara.
- Satu browser pelanggan hanya terikat ke satu meja. Tombol panah kembali berfungsi sebagai keluar pelanggan.
- Pesanan yang dibuat pelayan wajib memakai tunai atau QRIS terverifikasi sebelum masuk KDS.
- Checkbox KDS terkunci sampai **Mulai Masak**. Alur runner adalah **Konfirmasi Kirim** lalu **Pesanan Sudah Dikirim**, dan penyelesaian hanya memicu satu panggilan pelayan.
- Halaman pelanggan tidak boleh menampilkan pesan sesi berakhir saat atau setelah QRIS terverifikasi.
- Pesanan berstatus `diantar` ditampilkan sebagai **diantar**, bukan **selesai**, di halaman pelanggan dan LCD.
- Penyimpanan admin tetap tanpa reload melalui `fetch` dengan Reverb dan fallback polling.
- Firmware ESP32 tetap ringan: `LONG_PRESS_MS = 3000` dan `ROTASI_LAYAR_MS = 20000`.

## Commit

Gunakan [Conventional Commits](https://www.conventionalcommits.org/) dengan pesan singkat dan imperatif:

```text
feat: tambah filter status pesanan di KDS
fix: cegah pembayaran QRIS ganda
docs: perbarui panduan instalasi
test: tambah regresi status diantar
```

- Satu commit untuk satu perubahan logis.
- Jelaskan alasan perubahan di badan commit bila perlu.
- Jangan menyertakan berkas yang tidak terkait.

## Test dan Build

Sebelum membuka pull request, jalankan:

```bash
php artisan test
npm run build
```

Gunakan `composer test` bila ingin membersihkan cache konfigurasi terlebih dahulu. Tambahkan test regresi untuk setiap perbaikan bug. Test tidak boleh bergantung pada database produksi atau kredensial asli.

## Pull Request

1. Targetkan branch `feature/backend` (kecuali perubahan khusus frontend).
2. Isi deskripsi: ringkasan, alasan, langkah pengujian, dan tautan issue terkait.
3. Sertakan screenshot atau rekaman untuk perubahan UI.
4. Pastikan test lulus, build berhasil, dan tidak ada secret di diff.
5. Hindari perubahan yang tidak terkait agar review tetap fokus.
6. Tanggapi masukan reviewer; maintainer dapat meminta revisi sebelum merge.

Dengan mengirimkan kontribusi, Anda menyetujui bahwa kontribusi tersebut dilisensikan di bawah [MIT License](LICENSE).
